<?php

namespace Tests\Unit\Assoc;

use App\Models\Assoc\Contact;
use App\Models\Assoc\Debit;
use App\Models\Assoc\Membership;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Artisan;
use Tests\Concerns\UsesInMemorySqlite;
use Tests\TestCase;

class CreateDebitsCommandTest extends TestCase
{
    use DatabaseTransactions;
    use UsesInMemorySqlite;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpInMemorySqlite();
        Carbon::setTestNow(Carbon::parse("2026-02-01"));
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function testReportsHowManyDebitsItCreated(): void
    {
        $contact = Contact::create(["first_name" => "Ada", "last_name" => "Lovelace", "email" => "ada@example.com"]);
        Debit::create([
            "contact_id" => $contact->id,
            "source" => "membership",
            "iban" => "DE02120300000000202051",
            "account_holder" => "Familie Lovelace",
            "amount" => "5.00",
            "mandate" => "M1",
            "mandate_date" => "2020-01-01",
            "status" => "executed",
            "end_to_end_reference" => "E2E-past",
            "due_date" => "2026-01-01",
        ]);
        Membership::create([
            "contact_id" => $contact->id,
            "membership_type" => "person",
            "interval" => "monthly",
            "amount" => "5.00",
            "payment_method" => "directdebit",
            "payment_reference" => "M1",
            "join_date" => "2020-01-01",
            "standing" => "active",
            "end_date" => "2026-02-10",
        ]);

        Artisan::call("assoc:create-debits");
        $output = Artisan::output();

        $this->assertStringContainsString("Mitgliedsbeiträge angelegt: 1", $output);
        $this->assertStringContainsString("Daueraufträge angelegt: 0", $output);
        $this->assertSame(2, Debit::count());
    }
}
