<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class BadgeAwarded extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(
        public User $user,
        public string $roleName,
        public string $roleShortName,
        public int $newLevel,
        public int $engagementCount,
        public ?string $logoPath = null
    ) {
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $levelLabels = [
            1 => 'Basis',
            2 => 'Bronze',
            3 => 'Silber',
            4 => 'Gold',
        ];
        $levelLabel = $levelLabels[$this->newLevel] ?? "Level {$this->newLevel}";

        return new Envelope(
            subject: "Badge erreicht: {$this->roleName} ({$levelLabel}) - HONR",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.badge-awarded',
        );
    }
}
