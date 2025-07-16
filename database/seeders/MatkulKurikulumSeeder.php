<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\MatkulKurikulum;
use Illuminate\Support\Facades\DB;

class MatkulKurikulumSeeder extends Seeder
{
    public function run(): void
    {
        $csvFile = resource_path('csv/matkul_kurikulum.csv');
        if (!file_exists($csvFile)) {
            $this->command->error('CSV file not found: ' . $csvFile);
            return;
        }

        $this->command->info('Starting to import matkul_kurikulum data from CSV...');
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        MatkulKurikulum::truncate();
        $file = fopen($csvFile, 'r');
        $header = fgetcsv($file);
        $batchData = [];
        $batchSize = 1000;
        $totalRecords = 0;
        $errorCount = 0;
        while (($row = fgetcsv($file)) !== FALSE) {
            if (empty(array_filter($row))) {
                continue;
            }
            try {
                $batchData[] = [
                    'kur_id' => $row[0],
                    'kdmk' => $row[1],
                    'nmmk' => $row[2],
                    'nmen' => $row[3],
                    'tp' => $row[4],
                    'sks' => $row[5],
                    'sks_t' => $row[6],
                    'sks_p' => $row[7],
                    'smt' => $row[8],
                    'jns_smt' => $row[9],
                    'aktif' => ($row[10] === '' ? 0 : (int)$row[10]),
                    'kur_nama' => $row[11],
                    'kelompok_makul' => $row[12],
                    'kur_aktif' => $row[13],
                    'jenis_matkul' => $row[14],
                ];
                $totalRecords++;
                if (count($batchData) >= $batchSize) {
                    MatkulKurikulum::insert($batchData);
                    $this->command->info("Imported {$totalRecords} records...");
                    $batchData = [];
                }
            } catch (\Exception $e) {
                $errorCount++;
                $this->command->warn("Error processing row {$totalRecords}: " . $e->getMessage());
                file_put_contents(
                    storage_path('logs/matkul_kurikulum_seeder_errors.txt'),
                    "Row {$totalRecords}: " . $e->getMessage() . " | Data: " . json_encode($row) . PHP_EOL,
                    FILE_APPEND
                );
                continue;
            }
        }
        if (!empty($batchData)) {
            MatkulKurikulum::insert($batchData);
        }
        fclose($file);
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        $this->command->info("Successfully imported {$totalRecords} matkul_kurikulum records!");
        if ($errorCount > 0) {
            $this->command->warn("Skipped {$errorCount} records due to errors or missing data.");
        }
    }
}
