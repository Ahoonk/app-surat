<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PenawaranItem extends Model
{
    protected $fillable = [
        'penawaran_id',
        'nama',
        'rincian',
        'qty',
        'satuan',
        'unit_price',
        'amount',
    ];

    public function penawaran()
    {
        return $this->belongsTo(Penawaran::class);
    }
}
