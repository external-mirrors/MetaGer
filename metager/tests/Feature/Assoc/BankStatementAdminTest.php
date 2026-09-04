<?php

namespace Tests\Feature\Assoc;

use App\Models\Assoc\BankStatementLine;
use App\Models\Assoc\Debit;
use App\Models\Assoc\Household;
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

    private function household(): Household
    {
        return Household::create(["household_name" => "Familie Lovelace"]);
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
            "household_id" => $this->household()->id,
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
            "household_id" => $this->household()->id,
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
            "household_id" => $this->household()->id,
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
    }

    public function testAnAlreadyMatchedLineCannotBeMatchedAgain(): void
    {
        $debit = Debit::create([
            "household_id" => $this->household()->id,
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
            "household_id" => $this->household()->id,
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
    }
}
