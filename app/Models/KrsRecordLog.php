<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KrsRecordLog extends Model
{
    protected $table = 'krs_record_log';

    protected $fillable = [
        'id_krs',
        'nim_dinus',
        'kdmk',
        'aksi',
        'id_jadwal',
        'ip_addr',
        'lastUpdate',
    ];

    public $timestamps = false;
}
