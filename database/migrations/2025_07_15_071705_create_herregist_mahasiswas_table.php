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
        Schema::create('herregist_mahasiswa', function (Blueprint $table) {
            $table->id();
            $table->string('nim_dinus', 50)->nullable();
            $table->integer('ta')->nullable();
            $table->dateTime('date_reg')->nullable();

            $table->foreign('nim_dinus')->references('nim_dinus')->on('mahasiswa_dinus');
            $table->foreign('ta')->references('kode')->on('tahun_ajaran');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('herregist_mahasiswa');
    }
};
