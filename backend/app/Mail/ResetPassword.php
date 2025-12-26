<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ResetPassword extends Mailable
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
            subject: 'Passwort zurücksetzen - HONR',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        // Link to frontend password reset page (frontend will handle the form and API call)
        $frontendUrl = env('FRONTEND_URL', env('APP_URL', 'http://localhost:5173'));
        $resetUrl = rtrim($frontendUrl, '/') . '/reset-password?token=' . $this->token . '&email=' . urlencode($this->user->email);

        return new Content(
            view: 'emails.reset-password',
            with: [
                'resetUrl' => $resetUrl,
            ],
        );
    }
}

