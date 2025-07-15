<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MhsDipaketkan extends Model
{
    protected $table = 'mhs_dipaketkan';

    protected $fillable = [
        'nim_dinus',
        'ta_masuk_mhs',
    ];

    public $timestamps = false;

    // Define the primary key
    protected $primaryKey = 'nim_dinus';

    // Disable auto-incrementing for the primary key
    public $incrementing = false;
}
