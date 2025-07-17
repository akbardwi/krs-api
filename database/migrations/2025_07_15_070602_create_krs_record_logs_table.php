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
        Schema::create('krs_record_log', function (Blueprint $table) {
            $table->unsignedBigInteger('id_krs')->nullable();
            $table->string('nim_dinus', 50)->nullable();
            $table->string('kdmk')->nullable();
            $table->tinyInteger('aksi')->nullable()->comment('1=insert, 2=delete');
            $table->integer('id_jadwal')->nullable();
            $table->string('ip_addr', 50)->nullable();
            $table->timestamp('lastUpdate')->useCurrent();

            $table->foreign('id_krs')->references('id')->on('krs_record')->onDelete('no action');
            $table->foreign('nim_dinus')->references('nim_dinus')->on('mahasiswa_dinus');
            $table->foreign('kdmk')->references('kdmk')->on('matkul_kurikulum');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('krs_record_log');
    }
};
