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
        Schema::create('mhs_dipaketkan', function (Blueprint $table) {
            $table->string('nim_dinus', 50)->primary();
            $table->integer('ta_masuk_mhs');

            $table->foreign('nim_dinus')->references('nim_dinus')->on('mahasiswa_dinus');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mhs_dipaketkan');
    }
};
