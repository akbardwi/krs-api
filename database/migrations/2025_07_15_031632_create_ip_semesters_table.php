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
        Schema::create('ip_semester', function (Blueprint $table) {
            $table->id();
            $table->integer('ta')->default(0);
            $table->string('nim_dinus', 50);
            $table->integer('sks');
            $table->string('ips', 5);
            $table->dateTime('last_update')->nullable();
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
        Schema::dropIfExists('ip_semester');
    }
};
