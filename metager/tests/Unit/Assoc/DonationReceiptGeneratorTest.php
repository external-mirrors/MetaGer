<?php

namespace Tests\Unit\Assoc;

use App\Assoc\DonationReceiptGenerator;
use App\Assoc\DonationReceiptPdf;
use App\Models\Assoc\Contact;
use App\Models\Assoc\Debit;
use App\Models\Assoc\DonationReceipt;
use App\Models\Assoc\Household;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Storage;
use Tests\Concerns\UsesInMemorySqlite;
use Tests\TestCase;

class DonationReceiptGeneratorTest extends TestCase
{
    use DatabaseTransactions;
    use UsesInMemorySqlite;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpInMemorySqlite();
        Storage::fake("local");
    }

    private function generator(): DonationReceiptGenerator
    {
        return new DonationReceiptGenerator(new DonationReceiptPdf());
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

    private function debit(Contact|Household $payer, array $overrides = []): Debit
    {
        $payerColumn = $payer instanceof Contact ? "contact_id" : "household_id";

        return Debit::create(array_merge([
            $payerColumn => $payer->id,
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

    public function testGenerateSingleCreatesAReceiptAndLinksTheDebit(): void
    {
        $contact = $this->contact();
        $debit = $this->debit($contact, ["amount" => "12.34"]);

        $receipt = $this->generator()->generateSingle($debit);

        $this->assertInstanceOf(DonationReceipt::class, $receipt);
        $this->assertSame($contact->id, $receipt->contact_id);
        $this->assertSame("2026", (string) $receipt->year);
        $this->assertSame("12.34", (string) $receipt->total_amount);
        $this->assertSame("donation", $receipt->source);
        $this->assertNotNull($receipt->generated_at);
        $this->assertNotNull($receipt->pdf_path);
        Storage::disk("local")->assertExists($receipt->pdf_path);

        $debit->refresh();
        $this->assertSame($receipt->id, $debit->donation_receipt_id);
    }

    public function testGenerateSingleRejectsAPendingDebit(): void
    {
        $debit = $this->debit($this->contact(), ["status" => "pending"]);

        $this->expectException(\RuntimeException::class);
        $this->generator()->generateSingle($debit);
    }

    public function testGenerateSingleRejectsAnAlreadyReceiptedDebit(): void
    {
        $debit = $this->debit($this->contact());
        $this->generator()->generateSingle($debit);

        $this->expectException(\RuntimeException::class);
        $this->generator()->generateSingle($debit->fresh());
    }

    public function testGenerateImmediatePicksUpOnlyImmediatePreferencePayers(): void
    {
        $immediate = $this->contact(["email" => "immediate@example.test", "donation_receipt_preference" => "immediate"]);
        $annual = $this->contact(["email" => "annual@example.test", "donation_receipt_preference" => "annual"]);
        $debitImmediate = $this->debit($immediate);
        $debitAnnual = $this->debit($annual);

        $receipts = $this->generator()->generateImmediate();

        $this->assertCount(1, $receipts);
        $this->assertSame($immediate->id, $receipts->first()->contact_id);
        $this->assertNull($debitAnnual->fresh()->donation_receipt_id);
        $this->assertNotNull($debitImmediate->fresh()->donation_receipt_id);
    }

    public function testGenerateImmediateSkipsNeverPreferencePayers(): void
    {
        $never = $this->contact(["donation_receipt_preference" => "never"]);
        $this->debit($never);

        $receipts = $this->generator()->generateImmediate();

        $this->assertCount(0, $receipts);
    }

    public function testGenerateAnnualBatchGroupsDebitsByPayerAndSource(): void
    {
        $contact = $this->contact(["donation_receipt_preference" => "annual"]);
        $donation1 = $this->debit($contact, ["amount" => "5.00", "end_to_end_reference" => "E2E-a"]);
        $donation2 = $this->debit($contact, ["amount" => "7.50", "end_to_end_reference" => "E2E-b"]);
        $dues = $this->debit($contact, ["source" => "membership", "amount" => "20.00", "end_to_end_reference" => "E2E-c"]);

        $receipts = $this->generator()->generateAnnualBatch(2026);

        $this->assertCount(2, $receipts);
        $donationReceipt = $receipts->firstWhere("source", "donation");
        $this->assertSame("12.50", (string) $donationReceipt->total_amount);
        $this->assertSame($donation1->fresh()->donation_receipt_id, $donation2->fresh()->donation_receipt_id);

        $duesReceipt = $receipts->firstWhere("source", "membership");
        $this->assertSame("20.00", (string) $duesReceipt->total_amount);
        $this->assertSame($duesReceipt->id, $dues->fresh()->donation_receipt_id);
    }

    public function testGenerateAnnualBatchExcludesDebitsDueAfterTheRequestedYear(): void
    {
        $contact = $this->contact(["donation_receipt_preference" => "annual"]);
        $this->debit($contact, ["due_date" => "2027-01-15"]);

        $receipts = $this->generator()->generateAnnualBatch(2026);

        $this->assertCount(0, $receipts);
    }

    public function testAPayerWithNoPreferenceGetsNoAutomaticReceiptByDefault(): void
    {
        $contact = $this->contact();
        $this->debit($contact);

        $this->assertCount(0, $this->generator()->generateImmediate());
        $this->assertCount(0, $this->generator()->generateAnnualBatch(2026));
    }

    public function testEffectivePreferenceFallsBackToTheConfiguredDefault(): void
    {
        config(["assoc.donation_receipt_default_preference" => "immediate"]);
        $contact = $this->contact();
        $this->debit($contact);

        $receipts = $this->generator()->generateImmediate();

        $this->assertCount(1, $receipts);
    }

    public function testGenerateForPayerFoldsAllOutstandingDebitsOfOneSourceIntoOneReceipt(): void
    {
        $contact = $this->contact();
        $old = $this->debit($contact, ["amount" => "5.00", "due_date" => "2024-03-01", "end_to_end_reference" => "E2E-a"]);
        $recent = $this->debit($contact, ["amount" => "7.00", "due_date" => "2026-01-15", "end_to_end_reference" => "E2E-b"]);
        $dues = $this->debit($contact, ["source" => "membership", "amount" => "20.00", "end_to_end_reference" => "E2E-c"]);

        $receipt = $this->generator()->generateForPayer($contact, "donation");

        $this->assertNotNull($receipt);
        $this->assertSame("12.00", (string) $receipt->total_amount);
        $this->assertSame(2026, $receipt->year);
        $this->assertSame($receipt->id, $old->fresh()->donation_receipt_id);
        $this->assertSame($receipt->id, $recent->fresh()->donation_receipt_id);
        $this->assertNull($dues->fresh()->donation_receipt_id);
    }

    public function testGenerateForPayerReturnsNullWhenNothingIsOutstanding(): void
    {
        $contact = $this->contact();

        $receipt = $this->generator()->generateForPayer($contact, "donation");

        $this->assertNull($receipt);
    }

    public function testGenerateForPayerIgnoresThePayersPreference(): void
    {
        $contact = $this->contact(["donation_receipt_preference" => "never"]);
        $this->debit($contact);

        $receipt = $this->generator()->generateForPayer($contact, "donation");

        $this->assertNotNull($receipt);
    }

    public function testGenerateAnnualBatchOnlyIncludesUnreceiptedDebits(): void
    {
        $contact = $this->contact(["donation_receipt_preference" => "annual"]);
        $debit = $this->debit($contact);
        $this->generator()->generateSingle($debit);

        $receipts = $this->generator()->generateAnnualBatch(2026);

        $this->assertCount(0, $receipts);
    }
}
