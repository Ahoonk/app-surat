<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchasingOrder extends Model
{
    protected $fillable = [
        'penawaran_id',
        'dokumen_path',
        'dokumen_name',
        'nomor_po',
        'tanggal_po',
        'uploaded_by',
        'uploaded_at',
    ];

    public function penawaran()
    {
        return $this->belongsTo(Penawaran::class);
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }
}
