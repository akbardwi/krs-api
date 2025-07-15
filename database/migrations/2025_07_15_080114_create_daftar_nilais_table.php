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
        Schema::create('daftar_nilai', function (Blueprint $table) {
            $table->id('_id');
            $table->string('nim_dinus', 50)->nullable();
            $table->string('kdmk', 20)->nullable();
            $table->char('nl', 2)->nullable();
            $table->tinyInteger('hide')->default(0)->comment('0 = nilai muncul; 1 = disembunyikan');

            $table->index(['nim_dinus', 'kdmk'], 'nim');
            $table->foreign('nim_dinus')->references('nim_dinus')->on('mahasiswa_dinus');
            $table->foreign('kdmk')->references('kdmk')->on('matkul_kurikulum');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('daftar_nilai');
    }
};
