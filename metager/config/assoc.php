<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Donation receipt default preference
    |--------------------------------------------------------------------------
    |
    | Fallback used by Contact/Company::effectiveDonationReceiptPreference()
    | when the payer has no donation_receipt_preference of their own — i.e. no
    | migrated CiviCRM preference (see CiviCrmImporter::importContacts()) and
    | nobody has changed it since. One of "never", "immediate" or "annual".
    |
    | Default is "never": most donors never ask for a receipt, and generating
    | one unasked is a bigger mistake (an unwanted PDF, a wrong assumption
    | about what someone wants mailed to them) than not generating one someone
    | later requests — that case is DonationReceiptGenerator::generateForPayer(),
    | available on demand regardless of this setting. CiviCRM's own
    | Spendenbescheinigung.php::shouldCreateReceipt() also generated nothing
    | when neither the contribution nor the contact had a preference set, so
    | this default matches existing practice rather than changing it.
    |
    */

    "donation_receipt_default_preference" => env("ASSOC_DONATION_RECEIPT_DEFAULT_PREFERENCE", "never"),

    /*
    |--------------------------------------------------------------------------
    | Donation receipt signee
    |--------------------------------------------------------------------------
    |
    | The board member's name printed under the signature line, and a local
    | path to a scanned signature image (jpg/png) to embed. Both env-only,
    | deliberately not committed — CRM/Bescheinigungen/Form/DownloadReceipts.php
    | hardcoded two board members' names and JPEG signature scans straight
    | into extension source, which put a personal signature image in version
    | control. Leave the path unset to print no signature image and sign by
    | hand instead.
    |
    */

    "donation_receipt_signee_name" => env("ASSOC_DONATION_RECEIPT_SIGNEE_NAME"),
    "donation_receipt_signature_path" => env("ASSOC_DONATION_RECEIPT_SIGNATURE_PATH"),

    /*
    |--------------------------------------------------------------------------
    | SEPA creditor identity
    |--------------------------------------------------------------------------
    |
    | Used by SepaDirectDebitBatchGenerator to fill in every pain.008.001.02
    | collection batch's <Cdtr>/<CdtrAcct>/<CdtrAgt>/<CdtrSchmeId> blocks —
    | the association's own name, account and Gläubiger-ID, real production
    | values nobody has supplied yet. Env-only, deliberately not committed,
    | same reasoning as the donation-receipt signee above and config/sumas.json:
    | a fresh checkout has none, and whoever actually runs a batch against the
    | real accounts sources and sets these.
    |
    | The name must be SEPA-charset-safe (Latin letters, digits and a small
    | punctuation set — no umlauts or ß): the legacy extension's hardcoded
    | value spelled it "fuer", not "für", for exactly this reason. Nothing
    | here transliterates it automatically; get it right when setting the
    | env var.
    |
    | A missing value throws from the generator rather than silently
    | producing a batch with blank creditor fields — a config/ops problem,
    | left uncaught to surface as a 500, unlike DonationReceiptGenerator's
    | caught RuntimeExceptions, which are per-debit business-rule rejections.
    |
    */

    "sepa_creditor_name" => env("ASSOC_SEPA_CREDITOR_NAME"),
    "sepa_creditor_iban" => env("ASSOC_SEPA_CREDITOR_IBAN"),
    "sepa_creditor_bic" => env("ASSOC_SEPA_CREDITOR_BIC"),
    "sepa_creditor_id" => env("ASSOC_SEPA_CREDITOR_ID"),

    /*
    |--------------------------------------------------------------------------
    | Default correspondence locale
    |--------------------------------------------------------------------------
    |
    | Last resort for Membership::resolvedLocale() when neither the payer
    | (assoc_contacts.locale) nor the membership itself (assoc_memberships.
    | locale, imported from CiviCRM's Beitrag.Locale) has one set. suma-ev is
    | a German association; unlike app.locale this is never negotiated from a
    | browser, since nothing here renders in response to a request.
    |
    */

    "default_locale" => env("ASSOC_DEFAULT_LOCALE", "de"),

];
