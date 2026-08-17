<?php

namespace Tests\Feature\Search;

use App\Models\Authorization\SuggestionDebtAuthorization;
use Illuminate\Support\Facades\Redis;
use Tests\Concerns\FakesSearchEngines;
use Tests\TestCase;

/**
 * A search survives having something to pay for on the way in.
 *
 * AuthenticationValidation runs before the search controller and gates the
 * whole result page on two calls:
 *
 *     if ($user->authorize($suma_cost + $suggestion_debt) && $user->makePayment($suggestion_debt))
 *
 * — anything falsy there is a redirect to the startpage, not an error. So a
 * payment that quietly fails does not look like a payment problem. It looks
 * like the search page has stopped existing.
 *
 * The suggestion debt is what makes that reachable in practice. Address-bar
 * suggestions are served before anyone has paid for them and recorded as debt
 * against the client, which the next real search settles. The debt is stored in
 * Redis with a two-day expiry, so once it exists it is there for every
 * subsequent search until it is paid.
 *
 * This test exists because that combination hid a hole in the test harness for
 * a long time. Every other search test runs with a debt of exactly zero, and
 * makePayment(0.0) returns true without asking anyone — so the discharge call
 * was never actually exercised, and the harness got away with faking it as an
 * empty 200. KeyUser::makePayment reads `charge` out of that response and
 * returns false when it is missing, which meant every search redirected the
 * moment a real amount was owed. Locally: green. In CI, where the debt is
 * recorded for real: 73 failures, all of them "expected 200, got 302".
 */
class SearchAuthorizationTest extends TestCase
{
    use FakesSearchEngines;

    protected function tearDown(): void
    {
        // The debt outlives the test by two days otherwise, and every later
        // search in the same suite would have to pay it.
        Redis::connection(config("cache.stores.redis.connection"))
            ->del(SuggestionDebtAuthorization::GET_CACHE_KEY());
        $this->forgetSearchUserClaims();

        parent::tearDown();
    }

    /**
     * The case that was broken: something is owed, so the search actually has
     * to discharge it before it may render.
     */
    public function testASearchIsServedWhenASuggestionDebtHasToBePaidFirst(): void
    {
        $this->actingAsSearchUser();
        $this->fakeEngineResponses([
            "brave" => $this->engineFixture("brave-web.json"),
        ]);

        SuggestionDebtAuthorization::ADD_DEBT(0.5);
        $this->assertGreaterThan(
            0.0,
            SuggestionDebtAuthorization::GET_DEBT(),
            "Nothing was owed, so this test would pass without exercising a payment at all."
        );

        $response = $this->get("/meta/meta.ger3?eingabe=kaffee&focus=web&out=json");

        $this->assertSame(200, $response->getStatusCode(), $this->whyRefused($response));
        $this->assertNotEmpty(
            $response->json("results"),
            "The search was authorized but returned nothing."
        );
    }

    /**
     * Every refusal on this path is a 302 with an empty body, so a failure here
     * otherwise says only "expected 200, got 302" — the same message whichever
     * of the four possible reasons fired, and with none of the numbers that
     * decided it. The redirect target alone separates the authorization refusal
     * in the middleware from the controller's "no engines are enabled" one.
     */
    private function whyRefused(\Illuminate\Testing\TestResponse $response): string
    {
        $user = \Auth::guard("key")->user();
        $engines = app(\App\Models\Configuration\Searchengines::class);
        $claims = Redis::connection(config("cache.stores.redis.connection"))
            ->hgetall("keyserver:claims:test-key");

        $why = [];
        foreach ($engines->sumas as $name => $suma) {
            $why[] = $name . ": " . ($suma->configuration->disabled
                ? implode("+", array_map(fn($r) => $r->name ?? (string) $r, $suma->configuration->disabledReasons))
                : "enabled");
        }

        return sprintf(
            "The search was refused.\n"
                . "  redirected to : %s\n"
                . "  key user      : %s\n"
                . "  charge        : %s\n"
                . "  search cost   : %s (raw %s)\n"
                . "  suggestion debt: %s\n"
                . "  claims on key : %s\n"
                . "  engines enabled: %d\n"
                . "  locale        : regional=%s language=%s app.locale=%s app.url=%s\n"
                . "  engines       : %s",
            $response->headers->get("Location") ?? "(no Location header)",
            $user === null ? "none — the key guard has no user, so the legacy path ran" : $user->key,
            var_export($user?->key_data["charge"] ?? null, true),
            var_export($engines->getSearchCost(), true),
            var_export($engines->getRawSearchCost(), true),
            var_export(SuggestionDebtAuthorization::GET_DEBT(), true),
            json_encode($claims),
            count($engines->getEnabledSearchengines() ?: []),
            \LaravelLocalization::getCurrentLocaleRegional(),
            \App\Localization::getLanguage(),
            config("app.locale"),
            config("app.url"),
            implode(", ", $why)
        );
    }

    /**
     * CHARACTERIZATION TEST — this pins a bug, not a behaviour worth keeping.
     *
     * A key user pays the suggestion debt and the debt is not written down. It
     * is charged again on the next search, and the one after that, until the
     * two-day expiry drops it.
     *
     * AuthenticationValidation has two authorization branches. The legacy one
     * settles up:
     *
     *     if ($authorized === true) {
     *         $this->clearSuggestionDebt($suggestion_debt);   // ADD_DEBT(-$debt)
     *
     * The KeyUser branch above it discharges the same amount through
     * makePayment() and returns straight into the request, with no matching
     * call. clearSuggestionDebt is referenced exactly once in the codebase, from
     * the branch that is on its way out — so the users being overcharged are the
     * ones already migrated to the guard.
     *
     * Not fixed here: this is real money moving, the amounts and the intended
     * settlement point are a product decision, and it has nothing to do with the
     * CI failure this commit is about. When it is fixed on purpose, this test
     * fails and should be replaced with testPayingTheSuggestionDebtSettlesIt,
     * asserting 0.0.
     */
    public function testPayingTheSuggestionDebtDoesNotSettleItForAKeyUser(): void
    {
        $this->actingAsSearchUser();
        $this->fakeEngineResponses([
            "brave" => $this->engineFixture("brave-web.json"),
        ]);

        SuggestionDebtAuthorization::ADD_DEBT(0.5);

        $this->get("/meta/meta.ger3?eingabe=kaffee&focus=web&out=json")->assertOk();

        $this->assertSame(
            0.5,
            SuggestionDebtAuthorization::GET_DEBT(),
            "The suggestion debt was settled after all. That is the correct behaviour — replace this "
                . "characterization test with one asserting 0.0."
        );
    }
}
