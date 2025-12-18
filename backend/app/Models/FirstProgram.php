<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FirstProgram extends Model
{
    protected $fillable = [
        'name',
        'sort_order',
        'valid_from',
        'valid_to',
    ];

    protected $casts = [
        'valid_from' => 'date',
        'valid_to' => 'date',
    ];

    public function seasons(): HasMany
    {
        return $this->hasMany(Season::class);
    }

    public function roles(): HasMany
    {
        return $this->hasMany(Role::class);
    }
}
