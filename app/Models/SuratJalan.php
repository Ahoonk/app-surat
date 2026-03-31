<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SuratJalan extends Model
{
    protected $fillable = [
        'invoice_id',
        'nomor',
        'tanggal',
        'pemberi_nama',
        'pemberi_jabatan',
        'pemberi_alamat',
        'penerima_nama',
        'penerima_hp',
        'kota_tanggal_manual',
        'created_by',
    ];

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }
}
