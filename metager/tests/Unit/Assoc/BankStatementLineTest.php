<?php

namespace Tests\Unit\Assoc;

use App\Models\Assoc\BankStatementLine;
use App\Models\Assoc\Contact;
use App\Models\Assoc\Debit;
use App\Models\Assoc\RecurContribution;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Concerns\UsesInMemorySqlite;
use Tests\TestCase;

class BankStatementLineTest extends TestCase
{
    use DatabaseTransactions;
    use UsesInMemorySqlite;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpInMemorySqlite();
    }

    public function testAnUnmatchedLineCanBeCreated(): void
    {
        $line = BankStatementLine::create([
            "iban" => "DE02120300000000202051",
            "amount" => "10.00",
            "reference" => "Spende, vielen Dank",
            "booked_at" => "2026-01-05",
        ]);

        $reloaded = BankStatementLine::findOrFail($line->id);
        $this->assertNull($reloaded->matched_type);
        $this->assertNull($reloaded->matched());
    }

    public function testMatchedResolvesToTheDebitWhenMatchedTypeIsDebit(): void
    {
        $contact = Contact::create(["first_name" => "Ada", "last_name" => "Lovelace", "email" => "ada@example.com"]);
        $debit = Debit::create([
            "contact_id" => $contact->id,
            "source" => "donation",
            "iban" => "DE02120300000000202051",
            "account_holder" => "Familie Lovelace",
            "amount" => "10.00",
            "mandate" => "S1",
            "mandate_date" => "2026-01-01",
            "end_to_end_reference" => "E2E-1",
            "due_date" => "2026-02-01",
        ]);

        $line = BankStatementLine::create([
            "iban" => "DE02120300000000202051",
            "amount" => "10.00",
            "booked_at" => "2026-02-01",
            "matched_type" => "debit",
            "matched_id" => $debit->id,
            "match_method" => "mandate_reference",
        ]);

        $this->assertTrue($line->matched()->is($debit));
    }

    public function testMatchedResolvesToTheRecurContributionWhenMatchedTypeIsRecurContribution(): void
    {
        $contact = Contact::create(["first_name" => "Ada", "last_name" => "Lovelace", "email" => "ada@example.com"]);
        $recur = RecurContribution::create([
            "contact_id" => $contact->id,
            "source" => "donation",
            "iban" => "DE02120300000000202051",
            "amount" => "10.00",
            "mandate" => "S1",
            "mandate_date" => "2026-01-01",
            "frequency" => "monthly",
        ]);

        $line = BankStatementLine::create([
            "iban" => "DE02120300000000202051",
            "amount" => "10.00",
            "booked_at" => "2026-02-01",
            "matched_type" => "recur_contribution",
            "matched_id" => $recur->id,
            "match_method" => "regex",
        ]);

        $this->assertTrue($line->matched()->is($recur));
    }
}
