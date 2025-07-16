<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            TahunAjaranSeeder::class,
            MahasiswaDinusSeeder::class,
            MhsDipaketkanSeeder::class,
            HerregistMahasiswaSeeder::class,
            MhsIjinKrsSeeder::class,
            TagihanMhsSeeder::class,
            IpSemesterSeeder::class,
            HariSeeder::class,
            MatkulKurikulumSeeder::class,
            SesiKuliahSeeder::class,
            SesiKuliahBentrokSeeder::class,
            RuangSeeder::class,
            JadwalTawarSeeder::class,
            KrsRecordSeeder::class,
            KrsRecordLogSeeder::class,
            DaftarNilaiSeeder::class,
            ValidasiKrsMhsSeeder::class,
            JadwalInputKrsSeeder::class,
        ]);
        
        // User::factory(10)->create();

        // User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);
    }
}
