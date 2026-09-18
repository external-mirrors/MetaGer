<?php

namespace App\Membership;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Step 3 of the signup sequence: an admin has accepted the application, so
 * the Contact/Company and Membership rows are created in suma-crm. Replaces
 * every `App\Models\Membership\CiviCrm::CREATE_*`/`UPDATE_MEMBERSHIP` call
 * `adminAccept()` used to make.
 *
 * Carries the `payment_reference` {@see MembershipCheckoutIssuer} minted at
 * form time, so suma-crm adopts the mandate the applicant already authorized
 * instead of establishing a second one. suma-crm sends the welcome mail
 * itself from here on — its own `App\Mail\WelcomeMail`, against the
 * Membership row it just created — which is why `adminAccept()` no longer
 * sends metager's.
 *
 * Returns the created membership id, or null when suma-crm is unreachable or
 * rejected the request; the caller reports that to the admin and leaves the
 * application pending, so accepting it again is safe.
 */
final class MembershipIssuer
{
    public function create(array $payload): ?string
    {
        try {
            $response = Http::timeout(15)
                ->withHeaders(["Authorization" => "Bearer " . config("metager.metager.crm.token")])
                ->post(config("metager.metager.crm.internal_url") . "/api/memberships", $payload);
        } catch (\Throwable $e) {
            Log::warning("suma-crm membership intake unreachable: " . $e->getMessage());

            return null;
        }

        if (!$response->successful()) {
            Log::warning("suma-crm membership intake answered " . $response->status() . ": " . $response->body());

            return null;
        }

        $membershipId = $response->json("membership_id");

        return is_string($membershipId) || is_int($membershipId) ? (string) $membershipId : null;
    }
}
