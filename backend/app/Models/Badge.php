<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Badge extends Model
{
    protected $fillable = [
        'name',
        'status',
        'role_id',
        'description',
        'sort_order',
    ];

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function thresholds(): HasMany
    {
        return $this->hasMany(BadgeThreshold::class)->orderBy('sort_order');
    }

    public function earnedBadges(): HasMany
    {
        return $this->hasMany(EarnedBadge::class);
    }
}
