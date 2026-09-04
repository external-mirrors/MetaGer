<?php

namespace App\Models\Assoc\Concerns;

/**
 * Shared by Contact, Company and Household: all three can be a receipt payer,
 * and all three carry the same nullable donation_receipt_preference override
 * of config('assoc.donation_receipt_default_preference').
 */
trait HasDonationReceiptPreference
{
    public function effectiveDonationReceiptPreference(): string
    {
        return $this->donation_receipt_preference ?? config("assoc.donation_receipt_default_preference");
    }
}
