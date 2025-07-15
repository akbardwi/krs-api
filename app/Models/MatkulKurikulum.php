<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MatkulKurikulum extends Model
{
    protected $table = 'matkul_kurikulum';
    protected $primaryKey = 'kur_id';

    protected $fillable = [
        'kur_id',
        'kdmk',
        'nmmk',
        'nmen',
        'tp',
        'sks',
        'sks_t',
        'sks_p',
        'smt',
        'jns_smt',
        'aktif',
        'kur_nama',
        'kelompok_makul',
        'kur_aktif',
        'jenis_matkul'
    ];

    public $timestamps = false;
}
