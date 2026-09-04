<?php

namespace Tests\Unit\Assoc;

use App\Models\Assoc\Contact;
use App\Models\Assoc\DonationReceipt;
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

    public function testAReceiptCanBeCreatedForAContact(): void
    {
        $contact = Contact::create(["first_name" => "Ada", "last_name" => "Lovelace", "email" => "ada@example.com"]);
        $receipt = DonationReceipt::create([
            "contact_id" => $contact->id,
            "year" => 2026,
            "total_amount" => "123.45",
        ]);

        $reloaded = DonationReceipt::findOrFail($receipt->id);
        $this->assertSame(2026, $reloaded->year);
        $this->assertSame("123.45", $reloaded->total_amount);
        $this->assertTrue($reloaded->contact->is($contact));
        $this->assertNull($reloaded->generated_at);
    }
}
