<?php

namespace App\Http\Middleware;

use Vizir\KeycloakWebGuard\Middleware\KeycloakAuthenticated;

/**
 * Routes are cached in production (see CLAUDE.md), so anything that decides
 * per-environment while routes/session.php runs gets baked into the cache and
 * stops varying afterwards. Deciding here instead, per request, keeps the
 * route table itself a constant — this must stay attached to the admin group
 * unconditionally.
 */
class AdminAuthenticate extends KeycloakAuthenticated
{
    protected function authenticate($request, array $guards)
    {
        if (!app()->environment(["development", "production"])) {
            return;
        }

        parent::authenticate($request, $guards);
    }
}
