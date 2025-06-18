<?php

namespace App\Mail\Membership;

use App;
use App\Models\Membership\CiviCrm;
use App\Models\Membership\MembershipApplication;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Address;


class PaymentReminder extends Mailable
{
    use Queueable, SerializesModels;

    const REMINDER_STAGE_FIRST = "First Reminder";
    const REMINDER_STAGE_SECOND = "Second Reminder";
    const REMINDER_STAGE_ABORTED = "Aborted";

    public string $name;
    public MembershipApplication $application;
    public array $payments;
    public string $reminder_stage;
    public int $due_weeks = 0;

    /**
     * Create a new message instance.
     */
    public function __construct(MembershipApplication $application, string $reminder_stage)
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
        $this->reminder_stage = $reminder_stage;
        if ($reminder_stage === self::REMINDER_STAGE_FIRST) {
            $this->due_weeks = 2;
        } elseif ($reminder_stage === self::REMINDER_STAGE_SECOND) {
            $this->due_weeks = 4;
        }
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: match ($this->reminder_stage) {
                self::REMINDER_STAGE_FIRST => __("membership/mails/payment_reminder.subject.first"),
                self::REMINDER_STAGE_SECOND => __("membership/mails/payment_reminder.subject.second", ['date' => (clone $this->application->end_date)->addMonath(1)->isoFormat("L")]),
                self::REMINDER_STAGE_ABORTED => __("membership/mails/payment_reminder.subject.expired")
            },
            from: new Address("verein@metager.de", "SUMA-EV"),
            // bcc: [new Address("verein@metager.de", "SUMA-EV")],
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'mail.membership.payment_reminder_first',
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
