<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Exceptions;
use Tests\TestCase;
use Vizir\KeycloakWebGuard\Exceptions\KeycloakCallbackException;

/**
 * /callback reached without a login in progress.
 *
 * The admin login's OIDC callback checks the `state` it gets against the one
 * /login put in the session, and throws KeycloakCallbackException('Invalid
 * state') when there is none. Every event of METAGER-19G was the same SSRF
 * scanner — two hosting IPs, `Accept: *` + `/*`, no cookie, a Referer of
 * `/callback?url=http://169.254.169.254/latest/meta-data/iam/…` — so the
 * request carried no state at all, and each one was a 500 and a GlitchTip
 * event. A person gets the same 500 from a stale or bookmarked callback.
 *
 * Neither is our error. It is answered 403 and not reported. Errors Keycloak
 * itself hands back (`?error=…`) are left alone: those can mean the client
 * is misconfigured, which is ours.
 */
class KeycloakCallbackWithoutLoginTest extends TestCase
{
    public function testACallbackWithoutStateIsForbiddenAndNotReported(): void
    {
        Exceptions::fake();

        $this->get('/callback')->assertForbidden();

        Exceptions::assertNotReported(KeycloakCallbackException::class);
    }

    /** The scanner's own shape: a parameter the callback does not read. */
    public function testAProbeWithAForeignParameterIsForbiddenToo(): void
    {
        Exceptions::fake();

        $this->get('/callback?url=' . urlencode('http://169.254.169.254/latest/meta-data/'))
            ->assertForbidden();

        Exceptions::assertNotReported(KeycloakCallbackException::class);
    }

    /** A state that is not the one this session started with. */
    public function testAForgedStateIsForbiddenAndNotReported(): void
    {
        Exceptions::fake();

        $this->get('/callback?state=forged&code=whatever')->assertForbidden();

        Exceptions::assertNotReported(KeycloakCallbackException::class);
    }

    /**
     * Keycloak answering with an error is still reported: `invalid_client` or
     * `unauthorized_client` is a broken deployment, not a stray request.
     */
    public function testAnErrorFromKeycloakIsStillReported(): void
    {
        Exceptions::fake();

        $this->get('/callback?error=unauthorized_client');

        Exceptions::assertReported(KeycloakCallbackException::class);
    }
}
