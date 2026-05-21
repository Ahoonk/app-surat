<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DocumentSeries extends Model
{
    protected $fillable = [
        'company_id',
        'document_type',
        'prefix',
        'year_mode',
        'month_mode',
        'counter',
        'padding',
        'suffix',
    ];

    protected $casts = [
        'year_mode' => 'boolean',
        'month_mode' => 'boolean',
        'counter' => 'integer',
        'padding' => 'integer',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}
