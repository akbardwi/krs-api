<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TagihanMhs extends Model
{
    protected $table = 'tagihan_mhs';
    protected $primaryKey = 'id';
    public $timestamps = false;
    protected $fillable = [
        'ta',
        'nim_dinus',
        'spp_bank',
        'spp_bayar',
        'spp_bayar_date',
        'spp_dispensasi',
        'spp_host',
        'spp_status',
        'spp_transaksi',
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
