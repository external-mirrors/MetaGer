<?php

namespace Tests\Feature\Assoc;

use App\Models\Assoc\Contact;
use App\Models\Assoc\LedgerEntry;
use App\Models\Assoc\Membership;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Concerns\UsesInMemorySqlite;
use Tests\TestCase;

/**
 * `/admin/assoc/memberships/{id}/ledger-entries` — the two manual
 * ledger-adjustment actions from the payment-ledger design pass (waiver,
 * refund), see docs/civicrm-replacement.md. Carries no auth middleware under
 * APP_ENV=testing, same reason documented on AssocAdminTest.
 */
class LedgerEntryAdminTest extends TestCase
{
    use DatabaseTransactions;
    use UsesInMemorySqlite;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpInMemorySqlite();
    }

    private function membership(array $overrides = []): Membership
    {
        $contact = Contact::create(["first_name" => "Ada", "last_name" => "Lovelace", "email" => "ada@example.com"]);

        return Membership::create(array_merge([
            "contact_id" => $contact->id,
            "membership_type" => "person",
            "interval" => "monthly",
            "amount" => "10.00",
            "payment_method" => "banktransfer",
            "standing" => "active",
        ], $overrides));
    }

    public function testRecordingAWaiverAddsALedgerEntryAndReducesTheBalance(): void
    {
        $membership = $this->membership();
        LedgerEntry::create(["membership_id" => $membership->id, "kind" => "charge", "amount" => "10.00"]);

        $response = $this->post("/admin/assoc/memberships/{$membership->id}/ledger-entries", [
            "kind" => "waiver",
            "amount" => "10.00",
        ]);

        $response->assertRedirect();
        $entry = LedgerEntry::where("kind", "waiver")->sole();
        $this->assertSame($membership->id, $entry->membership_id);
        $this->assertSame("10.00", $entry->amount);
        $this->assertNull($entry->channel);
        $this->assertSame("0.00", $membership->fresh()->ledgerBalance());
    }

    public function testRecordingARefundRequiresAValidChannel(): void
    {
        $membership = $this->membership();

        $this->post("/admin/assoc/memberships/{$membership->id}/ledger-entries", [
            "kind" => "refund",
            "amount" => "10.00",
        ])->assertStatus(422);

        $this->post("/admin/assoc/memberships/{$membership->id}/ledger-entries", [
            "kind" => "refund",
            "amount" => "10.00",
            "channel" => "directdebit",
        ])->assertStatus(422);

        $this->assertSame(0, LedgerEntry::count());
    }

    public function testRecordingARefundWithASepaCreditTransferChannelSucceeds(): void
    {
        $membership = $this->membership();

        $response = $this->post("/admin/assoc/memberships/{$membership->id}/ledger-entries", [
            "kind" => "refund",
            "amount" => "10.00",
            "channel" => "sepa_credit_transfer",
        ]);

        $response->assertRedirect();
        $entry = LedgerEntry::sole();
        $this->assertSame("refund", $entry->kind);
        $this->assertSame("sepa_credit_transfer", $entry->channel);
    }

    /**
     * A waiver isn't a transfer of money by itself — a channel submitted
     * alongside one (e.g. a form that didn't clear the field) is ignored
     * rather than rejected or stored.
     */
    public function testAChannelSubmittedWithAWaiverIsIgnored(): void
    {
        $membership = $this->membership();

        $this->post("/admin/assoc/memberships/{$membership->id}/ledger-entries", [
            "kind" => "waiver",
            "amount" => "10.00",
            "channel" => "paypal",
        ])->assertRedirect();

        $this->assertNull(LedgerEntry::sole()->channel);
    }

    public function testAnInvalidKindIsRejected(): void
    {
        $membership = $this->membership();

        $this->post("/admin/assoc/memberships/{$membership->id}/ledger-entries", [
            "kind" => "charge",
            "amount" => "10.00",
        ])->assertStatus(422);

        $this->assertSame(0, LedgerEntry::count());
    }

    public function testANonPositiveAmountIsRejected(): void
    {
        $membership = $this->membership();

        $this->post("/admin/assoc/memberships/{$membership->id}/ledger-entries", [
            "kind" => "waiver",
            "amount" => "0",
        ])->assertStatus(422);

        $this->assertSame(0, LedgerEntry::count());
    }

    public function testAMissingMembershipIs404(): void
    {
        $this->post("/admin/assoc/memberships/00000000-0000-4000-8000-000000000000/ledger-entries", [
            "kind" => "waiver",
            "amount" => "10.00",
        ])->assertNotFound();
    }

    public function testTheMemberPageShowsTheLedgerBalanceAndLetsAnAdminBookAnAdjustment(): void
    {
        $membership = $this->membership(["amount" => "12.34"]);
        LedgerEntry::create(["membership_id" => $membership->id, "kind" => "charge", "amount" => "12.34"]);

        $response = $this->get("/admin/assoc/members/contact/{$membership->contact_id}");

        $response->assertOk();
        $response->assertSee("Kontostand");
        $response->assertSee("12,34");
        $response->assertSee(route("assoc_admin_membership_ledger_entry", ["id" => $membership->id]), false);
    }
}
