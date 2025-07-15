<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KrsRecord extends Model
{
    protected $table = 'krs_record';

    protected $fillable = [
        'ta',
        'kdmk',
        'id_jadwal',
        'nim_dinus',
        'sts',
        'sks',
        'modul'
    ];

    public $timestamps = false;
}
