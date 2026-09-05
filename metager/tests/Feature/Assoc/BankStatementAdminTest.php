<?php

namespace Tests\Feature\Assoc;

use App\Models\Assoc\BankStatementLine;
use App\Models\Assoc\Contact;
use App\Models\Assoc\Debit;
use App\Models\Assoc\LedgerEntry;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Concerns\UsesInMemorySqlite;
use Tests\TestCase;

/**
 * `/admin/assoc/bank-statements/*` — the shadow-mode matching triage UI, see
 * docs/civicrm-replacement.md phase 4. Unlike AssocAdminTest's routes this
 * one writes (a manual match, a rematch trigger); it carries no auth
 * middleware under APP_ENV=testing for the same reason documented on
 * AssocAdminTest.
 */
class BankStatementAdminTest extends TestCase
{
    use DatabaseTransactions;
    use UsesInMemorySqlite;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpInMemorySqlite();
    }

    private function contact(): Contact
    {
        return Contact::create(["first_name" => "Ada", "last_name" => "Lovelace", "email" => "ada@example.com"]);
    }

    public function testTheIndexDefaultsToUnmatchedLines(): void
    {
        BankStatementLine::create([
            "iban" => "DE02120300000000202051",
            "amount" => "10.00",
            "reference" => "Unmatched entry",
            "booked_at" => "2026-01-05",
        ]);
        $debit = Debit::create([
            "contact_id" => $this->contact()->id,
            "source" => "donation",
            "iban" => "DE02120300000000202051",
            "account_holder" => "Familie Lovelace",
            "amount" => "5.00",
            "mandate" => "M1",
            "mandate_date" => "2026-01-01",
            "status" => "pending",
            "end_to_end_reference" => "E2E-1",
            "due_date" => "2026-01-01",
        ]);
        BankStatementLine::create([
            "iban" => "DE02120300000000202051",
            "amount" => "5.00",
            "reference" => "Matched entry",
            "booked_at" => "2026-01-01",
            "matched_type" => "debit",
            "matched_id" => $debit->id,
            "match_method" => "mandate_reference",
            "matched_at" => now(),
        ]);

        $response = $this->get("/admin/assoc/bank-statements");

        $response->assertOk();
        $response->assertSee("Unmatched entry");
        $response->assertDontSee("Matched entry");
    }

    public function testTheAllFilterShowsBothMatchedAndUnmatchedLines(): void
    {
        BankStatementLine::create([
            "iban" => "DE02120300000000202051",
            "amount" => "10.00",
            "reference" => "Unmatched entry",
            "booked_at" => "2026-01-05",
        ]);

        $response = $this->get("/admin/assoc/bank-statements?status=all");

        $response->assertOk();
        $response->assertSee("Unmatched entry");
    }

    public function testTheDetailPageSearchesPendingDebitsByAccountHolder(): void
    {
        $line = BankStatementLine::create([
            "iban" => "DE02120300000000202051",
            "amount" => "10.00",
            "reference" => "Beitrag",
            "booked_at" => "2026-01-05",
        ]);
        Debit::create([
            "contact_id" => $this->contact()->id,
            "source" => "donation",
            "iban" => "DE02120300000000202051",
            "account_holder" => "Familie Lovelace",
            "amount" => "10.00",
            "mandate" => "M1",
            "mandate_date" => "2026-01-01",
            "status" => "pending",
            "end_to_end_reference" => "E2E-1",
            "due_date" => "2026-01-05",
        ]);

        $response = $this->get("/admin/assoc/bank-statements/{$line->id}?q=Lovelace");

        $response->assertOk();
        $response->assertSee("Familie Lovelace");
    }

    public function testManuallyMatchingALineToADebitRecordsTheMethodAndAssignsTheDebit(): void
    {
        $line = BankStatementLine::create([
            "iban" => "DE02120300000000202051",
            "amount" => "10.00",
            "reference" => "Beitrag",
            "booked_at" => "2026-01-05",
        ]);
        $debit = Debit::create([
            "contact_id" => $this->contact()->id,
            "source" => "donation",
            "iban" => "DE02120300000000202051",
            "account_holder" => "Familie Lovelace",
            "amount" => "10.00",
            "mandate" => "M1",
            "mandate_date" => "2026-01-01",
            "status" => "pending",
            "end_to_end_reference" => "E2E-1",
            "due_date" => "2026-01-05",
        ]);

        $response = $this->post("/admin/assoc/bank-statements/{$line->id}/match", [
            "type" => "debit",
            "target_id" => $debit->id,
        ]);

        $response->assertRedirect(route("assoc_admin_bank_statements"));
        $line->refresh();
        $this->assertSame("debit", $line->matched_type);
        $this->assertSame($debit->id, $line->matched_id);
        $this->assertSame("manual", $line->match_method);
        $this->assertNotNull($line->matched_at);
        $this->assertSame("executed", $debit->fresh()->status);
    }

    public function testAnAlreadyMatchedLineCannotBeMatchedAgain(): void
    {
        $debit = Debit::create([
            "contact_id" => $this->contact()->id,
            "source" => "donation",
            "iban" => "DE02120300000000202051",
            "account_holder" => "Familie Lovelace",
            "amount" => "10.00",
            "mandate" => "M1",
            "mandate_date" => "2026-01-01",
            "status" => "pending",
            "end_to_end_reference" => "E2E-1",
            "due_date" => "2026-01-05",
        ]);
        $line = BankStatementLine::create([
            "iban" => "DE02120300000000202051",
            "amount" => "10.00",
            "booked_at" => "2026-01-05",
            "matched_type" => "debit",
            "matched_id" => $debit->id,
            "match_method" => "mandate_reference",
            "matched_at" => now(),
        ]);

        $this->post("/admin/assoc/bank-statements/{$line->id}/match", [
            "type" => "debit",
            "target_id" => $debit->id,
        ])->assertNotFound();
    }

    public function testRematchingUpdatesLinesThatNowHaveAMatchingDebit(): void
    {
        $line = BankStatementLine::create([
            "iban" => "DE02120300000000202051",
            "amount" => "10.00",
            "reference" => "Beitrag M1 Januar",
            "booked_at" => "2026-01-05",
        ]);
        $debit = Debit::create([
            "contact_id" => $this->contact()->id,
            "source" => "donation",
            "iban" => "DE02120300000000202051",
            "account_holder" => "Familie Lovelace",
            "amount" => "10.00",
            "mandate" => "M1",
            "mandate_date" => "2026-01-01",
            "status" => "pending",
            "end_to_end_reference" => "E2E-1",
            "due_date" => "2026-01-05",
        ]);

        $response = $this->post("/admin/assoc/bank-statements/rematch");

        $response->assertRedirect(route("assoc_admin_bank_statements"));
        $line->refresh();
        $this->assertSame($debit->id, $line->matched_id);
        $this->assertSame("regex", $line->match_method);
        $this->assertSame("executed", $debit->fresh()->status);
    }

    /**
     * The manual counterpart of BankStatementMatcher::matchChargeback() — for
     * when the automatic lookup by end-to-end reference/mandate couldn't find
     * the bounced debit (see BankStatementController's class docblock).
     */
    public function testTheDetailPageForAChargebackLineSearchesExecutedDebitsOnly(): void
    {
        $line = BankStatementLine::create([
            "iban" => "DE02120300000000202051",
            "amount" => "-12.50",
            "reference" => "Retourenbelastung",
            "booked_at" => "2026-01-10",
        ]);
        $executed = Debit::create([
            "contact_id" => $this->contact()->id,
            "source" => "donation",
            "iban" => "DE02120300000000202051",
            "account_holder" => "Familie Lovelace",
            "amount" => "10.00",
            "mandate" => "M1",
            "mandate_date" => "2026-01-01",
            "status" => "executed",
            "end_to_end_reference" => "E2E-1",
            "due_date" => "2026-01-05",
        ]);
        Debit::create([
            "contact_id" => $this->contact()->id,
            "source" => "donation",
            "iban" => "DE02120300000000202051",
            "account_holder" => "Familie Lovelace",
            "amount" => "10.00",
            "mandate" => "M2",
            "mandate_date" => "2026-01-01",
            "status" => "pending",
            "end_to_end_reference" => "E2E-2",
            "due_date" => "2026-01-05",
        ]);

        $response = $this->get("/admin/assoc/bank-statements/{$line->id}?q=Lovelace");

        $response->assertOk();
        $response->assertSee("Rücklastschrift zuordnen");
        $response->assertSee("M1");
        $response->assertDontSee("M2");
        // The fee this candidate would produce (12.50 - 10.00), shown so an
        // admin can tell at a glance whether a candidate is plausible.
        $response->assertSee("2,50");
    }

    public function testManuallyMatchingAChargebackLineReversesThePaymentAndChargesTheFee(): void
    {
        $line = BankStatementLine::create([
            "iban" => "DE02120300000000202051",
            "amount" => "-12.50",
            "reference" => "Retourenbelastung",
            "booked_at" => "2026-01-10",
        ]);
        $debit = Debit::create([
            "contact_id" => $this->contact()->id,
            "source" => "donation",
            "iban" => "DE02120300000000202051",
            "account_holder" => "Familie Lovelace",
            "amount" => "10.00",
            "mandate" => "M1",
            "mandate_date" => "2026-01-01",
            "status" => "executed",
            "end_to_end_reference" => "E2E-1",
            "due_date" => "2026-01-05",
        ]);

        $response = $this->post("/admin/assoc/bank-statements/{$line->id}/match", [
            "type" => "debit",
            "target_id" => $debit->id,
        ]);

        $response->assertRedirect(route("assoc_admin_bank_statements"));
        $line->refresh();
        $this->assertSame("debit_reversal", $line->matched_type);
        $this->assertSame($debit->id, $line->matched_id);
        $debit->refresh();
        $this->assertSame("failed", $debit->status);
        $this->assertSame("10.00", (string) LedgerEntry::where("kind", "refund")->sole()->amount);
        $this->assertSame("2.50", (string) LedgerEntry::where("kind", "chargeback_fee")->sole()->amount);
    }

    public function testManuallyMatchingAChargebackLineToAnAlreadyFailedDebitIsRejected(): void
    {
        $line = BankStatementLine::create([
            "iban" => "DE02120300000000202051",
            "amount" => "-12.50",
            "booked_at" => "2026-01-10",
        ]);
        $debit = Debit::create([
            "contact_id" => $this->contact()->id,
            "source" => "donation",
            "iban" => "DE02120300000000202051",
            "account_holder" => "Familie Lovelace",
            "amount" => "10.00",
            "mandate" => "M1",
            "mandate_date" => "2026-01-01",
            "status" => "failed",
            "end_to_end_reference" => "E2E-1",
            "due_date" => "2026-01-05",
        ]);

        $this->post("/admin/assoc/bank-statements/{$line->id}/match", [
            "type" => "debit",
            "target_id" => $debit->id,
        ])->assertStatus(422);

        $this->assertNull($line->fresh()->matched_type);
    }

    public function testManuallyMatchingAChargebackLineThatWouldProduceANonPositiveFeeIsRejected(): void
    {
        $line = BankStatementLine::create([
            "iban" => "DE02120300000000202051",
            "amount" => "-10.00",
            "booked_at" => "2026-01-10",
        ]);
        $debit = Debit::create([
            "contact_id" => $this->contact()->id,
            "source" => "donation",
            "iban" => "DE02120300000000202051",
            "account_holder" => "Familie Lovelace",
            "amount" => "10.00",
            "mandate" => "M1",
            "mandate_date" => "2026-01-01",
            "status" => "executed",
            "end_to_end_reference" => "E2E-1",
            "due_date" => "2026-01-05",
        ]);

        $this->post("/admin/assoc/bank-statements/{$line->id}/match", [
            "type" => "debit",
            "target_id" => $debit->id,
        ])->assertStatus(422);

        $this->assertSame("executed", $debit->fresh()->status);
        $this->assertNull($line->fresh()->matched_type);
    }

    public function testAChargebackLineCannotBeManuallyMatchedToARecurContribution(): void
    {
        $line = BankStatementLine::create([
            "iban" => "DE02120300000000202051",
            "amount" => "-12.50",
            "booked_at" => "2026-01-10",
        ]);

        $this->post("/admin/assoc/bank-statements/{$line->id}/match", [
            "type" => "recur_contribution",
            "target_id" => "00000000-0000-4000-8000-000000000000",
        ])->assertStatus(422);
    }
}
