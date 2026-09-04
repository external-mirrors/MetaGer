<?php

namespace App\Http\Controllers;

use App\Models\Assoc\Company;
use App\Models\Assoc\Contact;
use Symfony\Component\HttpFoundation\Response;

/**
 * Read-only admin views over the assoc_* tables CiviCrmImporter fills — for
 * verifying the CiviCRM migration, not for editing. Nothing here writes.
 */
class AssocController extends Controller
{
    public function members(): Response
    {
        // COALESCE, not plain orderBy("last_name") — a contact with only
        // display_name set (see the assoc_contacts migration) has null
        // first_name/last_name, which would otherwise cluster them at one
        // end of the list regardless of what their name actually is.
        $contacts = Contact::with("membership")
            ->orderByRaw("COALESCE(last_name, display_name)")
            ->orderByRaw("COALESCE(first_name, '')")
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
}
