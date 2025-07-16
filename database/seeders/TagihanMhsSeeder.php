<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\TagihanMhs;
use Illuminate\Support\Facades\DB;

class TagihanMhsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $csvFile = resource_path('csv/tagihan_mhs.csv');
        
        if (!file_exists($csvFile)) {
            $this->command->error('CSV file not found: ' . $csvFile);
            return;
        }

        $this->command->info('Starting to import tagihan mahasiswa data from CSV...');
        
        // Disable foreign key checks to speed up import
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        
        // Truncate table before importing
        TagihanMhs::truncate();
        
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
                $id = !empty($row[0]) ? (int)$row[0] : null;
                $ta = !empty($row[1]) ? (int)$row[1] : null;
                $nim_dinus = !empty($row[2]) ? trim($row[2], '"') : null;
                $spp_bayar = !empty($row[3]) ? (bool)$row[3] : false;
                
                // Handle datetime field - also check for empty quoted strings
                $spp_bayar_date = null;
                if (!empty($row[4]) && $row[4] !== '' && $row[4] !== '0000-00-00 00:00:00' && $row[4] !== '""') {
                    $spp_bayar_date = $row[4];
                }
                
                $spp_host = (!empty($row[5]) && $row[5] !== '' && $row[5] !== '""') ? $row[5] : null;
                $spp_status = !empty($row[6]) ? (bool)$row[6] : false;
                
                // Handle spp_dispensasi - can be quoted string or number
                $spp_dispensasi = null;
                if (!empty($row[7]) && $row[7] !== '' && $row[7] !== '"0"' && $row[7] !== '0') {
                    $spp_dispensasi = (int)trim($row[7], '"');
                }
                
                $spp_bank = (!empty($row[8]) && $row[8] !== '' && $row[8] !== '""') ? $row[8] : null;
                $spp_transaksi = (!empty($row[9]) && $row[9] !== '' && $row[9] !== '""') ? trim($row[9], '"') : null;
                
                // Skip if essential fields are missing
                if (is_null($ta) || is_null($nim_dinus)) {
                    $errorCount++;
                    continue;
                }
                
                $batchData[] = [
                    'id' => $id,
                    'ta' => $ta,
                    'nim_dinus' => $nim_dinus,
                    'spp_bayar' => $spp_bayar,
                    'spp_bayar_date' => $spp_bayar_date,
                    'spp_host' => $spp_host,
                    'spp_status' => $spp_status,
                    'spp_dispensasi' => $spp_dispensasi,
                    'spp_bank' => $spp_bank,
                    'spp_transaksi' => $spp_transaksi,
                ];
                
                $totalRecords++;
                
                // Insert batch when it reaches the batch size
                if (count($batchData) >= $batchSize) {
                    TagihanMhs::insert($batchData);
                    $this->command->info("Imported {$totalRecords} records...");
                    $batchData = []; // Reset batch
                }
            } catch (\Exception $e) {
                $errorCount++;
                $this->command->warn("Error processing row {$totalRecords}: " . $e->getMessage());
                file_put_contents(
                    storage_path('logs/tagihan_mhs_seeder_errors.txt'),
                    "Row {$totalRecords}: " . $e->getMessage() . " | Data: " . json_encode($row) . PHP_EOL,
                    FILE_APPEND
                );
                continue;
            }
        }
        
        // Insert remaining records
        if (!empty($batchData)) {
            TagihanMhs::insert($batchData);
        }
        
        fclose($file);
        
        // Re-enable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        
        $this->command->info("Successfully imported {$totalRecords} tagihan mahasiswa records!");
        if ($errorCount > 0) {
            $this->command->warn("Skipped {$errorCount} records due to errors or missing data.");
        }
    }
}
