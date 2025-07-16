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
        Schema::create('jadwal_tawar', function (Blueprint $table) {
            $table->id();
            $table->integer('ta')->default(0);
            $table->string('kdmk');
            $table->string('klpk', 15);
            $table->string('klpk_2', 15)->nullable();
            $table->integer('kdds');
            $table->integer('kdds2')->nullable();
            $table->integer('jmax')->default(0);
            $table->integer('jsisa')->default(0);
            $table->tinyInteger('id_hari1');
            $table->tinyInteger('id_hari2');
            $table->tinyInteger('id_hari3');
            $table->smallInteger('id_sesi1')->unsigned();
            $table->smallInteger('id_sesi2')->unsigned();
            $table->smallInteger('id_sesi3')->unsigned();
            $table->unsignedBigInteger('id_ruang1');
            $table->unsignedBigInteger('id_ruang2');
            $table->unsignedBigInteger('id_ruang3');
            $table->tinyInteger('jns_jam')->comment('1=pagi, 2=malam, 3=pagi-malam');
            $table->boolean('open_class')->default(true)->comment('kelas dibuka utk KRS : 1 = open; 0 = close');

            $table->foreign('kdmk')->references('kdmk')->on('matkul_kurikulum');
            $table->foreign('id_hari1')->references('id')->on('hari');
            $table->foreign('id_hari2')->references('id')->on('hari');
            $table->foreign('id_hari3')->references('id')->on('hari');
            $table->foreign('id_sesi1')->references('id')->on('sesi_kuliah');
            $table->foreign('id_sesi2')->references('id')->on('sesi_kuliah');
            $table->foreign('id_sesi3')->references('id')->on('sesi_kuliah');
            $table->foreign('id_ruang1')->references('id')->on('ruang');
            $table->foreign('id_ruang2')->references('id')->on('ruang');
            $table->foreign('id_ruang3')->references('id')->on('ruang');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jadwal_tawar');
    }
};
