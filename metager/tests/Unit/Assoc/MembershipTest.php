<?php

namespace Tests\Unit\Assoc;

use App\Models\Assoc\Contact;
use App\Models\Assoc\Membership;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Concerns\UsesInMemorySqlite;
use Tests\TestCase;

class MembershipTest extends TestCase
{
    use DatabaseTransactions;
    use UsesInMemorySqlite;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpInMemorySqlite();
    }

    public function testAMembershipCastsItsDatesAndBooleanReduced(): void
    {
        $contact = Contact::create(["first_name" => "Ada", "last_name" => "Lovelace", "email" => "ada@example.com"]);
        $membership = Membership::create([
            "contact_id" => $contact->id,
            "membership_type" => "person",
            "reduced" => true,
            "interval" => "monthly",
            "amount" => "4.00",
            "payment_method" => "banktransfer",
            "start_date" => "2026-01-01",
        ]);

        $reloaded = Membership::findOrFail($membership->id);
        $this->assertTrue($reloaded->reduced);
        $this->assertInstanceOf(\Carbon\Carbon::class, $reloaded->start_date);
        $this->assertSame("2026-01-01", $reloaded->start_date->toDateString());
    }

    public function testStandingDefaultsToActive(): void
    {
        $contact = Contact::create(["first_name" => "Ada", "last_name" => "Lovelace", "email" => "ada@example.com"]);
        $membership = Membership::create([
            "contact_id" => $contact->id,
            "membership_type" => "person",
            "interval" => "annual",
            "amount" => "17.00",
            "payment_method" => "banktransfer",
        ]);

        $this->assertSame("active", $membership->fresh()->standing);
    }

    /**
     * "terminated"/"deceased" are a deliberate admin action, not a payment
     * state — the CiviCRM Zahlungsstatus values this replaces conflated the
     * two (Ausgetreten/Verstorben alongside Okay/Erste Zahlungserinnerung
     * etc.). Collection progress for banktransfer/directdebit members is
     * derived from end_date and assoc_debits, not stored here — see the
     * migration's own comment.
     */
    public function testEveryKnownStandingValueIsAccepted(): void
    {
        $contact = Contact::create(["first_name" => "Ada", "last_name" => "Lovelace", "email" => "ada@example.com"]);

        foreach (["active", "terminated", "deceased"] as $standing) {
            $membership = Membership::create([
                "contact_id" => $contact->id,
                "membership_type" => "person",
                "interval" => "annual",
                "amount" => "17.00",
                "payment_method" => "banktransfer",
                "standing" => $standing,
            ]);
            $this->assertSame($standing, Membership::findOrFail($membership->id)->standing);
            $membership->delete();
        }
    }

    public function testPaymentMethodAndAmountRoundTrip(): void
    {
        $contact = Contact::create(["first_name" => "Ada", "last_name" => "Lovelace", "email" => "ada@example.com"]);
        $membership = Membership::create([
            "contact_id" => $contact->id,
            "membership_type" => "person",
            "interval" => "monthly",
            "amount" => "19.99",
            "payment_method" => "directdebit",
            "payment_reference" => "M20260101120000",
            "join_date" => "2026-01-01",
        ]);

        $reloaded = Membership::findOrFail($membership->id);
        $this->assertSame("19.99", $reloaded->amount);
        $this->assertSame("directdebit", $reloaded->payment_method);
        $this->assertSame("M20260101120000", $reloaded->payment_reference);
        $this->assertNull($reloaded->paypal_vault_id);
        $this->assertSame("2026-01-01", $reloaded->join_date->toDateString());
    }

    /**
     * "exempt" replaces CiviCRM's two separate membership types for
     * honorary/reciprocity members (Ehrenmitglied/Gegenseitigkeit) — both mean
     * "no dues are ever collected," which is a billing fact, not a membership
     * type. amount stays 0.00 here; ChargeKeys' own 5€/month default for a
     * computed price of 0 is a key-charging concern, not something this
     * column needs to encode.
     */
    public function testExemptIsAValidPaymentMethodWithNoDuesCollected(): void
    {
        $contact = Contact::create(["first_name" => "Ada", "last_name" => "Lovelace", "email" => "ada@example.com"]);
        $membership = Membership::create([
            "contact_id" => $contact->id,
            "membership_type" => "person",
            "interval" => "annual",
            "amount" => "0.00",
            "payment_method" => "exempt",
        ]);

        $reloaded = Membership::findOrFail($membership->id);
        $this->assertSame("exempt", $reloaded->payment_method);
        $this->assertSame("0.00", $reloaded->amount);
        $this->assertNull($reloaded->payment_reference);
    }

    public function testReducedUntilLocaleAndMastodonIdRoundTrip(): void
    {
        $contact = Contact::create(["first_name" => "Ada", "last_name" => "Lovelace", "email" => "ada@example.com"]);
        $membership = Membership::create([
            "contact_id" => $contact->id,
            "membership_type" => "person",
            "reduced" => true,
            "interval" => "monthly",
            "amount" => "2.50",
            "payment_method" => "directdebit",
            "reduced_until" => "2027-01-01",
            "locale" => "de-DE",
            "mastodon_id" => "12345",
        ]);

        $reloaded = Membership::findOrFail($membership->id);
        $this->assertSame("2027-01-01", $reloaded->reduced_until->toDateString());
        $this->assertSame("de-DE", $reloaded->locale);
        $this->assertSame("12345", $reloaded->mastodon_id);
    }

    public function testCivicrmIdMustBeUniqueWhenPresent(): void
    {
        $contact = Contact::create(["first_name" => "Ada", "last_name" => "Lovelace", "email" => "ada@example.com"]);
        $otherContact = Contact::create(["first_name" => "Grace", "last_name" => "Hopper", "email" => "grace@example.com"]);
        Membership::create([
            "civicrm_id" => 42,
            "contact_id" => $contact->id,
            "membership_type" => "person",
            "interval" => "annual",
            "amount" => "17.00",
            "payment_method" => "banktransfer",
        ]);

        $this->expectException(QueryException::class);
        Membership::create([
            "civicrm_id" => 42,
            "contact_id" => $otherContact->id,
            "membership_type" => "person",
            "interval" => "annual",
            "amount" => "17.00",
            "payment_method" => "banktransfer",
        ]);
    }

    public function testAnUnknownStandingIsRejectedByTheDatabase(): void
    {
        $contact = Contact::create(["first_name" => "Ada", "last_name" => "Lovelace", "email" => "ada@example.com"]);

        $this->expectException(QueryException::class);
        Membership::create([
            "contact_id" => $contact->id,
            "membership_type" => "person",
            "interval" => "annual",
            "amount" => "17.00",
            "payment_method" => "banktransfer",
            "standing" => "not_a_real_standing",
        ]);
    }
}
