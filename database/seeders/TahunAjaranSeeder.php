<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\TahunAjaran;
use Illuminate\Support\Facades\DB;

class TahunAjaranSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $csvFile = resource_path('csv/tahun_ajaran.csv');
        
        if (!file_exists($csvFile)) {
            $this->command->error('CSV file not found: ' . $csvFile);
            return;
        }

        $this->command->info('Starting to import tahun ajaran data from CSV...');
        
        // Disable foreign key checks to speed up import
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        
        // Truncate table before importing
        TahunAjaran::truncate();
        
        $file = fopen($csvFile, 'r');
        $header = fgetcsv($file); // Skip header row
        
        $batchData = [];
        $batchSize = 100; // Process in smaller batches since data is less
        $totalRecords = 0;
        
        while (($row = fgetcsv($file)) !== FALSE) {
            // Skip empty rows
            if (empty(array_filter($row))) {
                continue;
            }
            
            // Parse and clean the data
            $id = !empty($row[0]) ? (int)$row[0] : null;
            $kode = !empty($row[1]) ? (int)$row[1] : null;
            $tahun_akhir = !empty($row[2]) ? $row[2] : null;
            $tahun_awal = !empty($row[3]) ? $row[3] : null;
            $jns_smt = !empty($row[4]) ? (int)$row[4] : 0;
            $set_aktif = !empty($row[5]) ? (bool)$row[5] : false;
            $biku_tagih_jenis = !empty($row[6]) ? (int)$row[6] : 0;
            
            // Handle datetime fields
            $update_time = !empty($row[7]) && $row[7] !== '' ? $row[7] : null;
            $update_id = !empty($row[8]) && $row[8] !== '' ? $row[8] : null;
            $update_host = !empty($row[9]) && $row[9] !== '' ? $row[9] : null;
            $added_time = !empty($row[10]) && $row[10] !== '' ? $row[10] : null;
            $added_id = !empty($row[11]) && $row[11] !== '' ? $row[11] : null;
            $added_host = !empty($row[12]) && $row[12] !== '' ? $row[12] : null;
            $tgl_masuk = !empty($row[13]) && $row[13] !== '' ? $row[13] : null;
            
            // Skip if essential fields are missing
            if (is_null($kode)) {
                continue;
            }
            
            $batchData[] = [
                'id' => $id,
                'kode' => $kode,
                'tahun_akhir' => $tahun_akhir,
                'tahun_awal' => $tahun_awal,
                'jns_smt' => $jns_smt,
                'set_aktif' => $set_aktif,
                'biku_tagih_jenis' => $biku_tagih_jenis,
                'update_time' => $update_time,
                'update_id' => $update_id,
                'update_host' => $update_host,
                'added_time' => $added_time,
                'added_id' => $added_id,
                'added_host' => $added_host,
                'tgl_masuk' => $tgl_masuk,
            ];
            
            $totalRecords++;
            
            // Insert batch when it reaches the batch size
            if (count($batchData) >= $batchSize) {
                TahunAjaran::insert($batchData);
                $this->command->info("Imported {$totalRecords} records...");
                $batchData = []; // Reset batch
            }
        }
        
        // Insert remaining records
        if (!empty($batchData)) {
            TahunAjaran::insert($batchData);
        }
        
        fclose($file);
        
        // Re-enable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        
        $this->command->info("Successfully imported {$totalRecords} tahun ajaran records!");
    }
}
