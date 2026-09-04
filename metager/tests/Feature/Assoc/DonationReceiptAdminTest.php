<?php

namespace Tests\Feature\Assoc;

use App\Assoc\DonationReceiptGenerator;
use App\Assoc\DonationReceiptPdf;
use App\Models\Assoc\Contact;
use App\Models\Assoc\Debit;
use App\Models\Assoc\DonationReceipt;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Storage;
use Tests\Concerns\UsesInMemorySqlite;
use Tests\TestCase;

/**
 * `/admin/assoc/donation-receipts/*` and the "Bescheinigung erstellen" action
 * on a debit — see docs/civicrm-replacement.md phase 5. Unlike AssocAdminTest's
 * routes this one writes; carries no auth middleware under APP_ENV=testing for
 * the same reason documented there.
 */
class DonationReceiptAdminTest extends TestCase
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

    public function testTheIndexListsGeneratedReceipts(): void
    {
        $contact = $this->contact();
        $debit = $this->debit($contact);
        (new DonationReceiptGenerator(new DonationReceiptPdf()))->generateSingle($debit);

        $response = $this->get("/admin/assoc/donation-receipts");

        $response->assertOk();
        $response->assertSee("Ada");
    }

    public function testGeneratingAReceiptForAnExecutedDebitLinksItAndRedirectsBack(): void
    {
        $contact = $this->contact();
        $debit = $this->debit($contact);

        $response = $this->from("/admin/assoc/members/contact/{$contact->id}")
            ->post("/admin/assoc/debits/{$debit->id}/generate-receipt");

        $response->assertRedirect("/admin/assoc/members/contact/{$contact->id}");
        $this->assertSame(1, DonationReceipt::count());
        $this->assertNotNull($debit->fresh()->donation_receipt_id);
    }

    public function testGeneratingAReceiptForAPendingDebitFails(): void
    {
        $contact = $this->contact();
        $debit = $this->debit($contact, ["status" => "pending"]);

        $response = $this->post("/admin/assoc/debits/{$debit->id}/generate-receipt");

        $response->assertStatus(422);
        $this->assertSame(0, DonationReceipt::count());
    }

    public function testDownloadingAReceiptStreamsThePdf(): void
    {
        $contact = $this->contact();
        $debit = $this->debit($contact);
        $receipt = (new DonationReceiptGenerator(new DonationReceiptPdf()))->generateSingle($debit);

        $response = $this->get("/admin/assoc/donation-receipts/{$receipt->id}/download");

        $response->assertOk();
    }
}
