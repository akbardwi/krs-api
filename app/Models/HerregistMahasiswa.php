<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HerregistMahasiswa extends Model
{
    protected $table = 'herregist_mahasiswa';

    protected $fillable = [
        'nim_dinus',
        'ta',
        'date_reg',
    ];

    public $timestamps = false;
}
