<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\MhsDipaketkan;
use Illuminate\Support\Facades\DB;

class MhsDipaketkanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $csvFile = resource_path('csv/mhs_dipaketkan.csv');
        
        if (!file_exists($csvFile)) {
            $this->command->error('CSV file not found: ' . $csvFile);
            return;
        }

        $this->command->info('Starting to import mahasiswa dipaketkan data from CSV...');
        
        // Disable foreign key checks to speed up import
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        
        // Truncate table before importing
        MhsDipaketkan::truncate();
        
        $file = fopen($csvFile, 'r');
        $header = fgetcsv($file); // Skip header row
        
        $batchData = [];
        $batchSize = 500; // Process in batches of 500 records
        $totalRecords = 0;
        $errorCount = 0;
        
        while (($row = fgetcsv($file)) !== FALSE) {
            // Skip empty rows
            if (empty(array_filter($row))) {
                continue;
            }
            
            try {
                // Parse and clean the data based on header: nim_dinus,ta_masuk_mhs
                $nim_dinus = !empty($row[0]) ? trim($row[0]) : null;
                $ta_masuk_mhs = !empty($row[1]) ? (int)$row[1] : null;
                
                // Skip if essential fields are missing
                if (is_null($nim_dinus) || is_null($ta_masuk_mhs)) {
                    $errorCount++;
                    continue;
                }
                
                $batchData[] = [
                    'nim_dinus' => $nim_dinus,
                    'ta_masuk_mhs' => $ta_masuk_mhs,
                ];
                
                $totalRecords++;
                
                // Insert batch when it reaches the batch size
                if (count($batchData) >= $batchSize) {
                    try {
                        MhsDipaketkan::insert($batchData);
                        $this->command->info("Imported {$totalRecords} records...");
                    } catch (\Exception $e) {
                        // Handle unique constraint violations (primary key duplicates)
                        $this->command->warn("Batch insert failed, trying individual inserts: " . $e->getMessage());
                        foreach ($batchData as $record) {
                            try {
                                MhsDipaketkan::create($record);
                            } catch (\Exception $individualError) {
                                $errorCount++;
                                $this->command->warn("Skipped record with nim_dinus={$record['nim_dinus']}: " . $individualError->getMessage());
                            }
                        }
                    }
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
            try {
                MhsDipaketkan::insert($batchData);
            } catch (\Exception $e) {
                // Handle unique constraint violations (primary key duplicates)
                $this->command->warn("Final batch insert failed, trying individual inserts: " . $e->getMessage());
                foreach ($batchData as $record) {
                    try {
                        MhsDipaketkan::create($record);
                    } catch (\Exception $individualError) {
                        $errorCount++;
                        $this->command->warn("Skipped record with nim_dinus={$record['nim_dinus']}: " . $individualError->getMessage());
                    }
                }
            }
        }
        
        fclose($file);
        
        // Re-enable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        
        $this->command->info("Successfully imported {$totalRecords} mahasiswa dipaketkan records!");
        if ($errorCount > 0) {
            $this->command->warn("Skipped {$errorCount} records due to errors or missing data.");
        }
    }
}
