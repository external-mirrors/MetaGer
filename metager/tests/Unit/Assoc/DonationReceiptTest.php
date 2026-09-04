<?php

namespace Tests\Unit\Assoc;

use App\Models\Assoc\DonationReceipt;
use App\Models\Assoc\Household;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Concerns\UsesInMemorySqlite;
use Tests\TestCase;

class DonationReceiptTest extends TestCase
{
    use DatabaseTransactions;
    use UsesInMemorySqlite;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpInMemorySqlite();
    }

    public function testAReceiptCanBeCreatedForAHousehold(): void
    {
        $household = Household::create(["household_name" => "Familie Lovelace"]);
        $receipt = DonationReceipt::create([
            "household_id" => $household->id,
            "year" => 2026,
            "total_amount" => "123.45",
        ]);

        $reloaded = DonationReceipt::findOrFail($receipt->id);
        $this->assertSame(2026, $reloaded->year);
        $this->assertSame("123.45", $reloaded->total_amount);
        $this->assertTrue($reloaded->household->is($household));
        $this->assertNull($reloaded->generated_at);
    }
}
