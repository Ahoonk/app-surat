<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FakturPajak extends Model
{
    protected $fillable = [
        'company_id',
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

    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}
