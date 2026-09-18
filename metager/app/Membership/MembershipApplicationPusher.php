<?php

namespace App\Membership;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * The push into suma-crm's own admin review queue, once a new (non-update)
 * application is actually ready for an admin to decide on — contact
 * present, amount/interval set, payment_method/payment_reference already
 * obtained via {@see MembershipCheckoutIssuer}, and reduction resolved if
 * reduced-fee (docs/civicrm-replacement.md, "Membership application review
 * moves to suma-crm"). Called from both `submitMembershipForm()`'s step 4
 * (the common case) and `adminMembershipReductionAccept()` (the minority
 * case where reduction resolves after step 4 already did).
 *
 * Unlike {@see MembershipCheckoutIssuer}, a failure here is not fatal to
 * the applicant's flow — the mandate already exists and they already have
 * their checkout URL. The caller leaves the local `MembershipApplication`
 * row in place on failure rather than losing the application; it falls
 * back to the legacy `adminAccept()`/`adminDeny()` review path in the
 * meantime, exactly as it worked before this queue existed.
 */
final class MembershipApplicationPusher
{
    public function create(array $payload): bool
    {
        try {
            $response = Http::timeout(10)
                ->withHeaders(["Authorization" => "Bearer " . config("metager.metager.crm.token")])
                ->post(config("metager.metager.crm.internal_url") . "/api/membership-applications", $payload);
        } catch (\Throwable $e) {
            Log::warning("suma-crm membership application push unreachable: " . $e->getMessage());

            return false;
        }

        if (!$response->successful()) {
            Log::warning("suma-crm membership application push answered " . $response->status() . ": " . $response->body());

            return false;
        }

        return true;
    }
}
