<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IpSemester extends Model
{
    protected $table = 'ip_semester';
    protected $primaryKey = 'id';
    public $timestamps = false;
    protected $fillable = [
        'ta',
        'nim_dinus',
        'sks',
        'ips',
        'last_update',
    ];

    public function tahunAjaran()
    {
        return $this->belongsTo(TahunAjaran::class, 'ta', 'kode');
    }

    public function mahasiswaDinus()
    {
        return $this->belongsTo(MahasiswaDinus::class, 'nim_dinus', 'nim_dinus');
    }
}
