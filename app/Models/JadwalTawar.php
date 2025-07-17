<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JadwalTawar extends Model
{
    protected $table = 'jadwal_tawar';

    protected $fillable = [
        'ta',
        'kdmk',
        'klpk',
        'klpk_2',
        'kdds',
        'kdds2',
        'jmax',
        'jsisa',
        'id_hari1',
        'id_hari2',
        'id_hari3',
        'id_sesi1',
        'id_sesi2',
        'id_sesi3',
        'id_ruang1',
        'id_ruang2',
        'id_ruang3',
        'jns_jam',
        'open_class'
    ];

    public $timestamps = false;

    /**
     * Relationship to KrsRecord
     */
    public function krsRecords()
    {
        return $this->hasMany(KrsRecord::class, 'id_jadwal', 'id');
    }

    /**
     * Relationship to MatkulKurikulum
     */
    public function matkulKurikulum()
    {
        return $this->belongsTo(MatkulKurikulum::class, 'kdmk', 'kdmk');
    }

    /**
     * Relationship to TahunAjaran
     */
    public function tahunAjaran()
    {
        return $this->belongsTo(TahunAjaran::class, 'ta', 'kode');
    }
}
