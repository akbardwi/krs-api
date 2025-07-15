<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SesiKuliahBentrok extends Model
{
    protected $table = 'sesi_kuliah_bentrok';

    protected $primaryKey = ['id', 'id_bentrok'];

    public $incrementing = false;

    protected $fillable = [
        'id',
        'id_bentrok',
    ];

    public $timestamps = false;
}
