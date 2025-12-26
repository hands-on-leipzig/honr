<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ProposalApproved extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(
        public User $user,
        public string $proposalType, // 'level', 'role', 'country', 'location', 'event'
        public string $proposalName,
        public ?string $proposalDescription = null,
        public ?array $additionalContext = null // For roles: first_program, for events: event details, etc.
    ) {
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $typeLabels = [
            'level' => 'Level',
            'role' => 'Rolle',
            'country' => 'Land',
            'location' => 'Standort',
            'event' => 'Veranstaltung',
        ];

        $typeLabel = $typeLabels[$this->proposalType] ?? $this->proposalType;

        return new Envelope(
            subject: "Vorschlag genehmigt: {$typeLabel} \"{$this->proposalName}\" - HONR",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.proposal-approved',
        );
    }
}

