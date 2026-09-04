<?php

namespace Tests\Unit\Assoc;

use App\Models\Assoc\Debit;
use App\Models\Assoc\Household;
use App\Models\Assoc\RecurContribution;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Concerns\UsesInMemorySqlite;
use Tests\TestCase;

class HouseholdTest extends TestCase
{
    use DatabaseTransactions;
    use UsesInMemorySqlite;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpInMemorySqlite();
    }

    public function testAHouseholdCanBeCreatedFromJustAName(): void
    {
        $household = Household::create(["household_name" => "Familie Lovelace"]);

        $reloaded = Household::findOrFail($household->id);
        $this->assertSame("Familie Lovelace", $reloaded->household_name);
        $this->assertNull($reloaded->street);
    }

    public function testAHouseholdCanOwnADebitAndARecurContribution(): void
    {
        $household = Household::create(["household_name" => "Familie Lovelace"]);

        Debit::create([
            "household_id" => $household->id,
            "source" => "donation",
            "iban" => "DE02120300000000202051",
            "account_holder" => "Familie Lovelace",
            "amount" => "25.00",
            "mandate" => "S1",
            "mandate_date" => "2026-01-01",
            "end_to_end_reference" => "E2E-1",
            "due_date" => "2026-02-01",
        ]);
        RecurContribution::create([
            "household_id" => $household->id,
            "source" => "donation",
            "iban" => "DE02120300000000202051",
            "amount" => "10.00",
            "mandate" => "S2",
            "mandate_date" => "2026-01-01",
            "frequency" => "monthly",
        ]);

        $this->assertCount(1, $household->debits);
        $this->assertCount(1, $household->recurContributions);
    }
}
