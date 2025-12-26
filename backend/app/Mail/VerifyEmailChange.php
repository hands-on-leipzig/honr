<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class VerifyEmailChange extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(
        public User $user,
        public string $newEmail,
        public string $token
    ) {
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Neue E-Mail-Adresse bestätigen - HOTR',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        // Link directly to API endpoint (no frontend page needed for email change verification)
        $verificationUrl = url('/api/auth/verify-email-change?token=' . $this->token . '&email=' . urlencode($this->newEmail));

        return new Content(
            view: 'emails.verify-email-change',
            with: [
                'verificationUrl' => $verificationUrl,
            ],
        );
    }
}

