<?php

namespace App\Models\Assoc\Concerns;

/**
 * Shared by Contact and Company: both can be a receipt payer, and both carry
 * the same nullable donation_receipt_preference override of
 * config('assoc.donation_receipt_default_preference').
 */
trait HasDonationReceiptPreference
{
    public function effectiveDonationReceiptPreference(): string
    {
        return $this->donation_receipt_preference ?? config("assoc.donation_receipt_default_preference");
    }
}
