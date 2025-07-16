<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\JadwalTawar;
use Illuminate\Support\Facades\DB;

class JadwalTawarSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $csvFile = resource_path('csv/jadwal_tawar.csv');
        
        if (!file_exists($csvFile)) {
            $this->command->error('CSV file not found: ' . $csvFile);
            return;
        }

        $this->command->info('Starting to import jadwal tawar data from CSV...');
        
        // Disable foreign key checks to speed up import
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        
        // Truncate table before importing
        JadwalTawar::truncate();
        
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
                // Parse and clean the data
                $id = !empty($row[0]) ? (int)$row[0] : null;
                $ta = !empty($row[1]) ? (int)$row[1] : null;
                $kdmk = !empty($row[2]) ? trim($row[2]) : null;
                $klpk = !empty($row[3]) ? trim($row[3]) : null;
                $klpk_2 = (!empty($row[4]) && $row[4] !== '') ? trim($row[4]) : null;
                $kdds = !empty($row[5]) ? (int)$row[5] : 0;
                $kdds2 = (!empty($row[6]) && $row[6] !== '0') ? (int)$row[6] : null;
                $jmax = !empty($row[7]) ? (int)$row[7] : 0;
                $jsisa = !empty($row[8]) ? (int)$row[8] : 0;
                
                // Handle hari fields (tinyInteger, 0 means null/not used)
                $id_hari1 = (!empty($row[9]) && $row[9] !== '0') ? (int)$row[9] : 0;
                $id_hari2 = (!empty($row[10]) && $row[10] !== '0') ? (int)$row[10] : 0;
                $id_hari3 = (!empty($row[11]) && $row[11] !== '0') ? (int)$row[11] : 0;
                
                // Handle sesi fields (smallInteger unsigned, 0 means null/not used)
                $id_sesi1 = (!empty($row[12]) && $row[12] !== '0') ? (int)$row[12] : 0;
                $id_sesi2 = (!empty($row[13]) && $row[13] !== '0') ? (int)$row[13] : 0;
                $id_sesi3 = (!empty($row[14]) && $row[14] !== '0') ? (int)$row[14] : 0;
                
                // Handle ruang fields (unsignedBigInteger, 0 means null/not used)
                $id_ruang1 = (!empty($row[15]) && $row[15] !== '0') ? (int)$row[15] : 0;
                $id_ruang2 = (!empty($row[16]) && $row[16] !== '0') ? (int)$row[16] : 0;
                $id_ruang3 = (!empty($row[17]) && $row[17] !== '0') ? (int)$row[17] : 0;
                
                $jns_jam = !empty($row[18]) ? (int)$row[18] : 1;
                $open_class = !empty($row[19]) ? (bool)$row[19] : true;
                
                // Skip if essential fields are missing
                if (is_null($ta) || is_null($kdmk) || is_null($klpk)) {
                    $errorCount++;
                    continue;
                }
                
                $batchData[] = [
                    'id' => $id,
                    'ta' => $ta,
                    'kdmk' => $kdmk,
                    'klpk' => $klpk,
                    'klpk_2' => $klpk_2,
                    'kdds' => $kdds,
                    'kdds2' => $kdds2,
                    'jmax' => $jmax,
                    'jsisa' => $jsisa,
                    'id_hari1' => $id_hari1,
                    'id_hari2' => $id_hari2,
                    'id_hari3' => $id_hari3,
                    'id_sesi1' => $id_sesi1,
                    'id_sesi2' => $id_sesi2,
                    'id_sesi3' => $id_sesi3,
                    'id_ruang1' => $id_ruang1,
                    'id_ruang2' => $id_ruang2,
                    'id_ruang3' => $id_ruang3,
                    'jns_jam' => $jns_jam,
                    'open_class' => $open_class,
                ];
                
                $totalRecords++;
                
                // Insert batch when it reaches the batch size
                if (count($batchData) >= $batchSize) {
                    JadwalTawar::insert($batchData);
                    $this->command->info("Imported {$totalRecords} records...");
                    $batchData = []; // Reset batch
                }
                
            } catch (\Exception $e) {
                $errorCount++;
                $this->command->warn("Error processing row {$totalRecords}: " . $e->getMessage());
                file_put_contents(
                    storage_path('logs/jadwal_tawar_seeder_errors.txt'),
                    "Row {$totalRecords}: " . $e->getMessage() . " | Data: " . json_encode($row) . PHP_EOL,
                    FILE_APPEND
                );
                continue;
            }
        }
        
        // Insert remaining records
        if (!empty($batchData)) {
            JadwalTawar::insert($batchData);
        }
        
        fclose($file);
        
        // Re-enable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        
        $this->command->info("Successfully imported {$totalRecords} jadwal tawar records!");
        if ($errorCount > 0) {
            $this->command->warn("Skipped {$errorCount} records due to errors or missing data.");
        }
    }
}
