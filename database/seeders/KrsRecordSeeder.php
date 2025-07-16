<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\KrsRecord;
use Illuminate\Support\Facades\DB;

class KrsRecordSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $csvFile = resource_path('csv/krs_record.csv');
        
        if (!file_exists($csvFile)) {
            $this->command->error('CSV file not found: ' . $csvFile);
            return;
        }

        $this->command->info('Starting to import KRS record data from CSV...');
        
        // Disable foreign key checks to speed up import
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        
        // Truncate table before importing
        KrsRecord::truncate();
        
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
                // Parse and clean the data
                $id = !empty($row[0]) ? (int)trim($row[0], '"') : null;
                $ta = !empty($row[1]) ? (int)trim($row[1], '"') : null;
                $kdmk = !empty($row[2]) ? trim($row[2]) : null;
                $id_jadwal = !empty($row[3]) ? (int)$row[3] : null;
                $nim_dinus = !empty($row[4]) ? trim($row[4]) : null;
                $sts = !empty($row[5]) ? trim($row[5]) : null;
                $sks = !empty($row[6]) ? (int)$row[6] : 0;
                $modul = !empty($row[7]) ? (bool)$row[7] : false;
                
                // Skip if essential fields are missing
                if (is_null($ta) || is_null($kdmk) || is_null($id_jadwal) || is_null($nim_dinus) || is_null($sts)) {
                    $errorCount++;
                    continue;
                }
                
                // Validate sts field (should be single character)
                if (strlen($sts) > 1) {
                    $sts = substr($sts, 0, 1);
                }
                
                $batchData[] = [
                    'id' => $id,
                    'ta' => $ta,
                    'kdmk' => $kdmk,
                    'id_jadwal' => $id_jadwal,
                    'nim_dinus' => $nim_dinus,
                    'sts' => $sts,
                    'sks' => $sks,
                    'modul' => $modul,
                ];
                
                $totalRecords++;
                
                // Insert batch when it reaches the batch size
                if (count($batchData) >= $batchSize) {
                    KrsRecord::insert($batchData);
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
            KrsRecord::insert($batchData);
        }
        
        fclose($file);
        
        // Re-enable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        
        $this->command->info("Successfully imported {$totalRecords} KRS record records!");
        if ($errorCount > 0) {
            $this->command->warn("Skipped {$errorCount} records due to errors or missing data.");
        }
    }
}
