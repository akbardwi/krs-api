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

    /**
     * Relationship to MahasiswaDinus
     */
    public function mahasiswa()
    {
        return $this->belongsTo(MahasiswaDinus::class, 'nim_dinus', 'nim_dinus');
    }

    /**
     * Relationship to TahunAjaran
     */
    public function tahunAjaran()
    {
        return $this->belongsTo(TahunAjaran::class, 'ta', 'kode');
    }
}
