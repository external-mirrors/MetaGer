<?php

namespace App\Donations;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Hands a donation off to suma-crm, which finds/creates the donor Contact and
 * establishes the mandate/checkout session with suma-payments on our behalf,
 * returning a hosted checkout_url to redirect the donor to.
 *
 * Unlike {@see \App\Authentication\ChargeOrderIssuer}, a null return here is
 * never an expected outcome (there is no "already has 3 open orders" case for
 * a donation) — it only ever means suma-crm is unreachable or rejected the
 * request, so every caller must branch on it and show the donor a visible
 * error rather than silently stranding them mid-payment.
 */
final class DonationCheckoutIssuer
{
    /**
     * @param  array<string, mixed>  $payload
     */
    public function create(array $payload): ?string
    {
        try {
            $response = Http::timeout(10)
                ->withHeaders(["Authorization" => "Bearer " . config("metager.metager.crm.token")])
                ->post(config("metager.metager.crm.url") . "/api/donations", $payload);
        } catch (\Throwable $e) {
            Log::warning("suma-crm donation checkout unreachable: " . $e->getMessage());

            return null;
        }

        if (!$response->successful()) {
            Log::warning("suma-crm donation checkout answered " . $response->status());

            return null;
        }

        return $response->json("checkout_url");
    }
}
