<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Company extends Model
{
    protected $fillable = [
        'name',
        'address',
        'logo',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function penawarans(): HasMany
    {
        return $this->hasMany(Penawaran::class);
    }
}
