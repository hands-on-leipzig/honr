<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class EmailVerificationToken extends Model
{
    public $timestamps = false; // Tokens are immutable after creation (no updated_at)

    protected $fillable = [
        'user_id',
        'email',
        'token',
        'type',
        'expires_at',
        'created_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
    ];

    /**
     * Generate and store a verification token
     */
    public static function createToken(int $userId, string $email, string $type, int $hours = 24): string
    {
        // Delete any existing tokens for this user/email/type
        self::where('user_id', $userId)
            ->where('email', $email)
            ->where('type', $type)
            ->delete();

        // Generate token
        $token = Str::random(64);
        $hashedToken = Hash::make($token);

        // Store hashed token
        $now = Carbon::now();
        self::create([
            'user_id' => $userId,
            'email' => $email,
            'token' => $hashedToken,
            'type' => $type,
            'expires_at' => $now->copy()->addHours($hours),
            'created_at' => $now,
        ]);

        // Return plain token for email
        return $token;
    }

    /**
     * Verify a token
     */
    public static function verifyToken(string $token, string $email, string $type): ?self
    {
        $verificationToken = self::where('email', $email)
            ->where('type', $type)
            ->where('expires_at', '>', Carbon::now())
            ->first();

        if (!$verificationToken) {
            return null;
        }

        if (!Hash::check($token, $verificationToken->token)) {
            return null;
        }

        return $verificationToken;
    }

    /**
     * Delete token after successful verification
     */
    public function delete(): bool
    {
        return parent::delete();
    }
}
