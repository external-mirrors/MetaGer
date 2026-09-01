<?php

namespace Tests\Feature;

use App\Authentication\CookieSupport;
use App\Http\SettingsCarry;
use Illuminate\Http\Request;
use Tests\TestCase;

/**
 * Unit-level tests for `App\Http\SettingsCarry`, run as a Feature test:
 * `boot()` calls `SearchSettings::isValidSetting()`, whose `available_foki`
 * is only populated once the full `SearchSettings::boot()` has run
 * (`SearchSettingsProvider`) — a plain PHPUnit TestCase (as
 * `CookieSupportTest` uses for the pure, dependency-free `CookieSupport`
 * methods) cannot supply that without reimplementing the container.
 *
 * Swaps the container's `request` binding directly rather than dispatching a
 * real HTTP request, mirroring `CookieCarryingUrlGeneratorTest`.
 */
class SettingsCarryTest extends TestCase
{
    private function asRequest(Request $request): SettingsCarry
    {
        $this->app->instance('request', $request);
        // Force a fresh SettingsCarry too: the container may have already
        // resolved and cached the singleton against a prior binding in this
        // process (irrelevant in a real request-per-process FPM worker, but
        // this test process reuses one container across cases).
        $this->app->forgetInstance(SettingsCarry::class);
        return $this->app->make(SettingsCarry::class);
    }

    public function testASettingWithNoMatchingCookieIsCarried(): void
    {
        $carry = $this->asRequest(Request::create("/?dark_mode=dark"));

        $this->assertSame(["dark_mode" => "dark"], $carry->all());
    }

    public function testASettingWithAMatchingCookieIsNotCarried(): void
    {
        $request = Request::create("/?dark_mode=dark");
        $request->cookies->set("dark_mode", "dark");

        $this->assertSame([], $this->asRequest($request)->all());
    }

    /**
     * Independence is per name, not one request-wide switch: a cookie
     * covering one setting must not suppress carrying of another that has
     * none.
     */
    public function testEachSettingIsGatedIndependently(): void
    {
        $request = Request::create("/?dark_mode=dark&new_tab=on");
        $request->cookies->set("new_tab", "on");

        $this->assertSame(["dark_mode" => "dark"], $this->asRequest($request)->all());
    }

    public function testAnUnrecognisedQueryParameterIsIgnored(): void
    {
        $carry = $this->asRequest(Request::create("/?eingabe=cats&token=abc"));

        $this->assertSame([], $carry->all());
    }

    /**
     * `key` is itself one of SettingsSchema::globalSettingKeys(), so
     * isValidSetting() would otherwise accept it — it has its own, separate
     * carrying mechanism (CookieSupport::keyMissingCookie()) and must not be
     * double-carried through this one too.
     */
    public function testTheKeyParameterIsNeverCarriedHere(): void
    {
        $carry = $this->asRequest(Request::create("/?key=aaaaaaaa-bbbb-cccc-dddd-eeeeee123456"));

        $this->assertSame([], $carry->all());
    }

    public function testTheOneHopMarkerIsNeverCarried(): void
    {
        $carry = $this->asRequest(Request::create("/?" . CookieSupport::MARKER . "=1"));

        $this->assertSame([], $carry->all());
    }

    public function testSetAddsASettingRegardlessOfTheRequest(): void
    {
        $carry = $this->asRequest(Request::create("/"));

        $carry->set("dark_mode", "dark");

        $this->assertSame(["dark_mode" => "dark"], $carry->all());
    }

    public function testForgetRemovesABootedSetting(): void
    {
        $carry = $this->asRequest(Request::create("/?dark_mode=dark"));

        $carry->forget("dark_mode");

        $this->assertSame([], $carry->all());
    }

    public function testForgetRemovesASetSetting(): void
    {
        $carry = $this->asRequest(Request::create("/"));
        $carry->set("dark_mode", "dark");

        $carry->forget("dark_mode");

        $this->assertSame([], $carry->all());
    }

    /**
     * `set()`/`forget()` must not be lost by a later `all()` re-triggering
     * `boot()` and rebuilding the map from scratch.
     */
    public function testBootOnlyRunsOnceEvenAfterMutation(): void
    {
        $carry = $this->asRequest(Request::create("/?dark_mode=dark"));

        $carry->forget("dark_mode");
        $carry->set("new_tab", "on");

        $this->assertSame(["new_tab" => "on"], $carry->all());
    }
}
