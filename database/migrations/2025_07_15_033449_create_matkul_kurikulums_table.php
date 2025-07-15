<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('matkul_kurikulum', function (Blueprint $table) {
            $table->integer('kur_id');
            $table->string('kdmk');
            $table->string('nmmk');
            $table->string('nmen');
            $table->enum('tp', ['T', 'P', 'TP']);
            $table->integer('sks');
            $table->smallInteger('sks_t');
            $table->smallInteger('sks_p');
            $table->integer('smt');
            $table->tinyInteger('jns_smt');
            $table->boolean('aktif');
            $table->string('kur_nama');
            $table->enum('kelompok_makul', ['MPK', 'MKK', 'MKB', 'MKD', 'MBB', 'MPB']);
            $table->boolean('kur_aktif');
            $table->enum('jenis_matkul', ['wajib', 'pilihan']);
            
            // Add index for foreign key constraint
            $table->index('kdmk');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('matkul_kurikulum');
    }
};
