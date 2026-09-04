<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Donation receipt default preference
    |--------------------------------------------------------------------------
    |
    | Fallback used by Contact/Company/Household::effectiveDonationReceiptPreference()
    | when the payer has no donation_receipt_preference of their own — i.e. no
    | migrated CiviCRM preference (see CiviCrmImporter::importContacts()) and
    | nobody has changed it since. One of "immediate" or "annual".
    |
    | CiviCRM's own Spendenbescheinigung.php::shouldCreateReceipt() generated no
    | receipt at all when neither the contribution nor the contact had a
    | preference set. That was never a deliberate default, just what happens
    | when nothing was configured — this setting replaces it with an actual,
    | changeable decision.
    |
    */

    "donation_receipt_default_preference" => env("ASSOC_DONATION_RECEIPT_DEFAULT_PREFERENCE", "annual"),

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

];
