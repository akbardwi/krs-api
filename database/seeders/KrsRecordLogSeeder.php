<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\KrsRecordLog;
use Illuminate\Support\Facades\DB;

class KrsRecordLogSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $csvFile = resource_path('csv/krs_record_log.csv');
        
        if (!file_exists($csvFile)) {
            $this->command->error('CSV file not found: ' . $csvFile);
            return;
        }

        $this->command->info('Starting to import KRS record log data from CSV...');
        
        // Disable foreign key checks to speed up import
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        
        // Truncate table before importing
        KrsRecordLog::truncate();
        
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
                // Parse and clean the data based on header: id_krs,kdmk,aksi,id_jadwal,lastUpdate,nim_dinus
                $id_krs = !empty($row[0]) ? (int)$row[0] : null;
                $kdmk = !empty($row[1]) ? trim($row[1]) : null;
                $aksi = !empty($row[2]) ? (int)$row[2] : null;
                $id_jadwal = !empty($row[3]) ? (int)$row[3] : null;
                
                // Handle datetime field
                $lastUpdate = null;
                if (!empty($row[4]) && $row[4] !== '' && $row[4] !== '0000-00-00 00:00:00') {
                    $lastUpdate = $row[4];
                }
                
                $nim_dinus = !empty($row[5]) ? trim($row[5]) : null;
                
                // ip_addr is not in CSV, so we'll set it to null
                $ip_addr = null;
                
                // Validate aksi field (1=insert, 2=delete, based on migration comment)
                // But CSV shows value 3 as well, so we'll allow it
                if (!is_null($aksi) && !in_array($aksi, [1, 2, 3])) {
                    $this->command->warn("Invalid aksi value: {$aksi} at record " . ($totalRecords + 1));
                }
                
                $batchData[] = [
                    'id_krs' => $id_krs,
                    'nim_dinus' => $nim_dinus,
                    'kdmk' => $kdmk,
                    'aksi' => $aksi,
                    'id_jadwal' => $id_jadwal,
                    'ip_addr' => $ip_addr,
                    'lastUpdate' => $lastUpdate,
                ];
                
                $totalRecords++;
                
                // Insert batch when it reaches the batch size
                if (count($batchData) >= $batchSize) {
                    KrsRecordLog::insert($batchData);
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
            KrsRecordLog::insert($batchData);
        }
        
        fclose($file);
        
        // Re-enable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        
        $this->command->info("Successfully imported {$totalRecords} KRS record log records!");
        if ($errorCount > 0) {
            $this->command->warn("Skipped {$errorCount} records due to errors or missing data.");
        }
    }
}
