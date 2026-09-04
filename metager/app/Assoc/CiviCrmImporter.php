<?php

namespace App\Assoc;

use App\Models\Assoc\Company;
use App\Models\Assoc\Contact;
use App\Models\Assoc\Debit;
use App\Models\Assoc\Household;
use App\Models\Assoc\Membership;
use App\Models\Assoc\RecurContribution;
use Illuminate\Support\Facades\DB;

/**
 * Reads de.suma-ev.donation-debit's tables (civicrm_contact/civicrm_debit/
 * civicrm_recur_contribution/civicrm_membership plus its Beitrag/Mastodon/
 * MetaGer_Key custom-value tables, on the `civicrm` connection —
 * config/database.php) and upserts them into the assoc_* tables, matched by
 * civicrm_id so a re-run updates rather than duplicates. Never writes to the
 * civicrm connection.
 */
class CiviCrmImporter
{
    public function __construct(private readonly string $connection = "civicrm")
    {
    }

    /**
     * @return array{contacts: int, companies: int, households: int, memberships: int, debits: int, recur_contributions: int}
     */
    public function import(): array
    {
        $payers = $this->importContacts();

        return [
            "contacts" => $payers->where("type", "contact")->count(),
            "companies" => $payers->where("type", "company")->count(),
            "households" => $payers->where("type", "household")->count(),
            "memberships" => $this->importMemberships($payers),
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
     * @param \Illuminate\Support\Collection<int, array{type: string, id: string}> $payers
     */
    private function importMemberships(\Illuminate\Support\Collection $payers): int
    {
        $rows = DB::connection($this->connection)
            ->table("civicrm_membership")
            ->join("civicrm_membership_type", "civicrm_membership_type.id", "=", "civicrm_membership.membership_type_id")
            ->leftJoin("civicrm_value_beitrag_8", "civicrm_value_beitrag_8.entity_id", "=", "civicrm_membership.id")
            ->leftJoin("civicrm_value_mastodon_10", "civicrm_value_mastodon_10.entity_id", "=", "civicrm_membership.id")
            ->leftJoin("civicrm_value_metager_key_14", "civicrm_value_metager_key_14.entity_id", "=", "civicrm_membership.id")
            ->select([
                "civicrm_membership.id",
                "civicrm_membership.contact_id",
                "civicrm_membership.join_date",
                "civicrm_membership.start_date",
                "civicrm_membership.end_date",
                "civicrm_membership_type.name as type_name",
                "civicrm_membership_type.duration_unit",
                "civicrm_membership_type.duration_interval",
                "civicrm_value_beitrag_8.monatlicher_mitgliedsbeitrag_29 as amount",
                "civicrm_value_beitrag_8.zahlungsweise_32",
                "civicrm_value_beitrag_8.zahlungsreferenz_36 as payment_reference",
                "civicrm_value_beitrag_8.zahlungsstatus_37",
                "civicrm_value_beitrag_8.erm_igt_bis_49 as reduced_until",
                "civicrm_value_beitrag_8.paypal_vault_50 as paypal_vault_id",
                "civicrm_value_beitrag_8.locale_52 as locale",
                "civicrm_value_mastodon_10.mastodon_id_42 as mastodon_id",
                "civicrm_value_metager_key_14.key_46 as key_id",
            ])
            ->get();

        $count = 0;
        foreach ($rows as $row) {
            $payer = $payers->get($row->contact_id);
            if ($payer === null || !in_array($payer["type"], ["contact", "company"], true)) {
                // Household memberships (4 in the production dump) and memberships
                // belonging to a contact importContacts() skipped (deleted, or an
                // unsupported contact type) have no home here — assoc_memberships
                // only relates to assoc_contacts/assoc_companies.
                continue;
            }

            $isExempt = $row->duration_unit === "lifetime";
            $paymentMethod = match (true) {
                $isExempt => "exempt",
                $row->zahlungsweise_32 === "1" => "directdebit",
                $row->zahlungsweise_32 === "2" => "banktransfer",
                $row->zahlungsweise_32 === "paypal" => "paypal",
                $row->zahlungsweise_32 === "card" => "card",
                default => null,
            };
            if ($paymentMethod === null) {
                // 38 rows in the production dump: long-expired (Status "Expired",
                // end_date in 2020/2021) memberships that predate Zahlungsweise
                // being populated at all. Not enough evidence to safely guess a
                // payment method for a real person — skip rather than fabricate
                // one.
                continue;
            }

            Membership::updateOrCreate(
                ["civicrm_id" => $row->id],
                array_merge(
                    $this->payerColumns($payer),
                    [
                        "membership_type" => $payer["type"] === "company" ? "company" : "person",
                        "reduced" => str_contains($row->type_name, "ermäßigt"),
                        "interval" => $this->intervalFor($row->duration_unit, $row->duration_interval),
                        "amount" => $row->amount ?? "0.00",
                        "payment_method" => $paymentMethod,
                        "payment_reference" => $row->payment_reference ?: null,
                        "paypal_vault_id" => $row->paypal_vault_id ?: null,
                        "join_date" => $row->join_date,
                        // Only Ausgetreten/Verstorben are an admin fact about
                        // standing — every other Zahlungsstatus value (Okay,
                        // Warte_auf_Lastschrifteingang, the two Erinnerung stages,
                        // Unterbrochen, Eingetreten, or never set) describes
                        // billing progress, not membership standing, and is left
                        // for a later derived-collection-stage phase to surface.
                        "standing" => match ($row->zahlungsstatus_37) {
                            "6" => "terminated",
                            "7" => "deceased",
                            default => "active",
                        },
                        "start_date" => $row->start_date,
                        "end_date" => $row->end_date,
                        "reduced_until" => $row->reduced_until,
                        "locale" => $row->locale ?: null,
                        "key_id" => $row->key_id ?: null,
                        "mastodon_id" => $row->mastodon_id ?: null,
                    ],
                ),
            );
            $count++;
        }

        return $count;
    }

    private function intervalFor(string $durationUnit, ?int $durationInterval): string
    {
        return match (true) {
            // Ehrenmitglied/Gegenseitigkeit (duration_unit=lifetime, 9 rows in the
            // production dump) don't have a real billing interval —
            // payment_method=exempt is what actually matters for them; "annual"
            // here is an arbitrary placeholder that nothing ever acts on.
            $durationUnit === "lifetime" => "annual",
            $durationUnit === "year" => "annual",
            $durationUnit === "month" && $durationInterval === 6 => "six-monthly",
            $durationUnit === "month" && $durationInterval === 3 => "quarterly",
            $durationUnit === "month" && $durationInterval === 1 => "monthly",
        };
    }
}
