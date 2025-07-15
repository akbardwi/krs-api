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
        Schema::create('mhs_ijin_krs', function (Blueprint $table) {
            $table->id();
            $table->integer('ta')->nullable();
            $table->string('nim_dinus', 50)->nullable();
            $table->boolean('ijinkan')->nullable();
            $table->timestamp('time')->useCurrent();
            $table->unique(['ta', 'nim_dinus'], 'nim');

            $table->foreign('ta')->references('kode')->on('tahun_ajaran');
            $table->foreign('nim_dinus')->references('nim_dinus')->on('mahasiswa_dinus');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mhs_ijin_krs');
    }
};
