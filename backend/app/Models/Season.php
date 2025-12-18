<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Season extends Model
{
    protected $fillable = [
        'name',
        'start_year',
        'first_program_id',
    ];

    protected $casts = [
        'start_year' => 'integer',
    ];

    public function firstProgram(): BelongsTo
    {
        return $this->belongsTo(FirstProgram::class);
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
