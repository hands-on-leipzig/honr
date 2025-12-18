<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Badge extends Model
{
    protected $fillable = [
        'name',
        'type',
        'status',
        'icon_path',
        'first_program_id',
        'season_id',
        'level_id',
        'country_id',
        'role_id',
        'description',
        'sort_order',
    ];

    public function firstProgram(): BelongsTo
    {
        return $this->belongsTo(FirstProgram::class);
    }

    public function season(): BelongsTo
    {
        return $this->belongsTo(Season::class);
    }

    public function level(): BelongsTo
    {
        return $this->belongsTo(Level::class);
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

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
