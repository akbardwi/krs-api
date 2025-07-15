<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DaftarNilai extends Model
{
    protected $table = 'daftar_nilai';

    protected $fillable = [
        'nim_dinus',
        'kdmk',
        'nl',
        'hide',
    ];

    public $timestamps = false;

    // Define the primary key
    protected $primaryKey = '_id';

    // Disable auto-incrementing for the primary key
    public $incrementing = true; // This is set to true as _id is an auto-incrementing integer
}
