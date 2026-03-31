<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FakturPajak extends Model
{
    protected $fillable = [
        'invoice_id',
        'dokumen_path',
        'dokumen_name',
        'uploaded_by',
        'uploaded_at',
        'payment_status',
        'payment_date',
    ];

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }
}
