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


class ReductionDeny extends Mailable
{
    use Queueable, SerializesModels;

    public string $application_id;
    public string $name;
    public string $message;
    public string $update_link;

    /**
     * Create a new message instance.
     */
    public function __construct(MembershipApplication $application, string $message)
    {
        if ($application->contact !== null) {
            $this->name = $application->contact->first_name . " " . $application->contact->last_name;
            $this->to(new Address($application->contact->email, $this->name));
        } else {
            $this->name = $application->company->company;
            $this->to(new Address($application->company->email, $this->name));
        }
        $this->application_id = $application->id;
        $this->locale($application->locale);
        $this->message = $message;
        $this->update_link = $application->is_update ? route("membership_form", ["application_id" => CiviCrm::GET_EDIT_ID($application->crm_membership), "edit" => "membership-fee"]) : route("membership_form", ["application_id" => $application->id]);
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $subject = __("membership/mails/reduction_deny.subject");
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
            markdown: 'mail.membership.reduction_deny',
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
