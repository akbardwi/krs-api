<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\DaftarNilai;
use App\Models\JadwalTawar;
use App\Models\MahasiswaDinus;
use App\Models\MatkulKurikulum;
use Illuminate\Http\Request;

class Course extends Controller
{
    /**
     * Available Courses (Simplified)
     */
    public function courseAvailable($nim)
    {
        // 1. Ambil data mahasiswa
        $mhs = MahasiswaDinus::where('nim_dinus', $nim)->first();
        if (!$mhs) {
            return response()->json(['error' => 'Mahasiswa tidak ditemukan'], 404);
        }

        // 2. Filter matkul berdasarkan prodi
        $matkuls = MatkulKurikulum::where('kdmk', 'like', $mhs->prodi . '%')->get(['kdmk', 'nmmk']);

        // 3. Ambil daftar nilai A
        $nilaiA = DaftarNilai::where('nim_dinus', $nim)
            ->where('nl', 'A')
            ->pluck('kdmk')
            ->toArray();

        // 4. Ambil KRS existing (jadwal tawar yang sudah diambil)
        $krsExisting = JadwalTawar::pluck('kdmk')
            ->toArray();

        // 5. Mark eligible
        $result = [];
        foreach ($matkuls as $matkul) {
            $eligible = true;
            $reason = null;

            if (in_array($matkul->kdmk, $nilaiA)) {
                $eligible = false;
                $reason = 'Sudah pernah diambil dengan nilai A';
            } elseif (in_array($matkul->kdmk, $krsExisting)) {
                $eligible = false;
                $reason = 'Tabrakan dengan KRS existing';
            }

            $result[] = [
                'kdmk' => $matkul->kdmk,
                'nmmk' => $matkul->nmmk,
                'eligible' => $eligible,
                'reason' => $reason,
            ];
        }

        return response()->json($result);
    }
}
