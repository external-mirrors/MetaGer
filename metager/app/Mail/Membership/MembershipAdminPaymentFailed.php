<?php

namespace App\Mail\Membership;

use App\Models\Membership\MembershipApplication;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Address;


class MembershipAdminPaymentFailed extends Mailable
{
    use Queueable, SerializesModels;

    public string $name;
    public string $message;
    public MembershipApplication $application;
    public string $order;

    /**
     * Create a new message instance.
     */
    public function __construct(MembershipApplication $application, array|null $order)
    {
        if ($application->contact !== null) {
            $this->name = $application->contact->first_name . " " . $application->contact->last_name;
            $this->to("vorstand@suma-ev.de", "SUMA-EV Vorstand");
        } else {
            $this->name = $application->company->company;
            $this->to("vorstand@suma-ev.de", "SUMA-EV Vorstand");
        }
        $this->locale($application->locale);
        $this->application = $application;
        $order_string = json_encode($order, JSON_PRETTY_PRINT);
        if ($order_string !== null) {
            $this->order = $order_string;
        } else {
            $this->order = $order;
        }

    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "[SUMA-EV] PayPal Zahlung fehlgeschlagen",
            from: new Address("verein@metager.de", "SUMA-EV"),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'mail.membership.admin_payment_failed',
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
