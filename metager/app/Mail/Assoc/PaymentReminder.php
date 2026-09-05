<?php

namespace App\Mail\Assoc;

use App;
use App\Models\Assoc\Membership;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;

/**
 * The assoc_* schema's counterpart to App\Mail\Membership\PaymentReminder
 * (see App\Assoc\PaymentReminderProcessor for what drives it) — reuses that
 * class's already-translated "membership/mails/payment_reminder" strings
 * (the same concept, already correct in all 12 shipped locales, no reason
 * to duplicate the copy) but against the new Membership/ledger model
 * instead of MembershipApplication/CiviCrm, with its own view: the legacy
 * view is wired to CiviCrm::GET_EDIT_ID() and MembershipApplication-only
 * fields, so forcing it onto this schema would mean faking a
 * MembershipApplication rather than a real integration.
 *
 * Deliberately omits the legacy view's "mastodon"/"key_charge" panels:
 * both claim an action ("charging paused", "account frozen") that nothing
 * in this schema performs yet — phase 6d (assoc:charge-keys) and any
 * Mastodon integration don't exist here. Claiming either would be false;
 * revisit once they do.
 */
class PaymentReminder extends Mailable
{
    use Queueable;
    use SerializesModels;

    public const STAGE_FIRST = "first";

    public const STAGE_SECOND = "second";

    public const STAGE_TERMINATED = "terminated";

    /** How much further notice the first/second stage's copy promises before the next stage. */
    private const DUE_WEEKS = 2;

    public string $name;

    public string $stage;

    public Carbon $dueDate;

    public string $amount;

    public ?string $paymentReference;

    /** Renamed from "locale" — Mailable already declares that property for ->locale(). */
    public string $recipientLocale;

    public function __construct(
        public Membership $membership,
        string $stage,
        Carbon $dueDate,
        /** The date PaymentReminderProcessor will actually terminate the membership by, if still unpaid — single source of truth lives there, not duplicated here. */
        public Carbon $terminationDate,
        string $recipientEmail,
        string $recipientName,
    ) {
        $this->name = $recipientName;
        $this->to(new Address($recipientEmail, $recipientName));
        $this->recipientLocale = $membership->locale ?? config("app.locale");
        $this->locale($this->recipientLocale);

        $this->stage = $stage;
        $this->dueDate = $dueDate;
        $this->amount = $membership->ledgerBalance();
        $this->paymentReference = $membership->payment_reference;
    }

    public function envelope(): Envelope
    {
        $subject = match ($this->stage) {
            self::STAGE_FIRST => __("membership/mails/payment_reminder.subject.first"),
            self::STAGE_SECOND => __("membership/mails/payment_reminder.subject.second", ["date" => $this->terminationDate->isoFormat("L")]),
            self::STAGE_TERMINATED => __("membership/mails/payment_reminder.subject.expired"),
        };

        if (!App::environment("production")) {
            $subject = "[**TEST**]" . $subject;
        }

        return new Envelope(
            subject: $subject,
            from: new Address("verein@metager.de", "SUMA-EV"),
            bcc: [new Address(config("metager.metager.membership.notification_address"), "SUMA-EV")],
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: "mail.assoc.payment_reminder",
            with: [
                "dueWeeks" => self::DUE_WEEKS,
                "terminationDate" => $this->terminationDate,
            ],
        );
    }

    /**
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
