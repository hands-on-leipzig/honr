<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EarnedBadge extends Model
{
    protected $fillable = [
        'user_id',
        'badge_id',
        'earned_at',
        'current_threshold_id',
    ];

    protected $casts = [
        'earned_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function badge(): BelongsTo
    {
        return $this->belongsTo(Badge::class);
    }

    public function currentThreshold(): BelongsTo
    {
        return $this->belongsTo(BadgeThreshold::class, 'current_threshold_id');
    }
}
