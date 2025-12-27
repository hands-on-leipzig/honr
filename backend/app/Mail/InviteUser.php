<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class InviteUser extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(
        public User $user,
        public string $token,
        public string $password
    ) {
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Einladung zu HONR - Hands-on Recognition',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        // Link to frontend verification page
        $frontendUrl = config('app.frontend_url', env('FRONTEND_URL', env('APP_URL', 'http://localhost:5173')));
        $verificationUrl = rtrim($frontendUrl, '/') . '/verify-email?token=' . $this->token . '&email=' . urlencode($this->user->email);

        return new Content(
            view: 'emails.invite-user',
            with: [
                'verificationUrl' => $verificationUrl,
            ],
        );
    }
}
