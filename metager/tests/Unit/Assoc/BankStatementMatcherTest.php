<?php

namespace Tests\Unit\Assoc;

use App\Assoc\BankStatementMatcher;
use App\Models\Assoc\BankStatementLine;
use App\Models\Assoc\Debit;
use App\Models\Assoc\Household;
use App\Models\Assoc\RecurContribution;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Concerns\UsesInMemorySqlite;
use Tests\TestCase;

/**
 * The four-tier cascade ported from de.suma-ev.bescheinigungen's
 * FetchBankAccount.php/IncomingPayment/Auto.php — see BankStatementMatcher's
 * docblock for the mapping onto assoc_debits/assoc_recur_contributions.
 */
class BankStatementMatcherTest extends TestCase
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

    private function debit(Household $household, array $overrides = []): Debit
    {
        return Debit::create(array_merge([
            "household_id" => $household->id,
            "source" => "donation",
            "iban" => "DE02120300000000202051",
            "account_holder" => "Familie Lovelace",
            "amount" => "10.00",
            "mandate" => "M1",
            "mandate_date" => "2026-01-01",
            "status" => "pending",
            "end_to_end_reference" => "E2E-" . uniqid(),
            "due_date" => "2026-02-01",
        ], $overrides));
    }

    private function line(array $overrides = []): BankStatementLine
    {
        return BankStatementLine::create(array_merge([
            "iban" => "DE02120300000000202051",
            "amount" => "10.00",
            "booked_at" => "2026-02-01",
        ], $overrides));
    }

    public function testMatchesByEndToEndReferenceOnAPendingDebit(): void
    {
        $debit = $this->debit($this->household(), ["end_to_end_reference" => "E2E-42"]);
        $line = $this->line();

        $matched = (new BankStatementMatcher())->match($line, mandate: null, endToEndReference: "E2E-42");

        $this->assertTrue($matched);
        $line->refresh();
        $this->assertSame("debit", $line->matched_type);
        $this->assertSame($debit->id, $line->matched_id);
        $this->assertSame("mandate_reference", $line->match_method);
        $this->assertNotNull($line->matched_at);
    }

    public function testMatchesByStructuredMandateOnAPendingDebit(): void
    {
        $debit = $this->debit($this->household());
        $line = $this->line();

        $matched = (new BankStatementMatcher())->match($line, mandate: "M1");

        $this->assertTrue($matched);
        $line->refresh();
        $this->assertSame("debit", $line->matched_type);
        $this->assertSame($debit->id, $line->matched_id);
        $this->assertSame("mandate_reference", $line->match_method);
    }

    public function testMatchesByStructuredMandateOnAnActiveRecurContribution(): void
    {
        $household = $this->household();
        $recur = RecurContribution::create([
            "household_id" => $household->id,
            "source" => "donation",
            "iban" => "DE02120300000000202051",
            "amount" => "10.00",
            "mandate" => "S1",
            "mandate_date" => "2026-01-01",
            "frequency" => "monthly",
            "active" => true,
        ]);
        $line = $this->line();

        $matched = (new BankStatementMatcher())->match($line, mandate: "S1");

        $this->assertTrue($matched);
        $line->refresh();
        $this->assertSame("recur_contribution", $line->matched_type);
        $this->assertSame($recur->id, $line->matched_id);
    }

    public function testPrefersTheDebitWhoseAmountMatchesExactlyAmongSeveralOnTheSameMandate(): void
    {
        $household = $this->household();
        $this->debit($household, ["amount" => "5.00", "due_date" => "2026-01-01"]);
        $wanted = $this->debit($household, ["amount" => "10.00", "due_date" => "2026-03-01"]);
        $line = $this->line(["amount" => "10.00"]);

        (new BankStatementMatcher())->match($line, mandate: "M1");

        $line->refresh();
        $this->assertSame($wanted->id, $line->matched_id);
    }

    public function testFallsBackToTheEarliestDueDebitWhenNoAmountMatches(): void
    {
        $household = $this->household();
        $earliest = $this->debit($household, ["amount" => "5.00", "due_date" => "2026-01-01"]);
        $this->debit($household, ["amount" => "7.00", "due_date" => "2026-03-01"]);
        $line = $this->line(["amount" => "10.00"]);

        (new BankStatementMatcher())->match($line, mandate: "M1");

        $line->refresh();
        $this->assertSame($earliest->id, $line->matched_id);
    }

    public function testMatchesViaRegexWhenTheMandateAppearsAsAWholeWordInTheFreeText(): void
    {
        $debit = $this->debit($this->household());
        $line = $this->line(["reference" => "Beitrag M1 Januar"]);

        $matched = (new BankStatementMatcher())->match($line);

        $this->assertTrue($matched);
        $line->refresh();
        $this->assertSame($debit->id, $line->matched_id);
        $this->assertSame("regex", $line->match_method);
    }

    public function testFallsBackToSubstringWhenTheMandateIsGluedToOtherText(): void
    {
        $debit = $this->debit($this->household());
        // "M1" is not a whole word here — bounded on the right by "0" — so the
        // regex tier must not fire; only the looser substring tier should.
        $line = $this->line(["reference" => "BeitragM10Januar"]);

        $matched = (new BankStatementMatcher())->match($line);

        $this->assertTrue($matched);
        $line->refresh();
        $this->assertSame($debit->id, $line->matched_id);
        $this->assertSame("substring", $line->match_method);
    }

    public function testLeavesALineUnmatchedWhenNothingCorroboratesIt(): void
    {
        $this->debit($this->household());
        $line = $this->line(["reference" => "Vielen Dank für die Spende"]);

        $matched = (new BankStatementMatcher())->match($line);

        $this->assertFalse($matched);
        $line->refresh();
        $this->assertNull($line->matched_type);
    }

    public function testRematchUnresolvedPicksUpDebitsCreatedAfterTheLine(): void
    {
        $line = $this->line(["reference" => "Beitrag M1 Januar"]);
        $debit = $this->debit($this->household());

        $result = (new BankStatementMatcher())->rematchUnresolved();

        $this->assertSame(1, $result["matched"]);
        $this->assertSame(0, $result["still_unmatched"]);
        $line->refresh();
        $this->assertSame($debit->id, $line->matched_id);
        $this->assertSame("regex", $line->match_method);
    }

    public function testDoesNotMatchAnAlreadyExecutedDebit(): void
    {
        $this->debit($this->household(), ["status" => "executed"]);
        $line = $this->line();

        $matched = (new BankStatementMatcher())->match($line, mandate: "M1");

        $this->assertFalse($matched);
    }
}
