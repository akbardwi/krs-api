<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\HerregistMahasiswa;
use Illuminate\Support\Facades\DB;

class HerregistMahasiswaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $csvFile = resource_path('csv/herregist_mahasiswa.csv');
        
        if (!file_exists($csvFile)) {
            $this->command->error('CSV file not found: ' . $csvFile);
            return;
        }

        $this->command->info('Starting to import herregistrasi mahasiswa data from CSV...');
        
        // Disable foreign key checks to speed up import
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        
        // Truncate table before importing
        HerregistMahasiswa::truncate();
        
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
                // Parse and clean the data based on header: "id","nim_dinus","ta","date_reg"
                $id = !empty($row[0]) ? (int)trim($row[0], '"') : null;
                $nim_dinus = !empty($row[1]) ? trim($row[1], '"') : null;
                $ta = !empty($row[2]) ? (int)trim($row[2], '"') : null;
                
                // Handle datetime field
                $date_reg = null;
                if (!empty($row[3]) && $row[3] !== '' && $row[3] !== '0000-00-00 00:00:00') {
                    $date_reg = $row[3];
                }
                
                $batchData[] = [
                    'id' => $id,
                    'nim_dinus' => $nim_dinus,
                    'ta' => $ta,
                    'date_reg' => $date_reg,
                ];
                
                $totalRecords++;
                
                // Insert batch when it reaches the batch size
                if (count($batchData) >= $batchSize) {
                    HerregistMahasiswa::insert($batchData);
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
            HerregistMahasiswa::insert($batchData);
        }
        
        fclose($file);
        
        // Re-enable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        
        $this->command->info("Successfully imported {$totalRecords} herregistrasi mahasiswa records!");
        if ($errorCount > 0) {
            $this->command->warn("Skipped {$errorCount} records due to errors or missing data.");
        }
    }
}
