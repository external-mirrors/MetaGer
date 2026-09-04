<?php

namespace Tests\Unit\Assoc;

use App\Models\Assoc\Contact;
use App\Models\Assoc\Debit;
use App\Models\Assoc\DonationReceipt;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Tests\Concerns\UsesInMemorySqlite;
use Tests\TestCase;

class GenerateDonationReceiptsCommandTest extends TestCase
{
    use DatabaseTransactions;
    use UsesInMemorySqlite;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpInMemorySqlite();
        Storage::fake("local");
    }

    private function contact(array $overrides = []): Contact
    {
        return Contact::create(array_merge([
            "first_name" => "Ada",
            "last_name" => "Lovelace",
            "email" => "ada@example.test",
            "street" => "Beispielstraße 1",
            "postal_code" => "30159",
            "city" => "Hannover",
        ], $overrides));
    }

    private function debit(Contact $contact, array $overrides = []): Debit
    {
        return Debit::create(array_merge([
            "contact_id" => $contact->id,
            "source" => "donation",
            "iban" => "DE02120300000000202051",
            "account_holder" => "Ada Lovelace",
            "amount" => "10.00",
            "mandate" => "M1",
            "mandate_date" => "2026-01-01",
            "status" => "executed",
            "end_to_end_reference" => "E2E-" . uniqid(),
            "due_date" => "2026-02-01",
        ], $overrides));
    }

    public function testWithNoOptionsGeneratesImmediatePreferencePayers(): void
    {
        $contact = $this->contact(["donation_receipt_preference" => "immediate"]);
        $this->debit($contact);

        Artisan::call("assoc:generate-donation-receipts");

        $this->assertSame(1, DonationReceipt::count());
        $this->assertStringContainsString("Sofort-Bescheinigungen erstellt: 1", Artisan::output());
    }

    public function testTheYearOptionGeneratesTheAnnualBatch(): void
    {
        $contact = $this->contact(["donation_receipt_preference" => "annual"]);
        $this->debit($contact);

        Artisan::call("assoc:generate-donation-receipts", ["--year" => "2026"]);

        $this->assertSame(1, DonationReceipt::count());
        $this->assertStringContainsString("Jahresbescheinigungen für 2026 erstellt: 1", Artisan::output());
    }

    public function testTheDebitOptionGeneratesASingleReceiptRegardlessOfPreference(): void
    {
        $contact = $this->contact(["donation_receipt_preference" => "never"]);
        $debit = $this->debit($contact);

        $exitCode = Artisan::call("assoc:generate-donation-receipts", ["--debit" => [$debit->id]]);

        $this->assertSame(0, $exitCode);
        $this->assertSame(1, DonationReceipt::count());
        $this->assertNotNull($debit->fresh()->donation_receipt_id);
    }

    public function testTheDebitOptionFailsCleanlyOnAnUnknownId(): void
    {
        $exitCode = Artisan::call("assoc:generate-donation-receipts", ["--debit" => ["does-not-exist"]]);

        $this->assertSame(1, $exitCode);
    }

    public function testDebitAndYearAreMutuallyExclusive(): void
    {
        $exitCode = Artisan::call("assoc:generate-donation-receipts", ["--debit" => ["x"], "--year" => "2026"]);

        $this->assertSame(1, $exitCode);
    }
}
