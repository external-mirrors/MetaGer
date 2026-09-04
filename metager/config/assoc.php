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

];
