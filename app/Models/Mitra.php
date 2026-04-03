<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Mitra extends Model
{
    protected $fillable = [
        'company_id',
        'nama',
        'email',
        'alamat',
        'nomor_penawaran',
        'nomor_invoice',
        'nomor_surat_jalan',
        'nomor_berita_acara',
        'template_penawaran_path',
        'template_invoice_path',
        'template_surat_jalan_path',
        'template_berita_acara_path',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function penawarans()
    {
        return $this->hasMany(Penawaran::class);
    }
}
