<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TahunAjaran extends Model
{
    protected $table = 'tahun_ajaran';
    protected $primaryKey = 'id';
    public $timestamps = false;
    protected $fillable = [
        'kode',
        'tahun_akhir',
        'tahun_awal',
        'jns_smt',
        'set_aktif',
        'biku_tagih_jenis',
        'update_time',
        'update_id',
        'update_host',
        'added_time',
        'added_id',
        'added_host',
        'tgl_masuk',
    ];
}
