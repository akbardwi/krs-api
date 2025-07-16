<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SesiKuliah;
use Illuminate\Support\Facades\DB;

class SesiKuliahSeeder extends Seeder
{
    public function run(): void
    {
        $csvFile = resource_path('csv/sesi_kuliah.csv');
        if (!file_exists($csvFile)) {
            $this->command->error('CSV file not found: ' . $csvFile);
            return;
        }

        $this->command->info('Starting to import sesi_kuliah data from CSV...');
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        SesiKuliah::truncate();
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
                    'jam' => $row[1],
                    'sks' => $row[2],
                    'jam_mulai' => $row[3],
                    'jam_selesai' => $row[4],
                    'status' => $row[5],
                ];
                $totalRecords++;
                if (count($batchData) >= $batchSize) {
                    SesiKuliah::insert($batchData);
                    $this->command->info("Imported {$totalRecords} records...");
                    $batchData = [];
                }
            } catch (\Exception $e) {
                $errorCount++;
                $this->command->warn("Error processing row {$totalRecords}: " . $e->getMessage());
                file_put_contents(
                    storage_path('logs/sesi_kuliah_seeder_errors.txt'),
                    "Row {$totalRecords}: " . $e->getMessage() . " | Data: " . json_encode($row) . PHP_EOL,
                    FILE_APPEND
                );
                continue;
            }
        }
        if (!empty($batchData)) {
            SesiKuliah::insert($batchData);
        }
        fclose($file);
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        $this->command->info("Successfully imported {$totalRecords} sesi_kuliah records!");
        if ($errorCount > 0) {
            $this->command->warn("Skipped {$errorCount} records due to errors or missing data.");
        }
    }
}
