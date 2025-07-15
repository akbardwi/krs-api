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
        Schema::create('tagihan_mhs', function (Blueprint $table) {
            $table->id();
            $table->integer('ta');
            $table->string('nim_dinus', 50);
            $table->string('spp_bank', 11)->nullable();
            $table->boolean('spp_bayar')->default(0);
            $table->dateTime('spp_bayar_date')->nullable();
            $table->integer('spp_dispensasi')->nullable();
            $table->string('spp_host', 25)->nullable();
            $table->boolean('spp_status');
            $table->string('spp_transaksi', 20)->nullable();

            $table->unique(['nim_dinus', 'ta'], 'nim');
            $table->foreign('ta')->references('kode')->on('tahun_ajaran');
            $table->foreign('nim_dinus')->references('nim_dinus')->on('mahasiswa_dinus');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tagihan_mhs');
    }
};
