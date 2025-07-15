<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MhsIjinKrs extends Model
{
    protected $table = 'mhs_ijin_krs';

    protected $fillable = [
        'ta',
        'nim_dinus',
        'ijinkan',
        'time',
    ];

    public $timestamps = false;
}
