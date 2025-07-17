<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\KrsRecord;
use App\Models\TahunAjaran;
use App\Models\JadwalTawar;
use App\Models\MatkulKurikulum;
use App\Models\MahasiswaDinus;
use App\Models\KrsRecordLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class Krs extends Controller
{
    /**
     * Student KRS Overview
     */
    public function currentKrs($nim)
    {
        $ta = TahunAjaran::where('set_aktif', 1)->first();
        if (!$ta) {
            return response()->json([
                'status' => 'error',
                'message' => 'Tahun ajaran aktif tidak ditemukan',
            ], 404);
        }

        $krs = KrsRecord::where('nim_dinus', $nim)
            ->where('ta', $ta->kode)
            ->with(['jadwalTawar', 'matkulKurikulum'])
            ->get();

        if ($krs->isEmpty()) {
            return response()->json([
                'status' => 'error',
                'message' => 'KRS tidak ditemukan untuk mahasiswa dengan NIM ' . $nim . ' pada tahun ajaran ' . $ta->kode,
                'ta' => $ta->kode,
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $krs,
            'ta' => $ta->kode
        ]);
    }

    /**
     * KRS Course Registration
     */
    public function courseRegistration(Request $request)
    {
        $request->validate([
            'nim_dinus' => 'required|string|max:50',
            'id_jadwal' => 'required|integer|exists:jadwal_tawar,id',
        ]);

        $nim = $request->nim_dinus;
        $idJadwal = $request->id_jadwal;

        // Get active tahun ajaran
        $ta = TahunAjaran::where('set_aktif', 1)->first();
        if (!$ta) {
            return response()->json([
                'status' => 'error',
                'message' => 'Tahun ajaran aktif tidak ditemukan',
            ], 404);
        }

        try {
            DB::beginTransaction();

            // 1. Validation: Check if mahasiswa exists
            $mahasiswa = MahasiswaDinus::where('nim_dinus', $nim)->first();
            if (!$mahasiswa) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Mahasiswa dengan NIM ' . $nim . ' tidak ditemukan',
                ], 404);
            }

            // 2. Get jadwal tawar details
            $jadwal = JadwalTawar::find($idJadwal);
            if (!$jadwal) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Jadwal tawar tidak ditemukan',
                ], 404);
            }

            // Additional validation: Check if jadwal is for current ta and is open
            if ($jadwal->ta != $ta->kode) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Jadwal tawar tidak sesuai dengan tahun ajaran aktif',
                ], 400);
            }

            if (!$jadwal->open_class) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Kelas tidak dibuka untuk registrasi KRS',
                ], 400);
            }

            // 3. Validation: Check if mata kuliah sesuai kurikulum
            $matkul = MatkulKurikulum::where('kdmk', $jadwal->kdmk)->first();
            if (!$matkul) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Mata kuliah tidak ditemukan dalam kurikulum',
                ], 404);
            }
            $prodi = $mahasiswa->prodi;
            if (strpos($jadwal->kdmk, $prodi) !== 0) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Mata kuliah tidak sesuai dengan program studi mahasiswa',
                ], 400);
            }
            // $kdmk = $matkul->kdmk;
            

            // 4. Validation: Check if kelas tidak penuh
            if ($jadwal->jsisa <= 0) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Kelas sudah penuh, tidak dapat menambahkan mahasiswa',
                ], 400);
            }

            // 5. Validation: Check if mata kuliah sudah diambil untuk tahun ajaran ini
            $existingKrs = KrsRecord::where('nim_dinus', $nim)
                ->where('ta', $ta->kode)
                ->where('kdmk', $jadwal->kdmk)
                ->first();

            if ($existingKrs) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Mata kuliah ' . $jadwal->kdmk . ' sudah diambil pada tahun ajaran ini',
                ], 400);
            }

            // 6. Validation: Check jadwal tidak tabrakan (same time conflict)
            $conflictingSchedule = $this->checkScheduleConflict($nim, $ta->kode, $jadwal);
            if ($conflictingSchedule) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Jadwal bertabrakan dengan mata kuliah: ' . $conflictingSchedule->kdmk,
                    'conflicting_schedule' => $conflictingSchedule,
                ], 400);
            }

            // 7. Insert ke krs_record
            $krsRecord = KrsRecord::create([
                'ta' => $ta->kode,
                'kdmk' => $jadwal->kdmk,
                'id_jadwal' => $idJadwal,
                'nim_dinus' => $nim,
                'sts' => 'B', // Default status 'B' (Baru/Belum)
                'sks' => $matkul->sks,
                'modul' => false,
            ]);

            // 8. Update jsisa di jadwal_tawar
            $jadwal->decrement('jsisa');

            // 9. Basic logging to krs_record_log
            KrsRecordLog::create([
                'id_krs' => $krsRecord->id,
                'nim_dinus' => $nim,
                'kdmk' => $jadwal->kdmk,
                'aksi' => 1, // 1 = insert
                'id_jadwal' => $idJadwal,
                'ip_addr' => $request->ip(),
                'lastUpdate' => now(),
            ]);

            // Basic logging to application logs
            Log::info('KRS mata kuliah added', [
                'nim_dinus' => $nim,
                'kdmk' => $jadwal->kdmk,
                'id_jadwal' => $idJadwal,
                'ta' => $ta->kode,
                'ip_address' => $request->ip(),
            ]);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Mata kuliah berhasil ditambahkan ke KRS',
                'data' => [
                    'krs_record' => $krsRecord,
                    'mata_kuliah' => $matkul,
                    'jadwal' => $jadwal,
                    'sisa_slot' => $jadwal->jsisa,
                ],
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Error adding mata kuliah to KRS', [
                'nim_dinus' => $nim,
                'id_jadwal' => $idJadwal,
                'error' => $e->getMessage(),
                'ip_address' => $request->ip(),
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan saat menambahkan mata kuliah ke KRS',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Check for schedule conflicts
     */
    private function checkScheduleConflict($nim, $ta, $newJadwal)
    {
        // Get all existing KRS for this student in current tahun ajaran
        $existingKrs = KrsRecord::where('nim_dinus', $nim)
            ->where('ta', $ta)
            ->with('jadwalTawar')
            ->get();

        foreach ($existingKrs as $krs) {
            $existingJadwal = $krs->jadwalTawar;
            
            if (!$existingJadwal) continue;

            // Check if schedules conflict based on hari and sesi
            $conflict = $this->hasTimeConflict($existingJadwal, $newJadwal);
            
            if ($conflict) {
                return $existingJadwal;
            }
        }

        return null;
    }

    /**
     * Check if two schedules have time conflict
     */
    private function hasTimeConflict($schedule1, $schedule2)
    {
        // Check hari1 conflicts
        if ($schedule1->id_hari1 && $schedule2->id_hari1) {
            if ($schedule1->id_hari1 == $schedule2->id_hari1) {
                if ($this->hasSesiConflict($schedule1->id_sesi1, $schedule2->id_sesi1)) {
                    return true;
                }
            }
        }

        // Check hari2 conflicts
        if ($schedule1->id_hari2 && $schedule2->id_hari2) {
            if ($schedule1->id_hari2 == $schedule2->id_hari2) {
                if ($this->hasSesiConflict($schedule1->id_sesi2, $schedule2->id_sesi2)) {
                    return true;
                }
            }
        }

        // Check hari3 conflicts
        if ($schedule1->id_hari3 && $schedule2->id_hari3) {
            if ($schedule1->id_hari3 == $schedule2->id_hari3) {
                if ($this->hasSesiConflict($schedule1->id_sesi3, $schedule2->id_sesi3)) {
                    return true;
                }
            }
        }

        // Cross-check between different hari combinations
        // hari1 vs hari2, hari1 vs hari3, hari2 vs hari1, etc.
        $hari1_conflicts = [
            [$schedule1->id_hari1, $schedule1->id_sesi1, $schedule2->id_hari2, $schedule2->id_sesi2],
            [$schedule1->id_hari1, $schedule1->id_sesi1, $schedule2->id_hari3, $schedule2->id_sesi3],
            [$schedule1->id_hari2, $schedule1->id_sesi2, $schedule2->id_hari1, $schedule2->id_sesi1],
            [$schedule1->id_hari2, $schedule1->id_sesi2, $schedule2->id_hari3, $schedule2->id_sesi3],
            [$schedule1->id_hari3, $schedule1->id_sesi3, $schedule2->id_hari1, $schedule2->id_sesi1],
            [$schedule1->id_hari3, $schedule1->id_sesi3, $schedule2->id_hari2, $schedule2->id_sesi2],
        ];

        foreach ($hari1_conflicts as $conflict_check) {
            if ($conflict_check[0] && $conflict_check[2] && 
                $conflict_check[0] == $conflict_check[2] && 
                $this->hasSesiConflict($conflict_check[1], $conflict_check[3])) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if two sesi have conflict
     */
    private function hasSesiConflict($sesi1, $sesi2)
    {
        // If both sesi are 0 (not used), no conflict
        if (!$sesi1 || !$sesi2) {
            return false;
        }

        // If exact same sesi, definitely conflict
        if ($sesi1 == $sesi2) {
            return true;
        }

        // Additional logic for overlapping sesi can be added here
        // For now, we assume different sesi numbers don't conflict
        return false;
    }

    /**
     * Remove mata kuliah from student KRS
     */
    public function removeMatakuliah(Request $request, $nim, $schedule_id)
    {
        // Get active tahun ajaran
        $ta = TahunAjaran::where('set_aktif', 1)->first();
        if (!$ta) {
            return response()->json([
                'status' => 'error',
                'message' => 'Tahun ajaran aktif tidak ditemukan',
            ], 404);
        }

        try {
            DB::beginTransaction();

            // 1. Validation: Check if mahasiswa exists
            $mahasiswa = MahasiswaDinus::where('nim_dinus', $nim)->first();
            if (!$mahasiswa) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Mahasiswa dengan NIM ' . $nim . ' tidak ditemukan',
                ], 404);
            }

            // 2. Find KRS record based on nim, ta, and schedule_id
            $krsRecord = KrsRecord::where('nim_dinus', $nim)
                ->where('ta', $ta->kode)
                ->where('id_jadwal', $schedule_id)
                ->with(['jadwalTawar', 'matkulKurikulum'])
                ->first();

            if (!$krsRecord) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'KRS record tidak ditemukan untuk mata kuliah dengan jadwal ID ' . $schedule_id,
                ], 404);
            }

            // 3. Business Logic: Check if KRS belum tervalidasi
            // Assuming 'V' status means validated, anything else means not validated yet
            if ($krsRecord->sts === 'V') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Tidak dapat menghapus mata kuliah yang sudah tervalidasi',
                ], 400);
            }

            // 4. Get jadwal tawar for quota update
            $jadwal = $krsRecord->jadwalTawar;
            if (!$jadwal) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Jadwal tawar tidak ditemukan',
                ], 404);
            }

            // Store data for logging and response before deletion
            $deletedData = [
                'krs_record_id' => $krsRecord->id,
                'nim_dinus' => $krsRecord->nim_dinus,
                'kdmk' => $krsRecord->kdmk,
                'id_jadwal' => $krsRecord->id_jadwal,
                'mata_kuliah' => $krsRecord->matkulKurikulum,
                'jadwal' => $jadwal,
            ];

            // 5. Delete KRS record
            KrsRecord::withoutGlobalScopes()->where('id', $krsRecord->id)->delete();
            // $krsRecord->delete();

            // 6. Update quota availability - increment jsisa
            $jadwal->increment('jsisa');

            // 7. Basic logging to krs_record_log
            KrsRecordLog::create([
                'id_krs' => $deletedData['krs_record_id'],
                'nim_dinus' => $deletedData['nim_dinus'],
                'kdmk' => $deletedData['kdmk'],
                'aksi' => 2, // 2 = delete
                'id_jadwal' => $deletedData['id_jadwal'],
                'ip_addr' => $request->ip(),
                'lastUpdate' => now(),
            ]);

            // Basic logging to application logs
            Log::info('KRS mata kuliah removed', [
                'nim_dinus' => $deletedData['nim_dinus'],
                'kdmk' => $deletedData['kdmk'],
                'id_jadwal' => $deletedData['id_jadwal'],
                'ta' => $ta->kode,
                'ip_address' => $request->ip(),
            ]);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Mata kuliah berhasil dihapus dari KRS',
                'data' => [
                    'deleted_mata_kuliah' => $deletedData['mata_kuliah'],
                    'deleted_jadwal' => $deletedData['jadwal'],
                    'updated_quota' => $jadwal->jsisa,
                ],
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Error removing mata kuliah from KRS', [
                'nim_dinus' => $nim,
                'schedule_id' => $schedule_id,
                'error' => $e->getMessage(),
                'ip_address' => $request->ip(),
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan saat menghapus mata kuliah dari KRS',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
