<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Penawaran extends Model
{
    protected $fillable = [
        'company_id',
        'user_id',
        'nomor',
        'tanggal',
        'customer_nama',
        'to_company',
        'to_address',
        'jenis_kontrak',
        'signature_role',
        'keterangan',
        'subtotal',
        'tax_percent',
        'tax_amount',
        'total',
        'status',
        'invoice_date',
        'invoice_number',
        'invoice_sequence',
        'approved_by',
        'approved_at'
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(PenawaranItem::class);
    }

    public function purchasingOrder()
    {
        return $this->hasOne(PurchasingOrder::class);
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }
}
