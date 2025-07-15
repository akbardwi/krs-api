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
        Schema::create('krs_record', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('ta')->index('PERIODE');
            $table->string('kdmk');
            $table->unsignedBigInteger('id_jadwal');
            $table->string('nim_dinus', 50)->index('MAHASISWA');
            $table->char('sts', 1);
            $table->integer('sks');
            $table->boolean('modul')->default(false);

            $table->foreign('ta')->references('kode')->on('tahun_ajaran');
            $table->foreign('kdmk')->references('kdmk')->on('matkul_kurikulum');
            $table->foreign('id_jadwal')->references('id')->on('jadwal_tawar');
            $table->foreign('nim_dinus')->references('nim_dinus')->on('mahasiswa_dinus');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('krs_records');
    }
};
