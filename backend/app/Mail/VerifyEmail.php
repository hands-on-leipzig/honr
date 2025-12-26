<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class VerifyEmail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(
        public User $user,
        public string $token
    ) {
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'E-Mail-Adresse bestätigen - HONR',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        // Link directly to API endpoint (no frontend page needed for email verification)
        $verificationUrl = url('/api/auth/verify-email?token=' . $this->token . '&email=' . urlencode($this->user->email));

        return new Content(
            view: 'emails.verify-email',
            with: [
                'verificationUrl' => $verificationUrl,
            ],
        );
    }
}

