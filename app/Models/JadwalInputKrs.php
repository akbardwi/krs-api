<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JadwalInputKrs extends Model
{
    protected $table = 'jadwal_input_krs';

    protected $fillable = [
        'ta',
        'prodi',
        'tgl_mulai',
        'tgl_selesai',
    ];

    public $timestamps = false;

    // Define the primary key
    protected $primaryKey = 'id';

    // Disable auto-incrementing for the primary key
    public $incrementing = true; // This is set to true as id is an auto-incrementing integer
}
