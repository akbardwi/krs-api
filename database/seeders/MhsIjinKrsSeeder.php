<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\MhsIjinKrs;
use Illuminate\Support\Facades\DB;

class MhsIjinKrsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $csvFile = resource_path('csv/mhs_ijin_krs.csv');
        
        if (!file_exists($csvFile)) {
            $this->command->error('CSV file not found: ' . $csvFile);
            return;
        }

        $this->command->info('Starting to import mahasiswa ijin KRS data from CSV...');
        
        // Disable foreign key checks to speed up import
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        
        // Truncate table before importing
        MhsIjinKrs::truncate();
        
        $file = fopen($csvFile, 'r');
        $header = fgetcsv($file); // Skip header row
        
        $batchData = [];
        $batchSize = 100; // Process in smaller batches since data is less
        $totalRecords = 0;
        $errorCount = 0;
        
        while (($row = fgetcsv($file)) !== FALSE) {
            // Skip empty rows
            if (empty(array_filter($row))) {
                continue;
            }
            
            try {
                // Parse and clean the data based on header: "id","ta","ijinkan","time","nim_dinus"
                $id = !empty($row[0]) ? (int)trim($row[0], '"') : null;
                $ta = !empty($row[1]) ? (int)$row[1] : null;
                $ijinkan = !empty($row[2]) ? (bool)$row[2] : null;
                
                // Handle datetime field
                $time = null;
                if (!empty($row[3]) && $row[3] !== '' && $row[3] !== '0000-00-00 00:00:00') {
                    $time = $row[3];
                }
                
                $nim_dinus = !empty($row[4]) ? trim($row[4], '"') : null;
                
                // Skip if we have a unique constraint violation potential
                // The unique constraint is on ['ta', 'nim_dinus']
                if (is_null($ta) || is_null($nim_dinus)) {
                    $errorCount++;
                    continue;
                }
                
                $batchData[] = [
                    'id' => $id,
                    'ta' => $ta,
                    'nim_dinus' => $nim_dinus,
                    'ijinkan' => $ijinkan,
                    'time' => $time,
                ];
                
                $totalRecords++;
                
                // Insert batch when it reaches the batch size
                if (count($batchData) >= $batchSize) {
                    try {
                        MhsIjinKrs::insert($batchData);
                        $this->command->info("Imported {$totalRecords} records...");
                    } catch (\Exception $e) {
                        // Handle unique constraint violations
                        $this->command->warn("Batch insert failed, trying individual inserts: " . $e->getMessage());
                        foreach ($batchData as $record) {
                            try {
                                MhsIjinKrs::create($record);
                            } catch (\Exception $individualError) {
                                $errorCount++;
                                $this->command->warn("Skipped record with ta={$record['ta']}, nim_dinus={$record['nim_dinus']}: " . $individualError->getMessage());
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
                MhsIjinKrs::insert($batchData);
            } catch (\Exception $e) {
                // Handle unique constraint violations
                $this->command->warn("Final batch insert failed, trying individual inserts: " . $e->getMessage());
                foreach ($batchData as $record) {
                    try {
                        MhsIjinKrs::create($record);
                    } catch (\Exception $individualError) {
                        $errorCount++;
                        $this->command->warn("Skipped record with ta={$record['ta']}, nim_dinus={$record['nim_dinus']}: " . $individualError->getMessage());
                    }
                }
            }
        }
        
        fclose($file);
        
        // Re-enable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        
        $this->command->info("Successfully imported {$totalRecords} mahasiswa ijin KRS records!");
        if ($errorCount > 0) {
            $this->command->warn("Skipped {$errorCount} records due to errors or missing data.");
        }
    }
}
