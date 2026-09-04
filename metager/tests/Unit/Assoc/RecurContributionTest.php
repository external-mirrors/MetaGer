<?php

namespace Tests\Unit\Assoc;

use App\Models\Assoc\Household;
use App\Models\Assoc\RecurContribution;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Concerns\UsesInMemorySqlite;
use Tests\TestCase;

class RecurContributionTest extends TestCase
{
    use DatabaseTransactions;
    use UsesInMemorySqlite;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpInMemorySqlite();
    }

    private function baseAttributes(): array
    {
        return [
            "source" => "donation",
            "iban" => "DE02120300000000202051",
            "mandate_date" => "2026-01-01",
        ];
    }

    public function testActiveDefaultsToTrue(): void
    {
        $household = Household::create(["household_name" => "Familie Lovelace"]);
        $recur = RecurContribution::create(array_merge($this->baseAttributes(), [
            "household_id" => $household->id,
            "amount" => "10.00",
            "mandate" => "S1",
            "frequency" => "monthly",
        ]));

        $this->assertTrue($recur->fresh()->active);
    }

    public function testAMandateMustBeUnique(): void
    {
        $household = Household::create(["household_name" => "Familie Lovelace"]);
        RecurContribution::create(array_merge($this->baseAttributes(), [
            "household_id" => $household->id,
            "amount" => "10.00",
            "mandate" => "S1",
            "frequency" => "monthly",
        ]));

        $this->expectException(QueryException::class);
        RecurContribution::create(array_merge($this->baseAttributes(), [
            "household_id" => $household->id,
            "amount" => "10.00",
            "mandate" => "S1",
            "frequency" => "monthly",
        ]));
    }

    public function testAnUnknownFrequencyIsRejectedByTheDatabase(): void
    {
        $household = Household::create(["household_name" => "Familie Lovelace"]);

        $this->expectException(QueryException::class);
        RecurContribution::create(array_merge($this->baseAttributes(), [
            "household_id" => $household->id,
            "amount" => "10.00",
            "mandate" => "S1",
            "frequency" => "weekly",
        ]));
    }
}
