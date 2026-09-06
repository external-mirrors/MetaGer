<?php

namespace Tests\Feature\Assoc;

use App\Models\Assoc\Contact;
use App\Models\Assoc\Debit;
use App\Models\Assoc\SepaBatch;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Storage;
use Tests\Concerns\UsesInMemorySqlite;
use Tests\TestCase;

/**
 * `/admin/assoc/sepa-batches` — the pain.008 generation half of phase 6c, see
 * SepaDirectDebitBatchGenerator and docs/civicrm-replacement.md. Carries no
 * auth middleware under APP_ENV=testing, same as the other assoc admin
 * routes.
 */
class SepaBatchAdminTest extends TestCase
{
    use DatabaseTransactions;
    use UsesInMemorySqlite;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpInMemorySqlite();
        Storage::fake("local");
        config([
            "assoc.sepa_creditor_name" => "Test Verein",
            "assoc.sepa_creditor_iban" => "DE89370400440532013000",
            "assoc.sepa_creditor_bic" => "COBADEFFXXX",
            "assoc.sepa_creditor_id" => "DE98ZZZ09999999999",
        ]);
    }

    private function pendingDebit(array $overrides = []): Debit
    {
        $contact = Contact::create(["first_name" => "Ada", "last_name" => "Lovelace", "email" => "ada@example.com"]);

        return Debit::create(array_merge([
            "contact_id" => $contact->id,
            "source" => "membership",
            "iban" => "DE02120300000000202051",
            "bic" => "GENODEF1S02",
            "account_holder" => "Familie Lovelace",
            "amount" => "10.00",
            "mandate" => "M1",
            "mandate_date" => "2020-01-01",
            "status" => "pending",
            "end_to_end_reference" => "E2E-" . uniqid(),
            "due_date" => "2026-02-10",
        ], $overrides));
    }

    public function testTheIndexShowsTheEligibilitySummaryAndPastBatches(): void
    {
        $this->pendingDebit();

        $response = $this->get("/admin/assoc/sepa-batches");

        $response->assertOk();
        $response->assertSee("1");
        $response->assertSee("10,00");
    }

    public function testGeneratingABatchFlipsTheDebitAndRedirects(): void
    {
        $debit = $this->pendingDebit();

        $response = $this->post("/admin/assoc/sepa-batches");

        $response->assertRedirect();
        $this->assertSame("submitted", $debit->fresh()->status);
        $this->assertSame(1, SepaBatch::count());
    }

    public function testGeneratingAgainWithNothingPendingDoesNotCreateAnEmptyBatch(): void
    {
        $this->pendingDebit();
        $this->post("/admin/assoc/sepa-batches");
        $this->assertSame(1, SepaBatch::count());

        $this->post("/admin/assoc/sepa-batches");

        $this->assertSame(1, SepaBatch::count());
    }

    public function testDownloadingABatchStreamsTheGeneratedFile(): void
    {
        $this->pendingDebit();
        $this->post("/admin/assoc/sepa-batches");
        $batch = SepaBatch::firstOrFail();

        $response = $this->get("/admin/assoc/sepa-batches/{$batch->id}/download");

        $response->assertOk();
    }
}
