<?php

namespace App\Mail\Membership;

use App\Models\Membership\MembershipApplication;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Address;


class MembershipAdminApplicationNotification extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @var \Illuminate\Database\Eloquent\Collection<MembershipApplication>
     */
    public \Illuminate\Database\Eloquent\Collection $finished, $updates, $reductions;

    /**
     * Create a new message instance.
     * 
     * @param \Illuminate\Database\Eloquent\Collection<MembershipApplication> $finished
     * @param \Illuminate\Database\Eloquent\Collection<MembershipApplication> $updates
     * @param \Illuminate\Database\Eloquent\Collection<MembershipApplication> $reductions
     */
    public function __construct(\Illuminate\Database\Eloquent\Collection $finished, \Illuminate\Database\Eloquent\Collection $updates, \Illuminate\Database\Eloquent\Collection $reductions, string $subject = "[SUMA-EV] Offene Änderungen an Mitgliedschaften")
    {
        $this->finished = $finished;
        $this->updates = $updates;
        $this->reductions = $reductions;

        $this->to("dominik@suma-ev.de", "SUMA-EV Vorstand");
        $this->subject($subject);
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address("verein@metager.de", "SUMA-EV"),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'mail.membership.admin_membership_application_notification',
            with: [
                "header_url" => "https://suma-ev.de"
            ]
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
