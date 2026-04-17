<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class NotaToko extends Model
{
    protected $fillable = [
        'company_id',
        'user_id',
        'nomor',
        'tanggal',
        'customer_nama',
        'customer_email',
        'alamat',
        'keterangan',
        'subtotal',
        'tax_percent',
        'tax_amount',
        'total',
        'payment_status',
        'payment_date',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(NotaTokoItem::class);
    }
}
