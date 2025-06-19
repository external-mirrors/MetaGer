<?php

namespace App\Mail;

use App\Localization;
use App\Models\Membership\CiviCrm;
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
    public array $membership;
    public array $payments;
    public array $contact;
    public string $plugin_firefox_url;
    public string $plugin_chrome_url;
    public string $plugin_edge_url;
    /**
     * Create a new message instance.
     */
    public function __construct(int $membership_id)
    {
        $membership = CiviCrm::FIND_MEMBERSHIPS(null, $membership_id);
        if ($membership !== null && !empty($membership)) {
            $this->membership = $membership[0];
        } else {
            throw new Exception("Couldn't find membership with ID $membership_id");
        }
        $this->contact = CiviCrm::GET_CONTACT($this->membership["contact_id"]);
        if ($this->contact === null) {
            throw new Exception("Couldn't find contact with ID {$membership['contact_id']}");
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
        return new Envelope(
            subject: __("membership/welcome_mail.subject"),
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
