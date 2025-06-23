<?php

namespace App\Mail\Membership;

use App;
use App\Localization;
use App\Models\Membership\CiviCrm;
use App\Models\Membership\MembershipApplication;
use Arr;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class WelcomeMail extends Mailable
{
    use Queueable, SerializesModels;

    public int $membership_count;
    public MembershipApplication $membership;
    public string $additional_message;
    public array $payments;
    public array $contact;
    public string $plugin_firefox_url;
    public string $plugin_chrome_url;
    public string $plugin_edge_url;
    /**
     * Create a new message instance.
     */
    public function __construct(int $membership_id, string $additional_message = "")
    {
        $this->membership = Arr::get(CiviCrm::FIND_MEMBERSHIPS(membership_id: $membership_id), "0");
        $this->additional_message = $additional_message;
        if ($this->membership === null) {
            throw new Exception("Couldn't find membership with ID $membership_id");
        }
        $this->locale($this->membership->locale);
        $this->locale("en-US");
        $this->contact = CiviCrm::GET_CONTACT($this->membership->crm_contact);
        if ($this->contact === null) {
            throw new Exception("Couldn't find contact with ID {$this->membership->crm_contact}");
        }
        // Get membership count
        $this->membership_count = CiviCrm::GET_MEMBERSHIP_COUNT();
        $this->payments = CiviCrm::MEMBERSHIP_NEXT_PAYMENTS($membership_id, 3);
        $this->plugin_firefox_url = "https://addons.mozilla.org/firefox/addon/metager-suche/";
        $this->plugin_chrome_url = "https://chromewebstore.google.com/detail/metager-suche/gjfllojpkdnjaiaokblkmjlebiagbphd";
        $this->plugin_edge_url = "https://microsoftedge.microsoft.com/addons/detail/metager-suche/fdckbcmhkcoohciclcedgjmchbdeijog";
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $subject = __("membership/mails/welcome_mail.subject");
        if (!App::environment("production"))
            $subject = "[**TEST**]" . $subject;
        return new Envelope(
            subject: $subject,
            from: new Address("verein@metager.de", "SUMA-EV"),
            to: [new Address($this->contact["email_primary.email"], $this->contact["addressee_display"])],
            bcc: [new Address("verein@metager.de", "SUMA-EV")],
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'mail.membership.welcome',
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
