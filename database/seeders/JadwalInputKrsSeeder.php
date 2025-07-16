<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\JadwalInputKrs;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class JadwalInputKrsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $csvFile = resource_path('csv/jadwal_input_krs.csv');
        
        if (!file_exists($csvFile)) {
            $this->command->error('CSV file not found: ' . $csvFile);
            return;
        }

        $this->command->info('Starting to import jadwal input KRS data from CSV...');
        
        // Disable foreign key checks to speed up import
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        
        // Truncate table before importing
        JadwalInputKrs::truncate();
        
        $file = fopen($csvFile, 'r');
        $header = fgetcsv($file); // Skip header row
        
        $batchData = [];
        $batchSize = 50; // Process in smaller batches since data is less
        $totalRecords = 0;
        $errorCount = 0;
        
        while (($row = fgetcsv($file)) !== FALSE) {
            // Skip empty rows
            if (empty(array_filter($row))) {
                continue;
            }
            
            try {
                // Parse and clean the data based on header: id,ta,prodi,tgl_mulai,tgl_selesai
                $id = !empty($row[0]) ? (int)$row[0] : null;
                $ta = !empty($row[1]) ? (int)$row[1] : null;
                $prodi = !empty($row[2]) ? trim($row[2]) : null;
                
                // Handle datetime fields with custom format: dd/mm/yy HH.mm
                $tgl_mulai = null;
                if (!empty($row[3])) {
                    try {
                        // Convert from "13/08/24 08.00" to datetime
                        $tgl_mulai = Carbon::createFromFormat('d/m/y H.i', $row[3])->format('Y-m-d H:i:s');
                    } catch (\Exception $e) {
                        $this->command->warn("Invalid tgl_mulai format: {$row[3]} at record " . ($totalRecords + 1));
                    }
                }
                
                $tgl_selesai = null;
                if (!empty($row[4])) {
                    try {
                        // Convert from "13/08/24 12.00" to datetime
                        $tgl_selesai = Carbon::createFromFormat('d/m/y H.i', $row[4])->format('Y-m-d H:i:s');
                    } catch (\Exception $e) {
                        $this->command->warn("Invalid tgl_selesai format: {$row[4]} at record " . ($totalRecords + 1));
                    }
                }
                
                $batchData[] = [
                    'id' => $id,
                    'ta' => $ta,
                    'prodi' => $prodi,
                    'tgl_mulai' => $tgl_mulai,
                    'tgl_selesai' => $tgl_selesai,
                ];
                
                $totalRecords++;
                
                // Insert batch when it reaches the batch size
                if (count($batchData) >= $batchSize) {
                    try {
                        JadwalInputKrs::insert($batchData);
                        $this->command->info("Imported {$totalRecords} records...");
                    } catch (\Exception $e) {
                        // Handle primary key duplicates
                        $this->command->warn("Batch insert failed, trying individual inserts: " . $e->getMessage());
                        foreach ($batchData as $record) {
                            try {
                                JadwalInputKrs::create($record);
                            } catch (\Exception $individualError) {
                                $errorCount++;
                                $this->command->warn("Skipped record with id={$record['id']}: " . $individualError->getMessage());
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
                JadwalInputKrs::insert($batchData);
            } catch (\Exception $e) {
                // Handle primary key duplicates
                $this->command->warn("Final batch insert failed, trying individual inserts: " . $e->getMessage());
                foreach ($batchData as $record) {
                    try {
                        JadwalInputKrs::create($record);
                    } catch (\Exception $individualError) {
                        $errorCount++;
                        $this->command->warn("Skipped record with id={$record['id']}: " . $individualError->getMessage());
                    }
                }
            }
        }
        
        fclose($file);
        
        // Re-enable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        
        $this->command->info("Successfully imported {$totalRecords} jadwal input KRS records!");
        if ($errorCount > 0) {
            $this->command->warn("Skipped {$errorCount} records due to errors or missing data.");
        }
    }
}
