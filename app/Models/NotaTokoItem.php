<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotaTokoItem extends Model
{
    protected $fillable = [
        'nota_toko_id',
        'nama',
        'qty',
        'satuan',
        'unit_price',
        'amount',
    ];

    public function notaToko()
    {
        return $this->belongsTo(NotaToko::class);
    }
}
