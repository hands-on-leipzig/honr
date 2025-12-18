<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Engagement extends Model
{
    protected $fillable = [
        'user_id',
        'role_id',
        'event_id',
        'is_recognized',
        'recognized_at',
    ];

    protected $casts = [
        'is_recognized' => 'boolean',
        'recognized_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }
}
