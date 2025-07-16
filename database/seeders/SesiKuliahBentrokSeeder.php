<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SesiKuliahBentrokSeeder extends Seeder
{
    public function run(): void
    {
        $csvFile = resource_path('csv/sesi_kuliah_bentrok.csv');
        if (!file_exists($csvFile)) {
            $this->command->error('CSV file not found: ' . $csvFile);
            return;
        }

        $this->command->info('Starting to import sesi_kuliah_bentrok data from CSV...');
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('sesi_kuliah_bentrok')->truncate();
        $file = fopen($csvFile, 'r');
        $header = fgetcsv($file); // skip header
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
                    'id_bentrok' => $row[1],
                ];
                $totalRecords++;
                if (count($batchData) >= $batchSize) {
                    DB::table('sesi_kuliah_bentrok')->insert($batchData);
                    $this->command->info("Imported {$totalRecords} records...");
                    $batchData = [];
                }
            } catch (\Exception $e) {
                $errorCount++;
                $this->command->warn("Error processing row {$totalRecords}: " . $e->getMessage());
                file_put_contents(
                    storage_path('logs/sesi_kuliah_bentrok_seeder_errors.txt'),
                    "Row {$totalRecords}: " . $e->getMessage() . " | Data: " . json_encode($row) . PHP_EOL,
                    FILE_APPEND
                );
                continue;
            }
        }
        if (!empty($batchData)) {
            DB::table('sesi_kuliah_bentrok')->insert($batchData);
        }
        fclose($file);
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        $this->command->info("Successfully imported {$totalRecords} sesi_kuliah_bentrok records!");
        if ($errorCount > 0) {
            $this->command->warn("Skipped {$errorCount} records due to errors or missing data.");
        }
    }
}