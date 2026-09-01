<?php

namespace App\Http\Middleware;

use App\Support\AppHosts;
use Illuminate\Http\Middleware\TrustHosts as Middleware;

/**
 * Which `Host` headers this application will answer.
 *
 * The default Laravel middleware trusts `app.url` and its subdomains — one
 * host, when this same deployment serves metager.de, metager.org, metager3.de,
 * every `*.review.metager.de` preview and two `.onion` addresses. The full set
 * is {@see \App\Support\AppHosts}.
 *
 * It is named directly in `bootstrap/app.php`'s global middleware list rather
 * than enabled through `$middleware->trustHosts()`: that file replaces
 * Laravel's whole default global stack with its own `use([...])`, and the flag
 * `trustHosts()` sets is only read while building the default stack. So the
 * class goes in the list, and `hosts()` below — which the base `handle()`
 * calls — is where the pattern set actually comes from.
 */
class TrustHosts extends Middleware
{
    /**
     * @return array<int, string|null>
     */
    public function hosts(): array
    {
        return AppHosts::trustedPatterns();
    }
}
