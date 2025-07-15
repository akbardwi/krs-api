<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MahasiswaDinus extends Model
{
    protected $table = 'mahasiswa_dinus';
    protected $primaryKey = 'id';
    public $timestamps = false;
    protected $fillable = [
        'nim_dinus',
        'ta_masuk',
        'prodi',
        'pass_mhs',
        'kelas',
        'akdm_stat',
    ];
}
