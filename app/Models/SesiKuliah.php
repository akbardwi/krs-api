<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SesiKuliah extends Model
{
    protected $table = 'sesi_kuliah';

    protected $fillable = [
        'id',
        'id_sesi',
        'id_makul',
        'id_dosen',
        'id_ruang',
        'hari_id',
        'jam_mulai',
        'jam_selesai',
        'ta',
        'aktif',
    ];

    public $timestamps = false;

    protected $primaryKey = 'id';
}
