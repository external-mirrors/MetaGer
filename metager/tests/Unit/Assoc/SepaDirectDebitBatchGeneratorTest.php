<?php

namespace Tests\Unit\Assoc;

use App\Assoc\SepaDirectDebitBatchGenerator;
use App\Models\Assoc\Contact;
use App\Models\Assoc\Debit;
use App\Models\Assoc\SepaBatch;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Tests\Concerns\UsesInMemorySqlite;
use Tests\TestCase;

/**
 * Ported from de.suma-ev.donation-debit's
 * CRM_DonationDebit_Form_ExecuteDebits::generateSepaXML() — see
 * SepaDirectDebitBatchGenerator's own docblock for the two bugs fixed rather
 * than reproduced (empty <PmtInf> groups, a repeated PmtInfId) and the
 * dropped OOFF case.
 */
class SepaDirectDebitBatchGeneratorTest extends TestCase
{
    use DatabaseTransactions;
    use UsesInMemorySqlite;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpInMemorySqlite();
        Storage::fake("local");
        Carbon::setTestNow(Carbon::parse("2026-02-01")); // a Sunday
        config([
            "assoc.sepa_creditor_name" => "Test Verein",
            "assoc.sepa_creditor_iban" => "DE89370400440532013000",
            "assoc.sepa_creditor_bic" => "COBADEFFXXX",
            "assoc.sepa_creditor_id" => "DE98ZZZ09999999999",
        ]);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function contact(): Contact
    {
        return Contact::create(["first_name" => "Ada", "last_name" => "Lovelace", "email" => "ada@example.com"]);
    }

    private function pendingDebit(array $overrides = []): Debit
    {
        return Debit::create(array_merge([
            "contact_id" => $this->contact()->id,
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

    /**
     * @return \DOMDocument
     */
    private function xmlOf(SepaBatch $batch): \DOMDocument
    {
        $doc = new \DOMDocument();
        $doc->loadXML(Storage::disk("local")->get($batch->xml_path));

        return $doc;
    }

    public function testReturnsNullWhenNothingIsEligible(): void
    {
        $batch = (new SepaDirectDebitBatchGenerator())->generate();

        $this->assertNull($batch);
        $this->assertSame(0, SepaBatch::count());
    }

    public function testExcludesBanktransferPendingDebits(): void
    {
        $this->pendingDebit(["iban" => null, "bic" => null, "mandate" => "M-BT"]);

        $batch = (new SepaDirectDebitBatchGenerator())->generate();

        $this->assertNull($batch);
        $this->assertSame(0, SepaBatch::count());
    }

    public function testExcludesAlreadySubmittedExecutedAndFailedDebits(): void
    {
        $this->pendingDebit(["mandate" => "M-SUB", "status" => "submitted", "end_to_end_reference" => "E2E-sub"]);
        $this->pendingDebit(["mandate" => "M-EXE", "status" => "executed", "end_to_end_reference" => "E2E-exe"]);
        $this->pendingDebit(["mandate" => "M-FAIL", "status" => "failed", "end_to_end_reference" => "E2E-fail"]);

        $batch = (new SepaDirectDebitBatchGenerator())->generate();

        $this->assertNull($batch);
    }

    public function testFirstCollectionOnAFreshMandateUsesFrstAndRespectsTheFiveWeekdayMinimum(): void
    {
        // Due 2026-02-02 (Monday) — too soon for a first-ever collection,
        // which needs 5 weekdays' lead from "today" (2026-02-01, a Sunday),
        // i.e. 2026-02-06 (Friday).
        $this->pendingDebit(["due_date" => "2026-02-02"]);

        $batch = (new SepaDirectDebitBatchGenerator())->generate();

        $doc = $this->xmlOf($batch);
        $groups = $doc->getElementsByTagName("PmtInf");
        $this->assertSame(1, $groups->length);
        $group = $groups->item(0);
        $this->assertSame("FRST", $group->getElementsByTagName("SeqTp")->item(0)->textContent);
        $this->assertSame("2026-02-06", $group->getElementsByTagName("ReqdColltnDt")->item(0)->textContent);
    }

    public function testASecondDebitOnTheSameMandateUsesRcurAndTheTwoWeekdayMinimum(): void
    {
        // A prior, already-executed collection under the same mandate — real
        // history, not itself part of this batch.
        $this->pendingDebit(["mandate" => "M1", "status" => "executed", "end_to_end_reference" => "E2E-prior", "due_date" => "2026-01-01"]);
        // Due 2026-02-04 — after the 2-weekday recurring minimum
        // (2026-02-03) but before the 5-weekday first-time one
        // (2026-02-06). If this were wrongly treated as FRST it would get
        // bumped to 2026-02-06; RCUR must leave it at its own due date.
        $this->pendingDebit(["mandate" => "M1", "due_date" => "2026-02-04"]);

        $batch = (new SepaDirectDebitBatchGenerator())->generate();

        $doc = $this->xmlOf($batch);
        $groups = $doc->getElementsByTagName("PmtInf");
        $this->assertSame(1, $groups->length);
        $group = $groups->item(0);
        $this->assertSame("RCUR", $group->getElementsByTagName("SeqTp")->item(0)->textContent);
        $this->assertSame("2026-02-04", $group->getElementsByTagName("ReqdColltnDt")->item(0)->textContent);
    }

    public function testAMandateWhoseOnlyPriorDebitFailedStillCountsAsFirst(): void
    {
        $this->pendingDebit(["mandate" => "M2", "status" => "failed", "end_to_end_reference" => "E2E-bounced", "due_date" => "2026-01-01"]);
        $this->pendingDebit(["mandate" => "M2", "due_date" => "2026-02-10"]);

        $batch = (new SepaDirectDebitBatchGenerator())->generate();

        $doc = $this->xmlOf($batch);
        $this->assertSame("FRST", $doc->getElementsByTagName("SeqTp")->item(0)->textContent);
    }

    public function testTwoDebitsLandingOnTheSameDateAndSequenceTypeShareOnePaymentGroup(): void
    {
        $this->pendingDebit(["mandate" => "M3", "amount" => "10.00", "due_date" => "2026-02-10", "end_to_end_reference" => "E2E-a"]);
        $this->pendingDebit(["mandate" => "M4", "amount" => "15.50", "due_date" => "2026-02-10", "end_to_end_reference" => "E2E-b"]);

        $batch = (new SepaDirectDebitBatchGenerator())->generate();

        $doc = $this->xmlOf($batch);
        $groups = $doc->getElementsByTagName("PmtInf");
        $this->assertSame(1, $groups->length);
        $group = $groups->item(0);
        $this->assertSame("2", $group->getElementsByTagName("NbOfTxs")->item(0)->textContent);
        $this->assertSame("25.50", $group->getElementsByTagName("CtrlSum")->item(0)->textContent);
        $this->assertSame(2, $doc->getElementsByTagName("DrctDbtTxInf")->length);

        $header = $doc->getElementsByTagName("GrpHdr")->item(0);
        $this->assertSame("2", $header->getElementsByTagName("NbOfTxs")->item(0)->textContent);
        $this->assertSame("25.50", $header->getElementsByTagName("CtrlSum")->item(0)->textContent);
    }

    public function testEligibleDebitsAreFlippedToSubmittedAndLinkedToTheBatch(): void
    {
        $debit = $this->pendingDebit();

        $batch = (new SepaDirectDebitBatchGenerator())->generate();

        $debit->refresh();
        $this->assertSame("submitted", $debit->status);
        $this->assertSame($batch->id, $debit->sepa_batch_id);
        $this->assertSame(1, $batch->debit_count);
        $this->assertSame("10.00", $batch->total_amount);
        $this->assertTrue(Storage::disk("local")->exists($batch->xml_path));
    }

    public function testThrowsWhenCreditorConfigIsMissing(): void
    {
        $this->pendingDebit();
        config(["assoc.sepa_creditor_iban" => null]);

        $this->expectException(\RuntimeException::class);

        (new SepaDirectDebitBatchGenerator())->generate();
    }
}
