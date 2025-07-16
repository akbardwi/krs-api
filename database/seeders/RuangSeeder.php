<?php

namespace Database\Seeders;

use App\Models\Ruang;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RuangSeeder extends Seeder
{
    public function run(): void
    {
        $csvFile = resource_path('csv/ruang.csv');
        if (!file_exists($csvFile)) {
            $this->command->error('CSV file not found: ' . $csvFile);
            return;
        }

        $this->command->info('Starting to import ruang data from CSV...');
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Ruang::truncate();
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
                    'id' => $row[0],
                    'nama' => $row[1],
                    'nama2' => $row[2],
                    'id_jenis_makul' => ($row[3] !== '' ? (int)$row[3] : null),
                    'id_fakultas' => $row[4],
                    'kapasitas' => ($row[5] !== '' ? (int)$row[5] : 0),
                    'kap_ujian' => ($row[6] !== '' ? (int)$row[6] : 0),
                    'status' => $row[7],
                    'luas' => $row[8],
                    'kondisi' => $row[9] ?? null,
                    'jumlah' => ($row[10] !== '' ? (int)$row[10] : 0),
                ];
                $totalRecords++;
                if (count($batchData) >= $batchSize) {
                    Ruang::insert($batchData);
                    $this->command->info("Imported {$totalRecords} records...");
                    $batchData = [];
                }
            } catch (\Exception $e) {
                $errorCount++;
                $this->command->warn("Error processing row {$totalRecords}: " . $e->getMessage());
                file_put_contents(
                    storage_path('logs/ruang_seeder_errors.txt'),
                    "Row {$totalRecords}: " . $e->getMessage() . " | Data: " . json_encode($row) . PHP_EOL,
                    FILE_APPEND
                );
                continue;
            }
        }
        if (!empty($batchData)) {
            Ruang::insert($batchData);
        }
        fclose($file);
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        $this->command->info("Successfully imported {$totalRecords} ruang records!");
        if ($errorCount > 0) {
            $this->command->warn("Skipped {$errorCount} records due to errors or missing data.");
        }
    }
}