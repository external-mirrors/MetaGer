<?php

namespace Tests\Feature;

use App\Localization\LocaleContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

/**
 * A signed URL generated under a locale prefix still validates when it comes
 * back.
 *
 * This is the one interaction the prefix strip could have broken without
 * anything else noticing. `URL::signedRoute()` signs what `route()` produces,
 * and `route()` produces the prefixed URL — but `ResolveLocale` removes the
 * prefix before the controller runs, so `$request->url()` is no longer the
 * string that was signed, and `$request->hasValidSignature()` would answer
 * `false` for a URL we generated ourselves.
 *
 * The symptom would have been a 404 on the donation thank-you page for every
 * user not browsing in their domain's default language — a page reached once,
 * after paying, by people who would have no reason to report it.
 *
 * `App\Localization::hasValidSignature()` is the fix, and the two call sites
 * that check signatures (`DonationController::donationFinished`,
 * `StartpageController::loadStartPage`) go through it.
 */
class SignedUrlUnderLocalePrefixTest extends TestCase
{
    /** Generate URLs as though this request were being served under /es-ES. */
    private function underSpanishPrefix(): void
    {
        $this->app->instance(
            LocaleContext::class,
            LocaleContext::resolve(Request::create(rtrim(config("app.url"), "/") . "/es-ES/"))
        );
    }

    public function testASignedRouteIsGeneratedWithTheLocalePrefix(): void
    {
        $this->underSpanishPrefix();

        $this->assertStringContainsString(
            "/es-ES/spende/",
            URL::signedRoute("thankyou", $this->donation()),
            "The signature would then cover a URL nobody is ever sent to."
        );
    }

    public function testTheSignatureIsAcceptedWhenTheUrlComesBack(): void
    {
        $this->underSpanishPrefix();
        $signed = URL::signedRoute("thankyou", $this->donation());

        $this->get($signed, ["Sec-Fetch-Mode" => "navigate"])->assertOk();
    }

    /** And the check is still a check: a tampered URL is refused. */
    public function testATamperedSignedUrlIsStillRefused(): void
    {
        $this->underSpanishPrefix();
        $signed = URL::signedRoute("thankyou", $this->donation());

        $this->get(str_replace("/spende/5/", "/spende/500/", $signed), ["Sec-Fetch-Mode" => "navigate"])
            ->assertNotFound();
    }

    /** @return array<string, mixed> */
    private function donation(): array
    {
        return [
            "amount" => 5,
            "interval" => "once",
            "funding_source" => "banktransfer",
            "timestamp" => time(),
        ];
    }
}
