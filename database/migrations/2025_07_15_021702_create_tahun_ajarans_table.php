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
        Schema::create('tahun_ajaran', function (Blueprint $table) {
            $table->id();
            $table->integer('kode')->unique('kode');
            $table->string('tahun_akhir');
            $table->string('tahun_awal');
            $table->tinyInteger('jns_smt')->comment('1 = reg ganjil, 2 = reg genap, 3 = sp ganjil, 4 = sp genap');
            $table->boolean('set_aktif');
            $table->tinyInteger('biku_tagih_jenis')->default(0)->comment('1 = spp; 2 = sks; 3 = kekurangan');
            $table->dateTime('update_time')->nullable();
            $table->string('update_id', 18)->nullable();
            $table->string('update_host', 18)->nullable();
            $table->dateTime('added_time')->nullable();
            $table->string('added_id', 18)->nullable();
            $table->string('added_host', 18)->nullable();
            $table->date('tgl_masuk')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tahun_ajaran');
    }
};
