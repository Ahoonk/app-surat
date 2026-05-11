<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SiteProduct extends Model
{
    protected $fillable = [
        'company_id',
        'name',
        'description',
        'features',
        'image_path',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'features' => 'array',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
}
