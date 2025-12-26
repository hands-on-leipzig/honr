<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Event extends Model
{
    protected $fillable = [
        'first_program_id',
        'season_id',
        'level_id',
        'location_id',
        'date',
        'status',
        'rejection_reason',
        'proposed_by_user_id',
    ];

    protected $casts = [
        'date' => 'date',
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

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function proposedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'proposed_by_user_id');
    }

    public function engagements(): HasMany
    {
        return $this->hasMany(Engagement::class);
    }
}
