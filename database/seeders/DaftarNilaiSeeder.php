<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\DaftarNilai;
use Illuminate\Support\Facades\DB;

class DaftarNilaiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $csvFile = resource_path('csv/daftar_nilai.csv');
        
        if (!file_exists($csvFile)) {
            $this->command->error('CSV file not found: ' . $csvFile);
            return;
        }

        $this->command->info('Starting to import daftar nilai data from CSV...');
        $this->command->warn('This is a large dataset (495,000+ records), please be patient...');
        
        // Disable foreign key checks to speed up import
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        
        // Truncate table before importing
        DaftarNilai::truncate();
        
        $file = fopen($csvFile, 'r');
        $header = fgetcsv($file); // Skip header row
        
        $batchData = [];
        $batchSize = 1000; // Process in batches of 1000 records
        $totalRecords = 0;
        $errorCount = 0;
        
        while (($row = fgetcsv($file)) !== FALSE) {
            // Skip empty rows
            if (empty(array_filter($row))) {
                continue;
            }
            
            try {
                // Parse and clean the data based on header: "_id","nim_dinus","kdmk","nl","hide"
                $_id = !empty($row[0]) ? (int)trim($row[0], '"') : null;
                $nim_dinus = !empty($row[1]) ? trim($row[1], '"') : null;
                $kdmk = !empty($row[2]) ? trim($row[2]) : null;
                $nl = !empty($row[3]) ? trim($row[3]) : null;
                $hide = !empty($row[4]) ? (int)$row[4] : 0;
                
                // Validate nl field (should be max 2 characters)
                if (!is_null($nl) && strlen($nl) > 2) {
                    $nl = substr($nl, 0, 2);
                }
                
                $batchData[] = [
                    '_id' => $_id,
                    'nim_dinus' => $nim_dinus,
                    'kdmk' => $kdmk,
                    'nl' => $nl,
                    'hide' => $hide,
                ];
                
                $totalRecords++;
                
                // Insert batch when it reaches the batch size
                if (count($batchData) >= $batchSize) {
                    DaftarNilai::insert($batchData);
                    $this->command->info("Imported {$totalRecords} records...");
                    $batchData = []; // Reset batch
                }
                
            } catch (\Exception $e) {
                $errorCount++;
                $this->command->warn("Error processing row " . ($totalRecords + 1) . ": " . $e->getMessage());
                continue;
            }
        }
        
        // Insert remaining records
        if (!empty($batchData)) {
            DaftarNilai::insert($batchData);
        }
        
        fclose($file);
        
        // Re-enable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        
        $this->command->info("Successfully imported {$totalRecords} daftar nilai records!");
        if ($errorCount > 0) {
            $this->command->warn("Skipped {$errorCount} records due to errors or missing data.");
        }
    }
}
