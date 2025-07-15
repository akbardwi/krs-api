<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Hari extends Model
{
    protected $table = 'hari';

    protected $fillable = [
        'id',
        'nama',
        'nama_en',
    ];

    public $timestamps = false;

    protected $primaryKey = 'id';
}
