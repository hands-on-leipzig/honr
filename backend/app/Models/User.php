<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'email',
        'password',
        'status',
        'nickname',
        'short_bio',
        'contact_link',
        'email_notify_proposals',
        'email_tool_info',
        'email_volunteer_newsletter',
        'is_admin',
        'last_login_at',
        'wizard_completed',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'email_notify_proposals' => 'boolean',
            'email_tool_info' => 'boolean',
            'email_volunteer_newsletter' => 'boolean',
            'is_admin' => 'boolean',
            'last_login_at' => 'datetime',
            'wizard_completed' => 'boolean',
        ];
    }

    public function engagements(): HasMany
    {
        return $this->hasMany(Engagement::class);
    }
}
