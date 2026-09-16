<?php

namespace App\Membership;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Step 1 of the signup sequence: opens a suma-payments checkout session for
 * an application that has just picked a payment method, while the applicant
 * is still on the form and can authorize it.
 *
 * suma-crm mints the payment reference and establishes the mandate; nothing
 * is created in the CRM itself until an admin accepts (see
 * {@see MembershipIssuer}). We keep the returned reference on the
 * application so acceptance can hand it back rather than opening a second
 * mandate for the same member.
 *
 * Shaped after {@see \App\Donations\DonationCheckoutIssuer}, and a null
 * return means the same thing it does there: suma-crm is unreachable or
 * rejected the request. It is never an expected outcome, so every caller
 * must show the applicant a visible error rather than advancing the form to
 * a step whose mandate does not exist.
 */
final class MembershipCheckoutIssuer
{
    /**
     * @return array{payment_reference: string, checkout_url: string}|null
     */
    public function create(array $payload): ?array
    {
        try {
            $response = Http::timeout(10)
                ->withHeaders(["Authorization" => "Bearer " . config("metager.metager.crm.token")])
                ->post(config("metager.metager.crm.url") . "/api/membership-checkouts", $payload);
        } catch (\Throwable $e) {
            Log::warning("suma-crm membership checkout unreachable: " . $e->getMessage());

            return null;
        }

        if (!$response->successful()) {
            Log::warning("suma-crm membership checkout answered " . $response->status() . ": " . $response->body());

            return null;
        }

        $reference = $response->json("payment_reference");
        $checkoutUrl = $response->json("checkout_url");

        if (!is_string($reference) || !is_string($checkoutUrl)) {
            Log::warning("suma-crm membership checkout answered without a reference or checkout url");

            return null;
        }

        return ["payment_reference" => $reference, "checkout_url" => $checkoutUrl];
    }
}
