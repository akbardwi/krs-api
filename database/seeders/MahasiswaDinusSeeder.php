<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\MahasiswaDinus;
use Illuminate\Support\Facades\DB;

class MahasiswaDinusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $csvFile = resource_path('csv/mahasiswa_dinus.csv');
        
        if (!file_exists($csvFile)) {
            $this->command->error('CSV file not found: ' . $csvFile);
            return;
        }

        $this->command->info('Starting to import mahasiswa data from CSV...');
        
        // Disable foreign key checks to speed up import
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        
        // Truncate table before importing
        MahasiswaDinus::truncate();
        
        $file = fopen($csvFile, 'r');
        $header = fgetcsv($file); // Skip header row
        
        $batchData = [];
        $batchSize = 1000; // Process in batches of 1000 records
        $totalRecords = 0;
        
        while (($row = fgetcsv($file)) !== FALSE) {
            // Clean the data
            $nim_dinus = trim($row[0], '"');
            $ta_masuk = trim($row[1], '"');
            $prodi = trim($row[2], '"');
            $akdm_stat = trim($row[3], '"');
            $kelas = trim($row[4], '"');
            $pass_mhs = trim($row[5], '"');
            
            $batchData[] = [
                'nim_dinus' => $nim_dinus,
                'ta_masuk' => $ta_masuk,
                'prodi' => $prodi,
                'akdm_stat' => $akdm_stat,
                'kelas' => $kelas,
                'pass_mhs' => $pass_mhs,
            ];
            
            $totalRecords++;
            
            // Insert batch when it reaches the batch size
            if (count($batchData) >= $batchSize) {
                MahasiswaDinus::insert($batchData);
                $this->command->info("Imported {$totalRecords} records...");
                $batchData = []; // Reset batch
            }
        }
        
        // Insert remaining records
        if (!empty($batchData)) {
            MahasiswaDinus::insert($batchData);
        }
        
        fclose($file);
        
        // Re-enable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        
        $this->command->info("Successfully imported {$totalRecords} mahasiswa records!");
    }
}
