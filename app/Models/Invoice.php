<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    protected $fillable = [
        'penawaran_id',
        'purchasing_order_id',
        'nomor',
        'tanggal',
        'sequence',
        'total',
        'payment_status',
        'payment_date',
        'created_by',
    ];

    public function penawaran()
    {
        return $this->belongsTo(Penawaran::class);
    }

    public function purchasingOrder()
    {
        return $this->belongsTo(PurchasingOrder::class);
    }

    public function fakturPajak()
    {
        return $this->hasOne(FakturPajak::class);
    }

    public function suratJalan()
    {
        return $this->hasOne(SuratJalan::class);
    }

    public function beritaAcara()
    {
        return $this->hasOne(BeritaAcara::class);
    }
}
