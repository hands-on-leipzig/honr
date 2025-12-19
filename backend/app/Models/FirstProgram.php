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
        'logo_path',
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

    public function events(): HasMany
    {
        return $this->hasMany(Event::class);
    }

    public function badges(): HasMany
    {
        return $this->hasMany(Badge::class);
    }
}
