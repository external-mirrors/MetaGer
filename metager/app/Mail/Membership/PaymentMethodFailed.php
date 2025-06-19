<?php

namespace App\Mail\Membership;

use App;
use App\Models\Membership\CiviCrm;
use App\Models\Membership\MembershipApplication;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Address;


class PaymentMethodFailed extends Mailable
{
    use Queueable, SerializesModels;

    public MembershipApplication $application;
    public string $name;
    public array $payments;

    /**
     * Create a new message instance.
     */
    public function __construct(MembershipApplication $application)
    {
        if ($application->contact !== null) {
            $this->name = $application->contact->first_name . " " . $application->contact->last_name;
            $this->to(new Address($application->contact->email, $this->name));
        } else {
            $this->name = $application->company->company;
            $this->to(new Address($application->company->email, $this->name));
        }
        $this->application = $application;
        $this->payments = CiviCrm::MEMBERSHIP_NEXT_PAYMENTS($application->crm_membership, 3);
        $this->locale($application->locale);
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $subject = __("membership/mails/payment_method_failed.subject");
        if (!App::environment("production"))
            $subject = "[**TEST**]" . $subject;
        return new Envelope(
            subject: $subject,
            from: new Address("verein@metager.de", "SUMA-EV"),
            bcc: [new Address(config("metager.metager.membership.notification_address"), "SUMA-EV")],
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'mail.membership.payment_method_failed',
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
