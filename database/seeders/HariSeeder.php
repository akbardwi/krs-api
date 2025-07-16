<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class HariSeeder extends Seeder
{
    public function run(): void
    {
        $csvFile = resource_path('csv/hari.csv');
        if (!file_exists($csvFile)) {
            $this->command->error('CSV file not found: ' . $csvFile);
            return;
        }

        $this->command->info('Starting to import hari data from CSV...');
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('hari')->truncate();
        $file = fopen($csvFile, 'r');
        $header = fgetcsv($file);
        $batchData = [];
        $batchSize = 100;
        $totalRecords = 0;
        while (($row = fgetcsv($file)) !== FALSE) {
            $batchData[] = [
                'id' => $row[0],
                'nama' => $row[1],
                'nama_en' => $row[2],
            ];
            $totalRecords++;
            if (count($batchData) >= $batchSize) {
                DB::table('hari')->insert($batchData);
                $batchData = [];
            }
        }
        if (!empty($batchData)) {
            DB::table('hari')->insert($batchData);
        }
        fclose($file);
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        $this->command->info("Successfully imported {$totalRecords} hari records!");
    }
}
