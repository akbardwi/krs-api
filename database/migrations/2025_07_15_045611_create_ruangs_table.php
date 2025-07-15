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
        Schema::create('ruang', function (Blueprint $table) {
            $table->id();
            $table->string('nama', 250);
            $table->string('nama2', 250)->default('-');
            $table->integer('id_jenis_makul')->nullable();
            $table->string('id_fakultas', 5)->nullable();
            $table->tinyInteger('kapasitas')->default(0);
            $table->tinyInteger('kap_ujian')->default(0);
            $table->tinyInteger('status')->default(1)->comment('1: buka 0: tutup 2: hapus');
            $table->string('luas', 5)->default('0')->comment('meter persegi');
            $table->string('kondisi', 50)->nullable();
            $table->integer('jumlah')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ruang');
    }
};
