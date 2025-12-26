<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Role extends Model
{
    protected $fillable = [
        'name',
        'short_name',
        'description',
        'sort_order',
        'first_program_id',
        'role_category',
        'status',
        'rejection_reason',
        'proposed_by_user_id',
        'logo_path',
    ];

    public function firstProgram(): BelongsTo
    {
        return $this->belongsTo(FirstProgram::class);
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
