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
        Schema::create('sesi_kuliah', function (Blueprint $table) {
            $table->smallIncrements('id');
            $table->string('jam', 11)->default('');
            $table->tinyInteger('sks')->default(0);
            $table->time('jam_mulai')->nullable();
            $table->time('jam_selesai')->nullable();
            $table->integer('status')->default(1)->comment('0=tidak valid, 1= jam valid(kelipatan 50menit), 2 = jam yang harusnya tidak di pakai(jam istirahat)');
            $table->unique(['jam_mulai', 'jam_selesai'], 'jam_unik');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sesi_kuliah');
    }
};
