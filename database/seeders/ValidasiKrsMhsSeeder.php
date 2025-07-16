<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\ValidasiKrsMhs;
use Illuminate\Support\Facades\DB;

class ValidasiKrsMhsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $csvFile = resource_path('csv/validasi_krs_mhs.csv');
        
        if (!file_exists($csvFile)) {
            $this->command->error('CSV file not found: ' . $csvFile);
            return;
        }

        $this->command->info('Starting to import validasi KRS mahasiswa data from CSV...');
        
        // Disable foreign key checks to speed up import
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        
        // Truncate table before importing
        ValidasiKrsMhs::truncate();
        
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
                // Parse and clean the data based on header: "id","nim_dinus","job_date","job_host","job_agent","ta"
                $id = !empty($row[0]) ? (int)trim($row[0], '"') : null;
                $nim_dinus = !empty($row[1]) ? trim($row[1], '"') : null;
                
                // Handle datetime field
                $job_date = null;
                if (!empty($row[2]) && $row[2] !== '' && $row[2] !== '0000-00-00 00:00:00') {
                    $job_date = $row[2];
                }
                
                $job_host = !empty($row[3]) ? trim($row[3], '"') : null;
                
                // Handle job_agent field - some entries might not be quoted
                $job_agent = null;
                if (!empty($row[4]) && $row[4] !== '') {
                    // Remove surrounding quotes if they exist, but preserve internal quotes
                    $job_agent = $row[4];
                    if (substr($job_agent, 0, 1) === '"' && substr($job_agent, -1) === '"') {
                        $job_agent = substr($job_agent, 1, -1);
                    }
                    // Truncate if too long (max 255 characters as per migration)
                    if (strlen($job_agent) > 255) {
                        $job_agent = substr($job_agent, 0, 255);
                    }
                }
                
                $ta = !empty($row[5]) ? (int)$row[5] : null;
                
                // Skip if essential fields are missing (nim_dinus is required per migration)
                if (is_null($nim_dinus)) {
                    $errorCount++;
                    continue;
                }
                
                $batchData[] = [
                    'id' => $id,
                    'nim_dinus' => $nim_dinus,
                    'job_date' => $job_date,
                    'job_host' => $job_host,
                    'job_agent' => $job_agent,
                    'ta' => $ta,
                ];
                
                $totalRecords++;
                
                // Insert batch when it reaches the batch size
                if (count($batchData) >= $batchSize) {
                    ValidasiKrsMhs::insert($batchData);
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
            ValidasiKrsMhs::insert($batchData);
        }
        
        fclose($file);
        
        // Re-enable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        
        $this->command->info("Successfully imported {$totalRecords} validasi KRS mahasiswa records!");
        if ($errorCount > 0) {
            $this->command->warn("Skipped {$errorCount} records due to errors or missing data.");
        }
    }
}
