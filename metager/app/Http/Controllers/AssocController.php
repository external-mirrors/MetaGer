<?php

namespace App\Http\Controllers;

use App\Models\Assoc\Company;
use App\Models\Assoc\Contact;
use App\Models\Assoc\Household;
use Symfony\Component\HttpFoundation\Response;

/**
 * Read-only admin views over the assoc_* tables CiviCrmImporter fills — for
 * verifying the CiviCRM migration, not for editing. Nothing here writes.
 */
class AssocController extends Controller
{
    public function members(): Response
    {
        $contacts = Contact::with("membership")
            ->orderBy("last_name")->orderBy("first_name")
            ->paginate(50, ["*"], "contacts_page")
            ->withQueryString();
        $companies = Company::with("membership")
            ->orderBy("name")
            ->paginate(50, ["*"], "companies_page")
            ->withQueryString();

        return response(view("admin.assoc.members", [
            "title" => "Mitglieder",
            "contacts" => $contacts,
            "companies" => $companies,
        ]));
    }

    public function member(string $type, string $id): Response
    {
        abort_unless(in_array($type, ["contact", "company"], true), 404);

        $payer = match ($type) {
            "contact" => Contact::with(["membership", "debits", "recurContributions"])->findOrFail($id),
            "company" => Company::with(["membership", "debits", "recurContributions"])->findOrFail($id),
        };

        return response(view("admin.assoc.member", [
            "title" => "Mitglied",
            "type" => $type,
            "payer" => $payer,
        ]));
    }

    public function households(): Response
    {
        $households = Household::orderBy("household_name")->paginate(50);

        return response(view("admin.assoc.households", [
            "title" => "Haushalte",
            "households" => $households,
        ]));
    }

    public function household(string $id): Response
    {
        $household = Household::with(["debits", "recurContributions"])->findOrFail($id);

        return response(view("admin.assoc.household", [
            "title" => "Haushalt",
            "household" => $household,
        ]));
    }
}
