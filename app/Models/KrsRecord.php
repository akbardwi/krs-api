<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KrsRecord extends Model
{
    protected $table = 'krs_record';

    protected $fillable = [
        'ta',
        'kdmk',
        'id_jadwal',
        'nim_dinus',
        'sts',
        'sks',
        'modul'
    ];

    public $timestamps = false;

    /**
     * Relationship to JadwalTawar
     */
    public function jadwalTawar()
    {
        return $this->belongsTo(JadwalTawar::class, 'id_jadwal', 'id');
    }

    /**
     * Relationship to MatkulKurikulum
     */
    public function matkulKurikulum()
    {
        return $this->belongsTo(MatkulKurikulum::class, 'kdmk', 'kdmk');
    }

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
