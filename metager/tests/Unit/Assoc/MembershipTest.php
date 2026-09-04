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
            "status" => "okay",
            "start_date" => "2026-01-01",
        ]);

        $reloaded = Membership::findOrFail($membership->id);
        $this->assertTrue($reloaded->reduced);
        $this->assertInstanceOf(\Carbon\Carbon::class, $reloaded->start_date);
        $this->assertSame("2026-01-01", $reloaded->start_date->toDateString());
    }

    /**
     * The status column carries every value CiviCrm.php and
     * MembershipPaymentReminder actually read or write today (Zahlungsstatus:
     * Eingetreten, Okay, Erste/Zweite Zahlungserinnerung, Unterbrochen,
     * Ausgetreten, Verstorben) — pinned here so an import or the reminder
     * cron's future port can't silently drop one.
     */
    public function testEveryKnownZahlungsstatusValueIsAccepted(): void
    {
        $contact = Contact::create(["first_name" => "Ada", "last_name" => "Lovelace", "email" => "ada@example.com"]);

        $statuses = ["eingetreten", "okay", "erste_zahlungserinnerung", "zweite_zahlungserinnerung", "unterbrochen", "ausgetreten", "verstorben"];
        foreach ($statuses as $status) {
            $membership = Membership::create([
                "contact_id" => $contact->id,
                "membership_type" => "person",
                "interval" => "annual",
                "status" => $status,
            ]);
            $this->assertSame($status, Membership::findOrFail($membership->id)->status);
            $membership->delete();
        }
    }

    public function testAnUnknownStatusIsRejectedByTheDatabase(): void
    {
        $contact = Contact::create(["first_name" => "Ada", "last_name" => "Lovelace", "email" => "ada@example.com"]);

        $this->expectException(QueryException::class);
        Membership::create([
            "contact_id" => $contact->id,
            "membership_type" => "person",
            "interval" => "annual",
            "status" => "not_a_real_status",
        ]);
    }
}
