<?php

namespace App\Assoc;

use App\Models\Assoc\Company;
use App\Models\Assoc\Contact;
use App\Models\Assoc\Debit;
use App\Models\Assoc\Household;
use App\Models\Assoc\RecurContribution;
use Illuminate\Support\Facades\DB;

/**
 * Reads de.suma-ev.donation-debit's tables (civicrm_contact/civicrm_debit/
 * civicrm_recur_contribution, on the `civicrm` connection — config/database.php)
 * and upserts them into the assoc_* tables, matched by civicrm_id so a re-run
 * updates rather than duplicates. Never writes to the civicrm connection.
 *
 * Membership import is not implemented yet: CiviCRM's payment_method/status/
 * amount/payment_reference for a membership live in per-install custom-value
 * tables (Beitrag.*, see App\Models\Membership\CiviCrm) whose column names
 * can only be read from civicrm_custom_group/civicrm_custom_field on an actual
 * dump — see importMemberships() below.
 */
class CiviCrmImporter
{
    public function __construct(private readonly string $connection = "civicrm")
    {
    }

    /**
     * @return array{contacts: int, companies: int, households: int, debits: int, recur_contributions: int}
     */
    public function import(): array
    {
        $payers = $this->importContacts();

        return [
            "contacts" => $payers->where("type", "contact")->count(),
            "companies" => $payers->where("type", "company")->count(),
            "households" => $payers->where("type", "household")->count(),
            "debits" => $this->importDebits($payers),
            "recur_contributions" => $this->importRecurContributions($payers),
        ];
    }

    /**
     * @return \Illuminate\Support\Collection<int, array{type: string, id: string}>
     *         keyed by the CiviCRM contact id
     */
    private function importContacts(): \Illuminate\Support\Collection
    {
        $rows = DB::connection($this->connection)
            ->table("civicrm_contact")
            ->leftJoin("civicrm_email", function ($join) {
                $join->on("civicrm_email.contact_id", "=", "civicrm_contact.id")
                    ->where("civicrm_email.is_primary", "=", 1);
            })
            ->leftJoin("civicrm_address", function ($join) {
                $join->on("civicrm_address.contact_id", "=", "civicrm_contact.id")
                    ->where("civicrm_address.is_primary", "=", 1);
            })
            ->where("civicrm_contact.is_deleted", "=", 0)
            ->whereIn("civicrm_contact.contact_type", ["Individual", "Organization", "Household"])
            ->select([
                "civicrm_contact.id",
                "civicrm_contact.contact_type",
                "civicrm_contact.first_name",
                "civicrm_contact.last_name",
                "civicrm_contact.organization_name",
                "civicrm_contact.household_name",
                "civicrm_email.email",
                "civicrm_address.street_address",
                "civicrm_address.postal_code",
                "civicrm_address.city",
            ])
            ->get();

        $payers = collect();

        foreach ($rows as $row) {
            $payer = match ($row->contact_type) {
                "Individual" => Contact::updateOrCreate(
                    ["civicrm_id" => $row->id],
                    [
                        "first_name" => $row->first_name ?? "",
                        "last_name" => $row->last_name ?? "",
                        "email" => $row->email ?? "",
                        "street" => $row->street_address,
                        "postal_code" => $row->postal_code,
                        "city" => $row->city,
                    ],
                ),
                "Organization" => Company::updateOrCreate(
                    ["civicrm_id" => $row->id],
                    [
                        "name" => $row->organization_name ?? "",
                        "street" => $row->street_address,
                        "postal_code" => $row->postal_code,
                        "city" => $row->city,
                    ],
                ),
                "Household" => Household::updateOrCreate(
                    ["civicrm_id" => $row->id],
                    [
                        "household_name" => $row->household_name ?? "",
                        "street" => $row->street_address,
                        "postal_code" => $row->postal_code,
                        "city" => $row->city,
                    ],
                ),
            };

            $payers->put($row->id, [
                "type" => match ($row->contact_type) {
                    "Individual" => "contact",
                    "Organization" => "company",
                    "Household" => "household",
                },
                "id" => $payer->id,
            ]);
        }

        return $payers;
    }

    /**
     * @param \Illuminate\Support\Collection<int, array{type: string, id: string}> $payers
     */
    private function importDebits(\Illuminate\Support\Collection $payers): int
    {
        $rows = DB::connection($this->connection)->table("civicrm_debit")->get();

        $count = 0;
        foreach ($rows as $row) {
            $payer = $payers->get($row->contact_id);
            if ($payer === null) {
                continue;
            }

            Debit::updateOrCreate(
                ["civicrm_id" => $row->id],
                array_merge(
                    $this->payerColumns($payer),
                    [
                        "source" => $row->membership_id !== null ? "membership" : "donation",
                        "iban" => $row->iban,
                        "bic" => $row->bic,
                        "account_holder" => $row->account_holder,
                        "amount" => $row->amount,
                        "mandate" => $row->mandate,
                        "mandate_date" => $row->mandate_date,
                        "status" => $row->status,
                        "end_to_end_reference" => $row->end_to_end_reference,
                        "due_date" => $row->due_date,
                        "reference" => $row->reference,
                    ],
                ),
            );
            $count++;
        }

        return $count;
    }

    /**
     * @param \Illuminate\Support\Collection<int, array{type: string, id: string}> $payers
     */
    private function importRecurContributions(\Illuminate\Support\Collection $payers): int
    {
        $rows = DB::connection($this->connection)->table("civicrm_recur_contribution")->get();

        $count = 0;
        foreach ($rows as $row) {
            $payer = $payers->get($row->contact_id);
            if ($payer === null) {
                continue;
            }

            RecurContribution::updateOrCreate(
                ["civicrm_id" => $row->id],
                array_merge(
                    $this->payerColumns($payer),
                    [
                        // civicrm_recur_contribution has no membership_id — CreateDebits
                        // for memberships writes assoc_debits rows directly from
                        // Beitrag.Zahlungsreferenz, never through this table (see
                        // CRM_DonationDebit_Page_Dauerspenden — this table is donations
                        // only, "Dauerspenden").
                        "source" => "donation",
                        "iban" => $row->iban,
                        "bic" => $row->bic,
                        "account_holder" => $row->account_holder,
                        "amount" => $row->amount,
                        "mandate" => $row->mandate,
                        "mandate_date" => $row->mandate_date,
                        "frequency" => $row->frequency,
                        "next_due_date" => $row->next_debit,
                    ],
                ),
            );
            $count++;
        }

        return $count;
    }

    /**
     * @param array{type: string, id: string} $payer
     */
    private function payerColumns(array $payer): array
    {
        return [
            "contact_id" => $payer["type"] === "contact" ? $payer["id"] : null,
            "company_id" => $payer["type"] === "company" ? $payer["id"] : null,
            "household_id" => $payer["type"] === "household" ? $payer["id"] : null,
        ];
    }

    /**
     * Not implemented: Beitrag.Zahlungsweise/Zahlungsstatus/Zahlungsreferenz/
     * PayPal_Vault/Monatlicher_Mitgliedsbeitrag live in custom-value tables whose
     * column names (custom_NN) are assigned per-install by CiviCRM when the
     * custom fields were created, and can only be read off
     * civicrm_custom_group/civicrm_custom_field on an actual database dump —
     * guessing them here would need rewriting the moment a real dump arrives
     * anyway. See assoc_memberships' migration for the target columns.
     */
    public function importMemberships(): int
    {
        return 0;
    }
}
