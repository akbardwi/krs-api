<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ValidasiKrsMhs extends Model
{
    protected $table = 'validasi_krs_mhs';

    protected $fillable = [
        'nim_dinus',
        'job_date',
        'job_host',
        'job_agent',
        'ta',
    ];

    public $timestamps = false;

    // Define the primary key
    protected $primaryKey = 'id';

    // Disable auto-incrementing for the primary key
    public $incrementing = true; // This is set to true as id is an auto-incrementing integer

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
