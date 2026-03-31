<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BeritaAcara extends Model
{
    protected $fillable = [
        'invoice_id',
        'nomor',
        'tanggal',
        'perihal',
        'keterangan_akhir',
        'kota_tanggal_manual',
        'created_by',
    ];

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }
}
