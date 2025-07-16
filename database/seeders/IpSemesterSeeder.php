<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\IpSemester;
use Illuminate\Support\Facades\DB;

class IpSemesterSeeder extends Seeder
{
    public function run(): void
    {
        $csvFile = resource_path('csv/ip_semester.csv');
        
        if (!file_exists($csvFile)) {
            $this->command->error('CSV file not found: ' . $csvFile);
            return;
        }

        $this->command->info('Starting to import ip_semester data from CSV...');
        
        // Disable foreign key checks to speed up import
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        
        // Truncate table before importing
        IpSemester::truncate();
        
        $file = fopen($csvFile, 'r');
        $header = fgetcsv($file); // Skip header row
        
        $batchData = [];
        $batchSize = 1000; // Process in batches of 1000 records
        $totalRecords = 0;
        
        while (($row = fgetcsv($file)) !== FALSE) {
            $batchData[] = [
                'id' => $row[0],
                'ta' => $row[1],
                'sks' => $row[2],
                'ips' => $row[3],
                'last_update' => $row[4],
                'nim_dinus' => $row[5],
            ];
            $totalRecords++;
            if (count($batchData) >= $batchSize) {
                IpSemester::insert($batchData);
                $this->command->info("Imported {$totalRecords} records...");
                $batchData = [];
            }
        }
        if (!empty($batchData)) {
            IpSemester::insert($batchData);
        }
        fclose($file);
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        $this->command->info("Successfully imported {$totalRecords} ip_semester records!");
    }
}
