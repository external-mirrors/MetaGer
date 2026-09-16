<?php

namespace App\Membership;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Denial counterpart to {@see MembershipCheckoutIssuer}: an application
 * whose payment method opened a `held` mandate at step 1 (§1.4) but is then
 * rejected here rather than accepted. No suma-crm Membership ever adopted
 * the reference — {@see MembershipIssuer} is what would have — so there is
 * nothing local to clean up there either; this only tells suma-payments to
 * shut the held mandate down (`App\Http\Controllers\Api\MembershipCheckoutController::void()`
 * over there, which forwards to suma-payments' own `/mandates/{reference}/void`).
 *
 * Best-effort like the mailer calls around it in adminDeny() — a failure
 * here must not block the deny itself; it's logged so it can be retried by
 * hand (void is idempotent against a retry, same reasoning as suma-crm's
 * own MandateReleaseClient).
 */
final class MembershipCheckoutVoider
{
    public function void(string $paymentReference): void
    {
        try {
            $response = Http::timeout(10)
                ->withHeaders(["Authorization" => "Bearer " . config("metager.metager.crm.token")])
                ->post(config("metager.metager.crm.url") . "/api/membership-checkouts/{$paymentReference}/void");
        } catch (\Throwable $e) {
            Log::warning("suma-crm membership checkout void unreachable: " . $e->getMessage());

            return;
        }

        if (!$response->successful()) {
            Log::warning("suma-crm membership checkout void answered " . $response->status() . ": " . $response->body());
        }
    }
}
