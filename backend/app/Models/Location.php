<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Location extends Model
{
    protected $fillable = [
        'name',
        'country_id',
        'street_address',
        'city',
        'postal_code',
        'latitude',
        'longitude',
        'status',
        'rejection_reason',
        'proposed_by_user_id',
    ];

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    public function proposedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'proposed_by_user_id');
    }

    public function events(): HasMany
    {
        return $this->hasMany(Event::class);
    }
}
