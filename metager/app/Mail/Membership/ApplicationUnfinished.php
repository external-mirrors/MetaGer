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


class ApplicationUnfinished extends Mailable
{
    use Queueable, SerializesModels;

    public string $name;
    public string $application_link;

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
        $locale = App::getLocale();
        App::setLocale($application->locale);
        $parameters = [
            "type" => "person",
            "amount" => $application->amount,
            "interval" => $application->interval,
            "payment_method" => $application->payment_method
        ];
        if ($application->contact !== null) {
            $parameters["title"] = $application->contact->title;
            $parameters["firstname"] = $application->contact->first_name;
            $parameters["lastname"] = $application->contact->last_name;
            $parameters["email"] = $application->contact->email;
        } elseif ($application->company !== null) {
            $parameters["type"] = "company";
            $parameters["company"] = $application->company->company;
            $parameters["employees"] = $application->company->employees;
            $parameters["email"] = $application->company->email;
        }
        $this->application_link = route("membership_form", $parameters);
        $this->locale($application->locale);
        App::setLocale($locale);
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $subject = __("membership/mails/application_unfinished.subject");
        if (!App::is("production"))
            $subject = "[**TEST**]" . $subject;
        return new Envelope(
            subject: $subject,
            from: new Address("verein@metager.de", "SUMA-EV"),
            bcc: [new Address("verein@metager.de", "SUMA-EV")],
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'mail.membership.application_unfinished',
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
