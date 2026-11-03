<?php

namespace App\Models\Authorization;

/**
 * Who pays for the searches /api/blocklist/check runs: MetaGer itself.
 *
 * Those searches are made on suma-crm's behalf, behind CrmAuthorization, by
 * nobody with a key. Without this they would run as an anonymous visitor and
 * every paid engine would be switched off with PAYMENT_REQUIRED, which is
 * exactly the set of engines a removal request is usually about. The cost is
 * the operator's, so no payment is made; the endpoint's throttle is what
 * bounds it (App\Http\Controllers\BlocklistCheckController).
 */
class ListingCheckAuthorization extends Authorization
{
    public function __construct()
    {
        parent::__construct();
        $this->availableTokens = PHP_INT_MAX;
    }

    public function getToken()
    {
        return "";
    }

    public function makePayment(float $cost): bool
    {
        return true;
    }
}
