<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ruang extends Model
{
    protected $table = 'ruang';

    protected $fillable = [
        'nama',
        'nama2',
        'id_jenis_makul',
        'id_fakultas',
        'kapasitas',
        'kap_ujian',
        'status',
        'luas',
        'kondisi',
        'jumlah'
    ];

    public $timestamps = false;

    // Define any relationships or additional methods here if needed
}
