<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BeritaAcara extends Model
{
    protected $fillable = [
        'company_id',
        'invoice_id',
        'nomor',
        'tanggal',
        'perihal',
        'keterangan_akhir',
        'kota_tanggal_manual',
        'created_by',
        'snapshot_data',
    ];

    protected $casts = [
        'snapshot_data' => 'array',
        'tanggal' => 'date',
        'kota_tanggal_manual' => 'date',
    ];

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}
