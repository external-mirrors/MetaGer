<?php

namespace Tests\Feature\Search;

use App\Models\Authorization\SuggestionDebtAuthorization;
use Illuminate\Support\Facades\Http;
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
     * Paying the debt settles it, so the next search does not pay it again.
     *
     * This was a characterization test until the bug behind it was fixed. The
     * KeyUser branch of AuthenticationValidation discharged the debt through
     * makePayment() and returned straight into the request, while only the
     * legacy branch called clearSuggestionDebt() — so a key user paid the same
     * outstanding debt on every search until its two-day expiry dropped it, and
     * the users affected were the ones already migrated to the guard.
     *
     * The settling had to be written out on that branch rather than reusing
     * clearSuggestionDebt(), because that method pays the debt itself and it is
     * already paid by the time the branch gets there.
     */
    public function testPayingTheSuggestionDebtSettlesIt(): void
    {
        $this->actingAsSearchUser();
        $this->fakeEngineResponses([
            "brave" => $this->engineFixture("brave-web.json"),
        ]);

        SuggestionDebtAuthorization::ADD_DEBT(0.5);

        $this->get("/meta/meta.ger3?eingabe=kaffee&focus=web&out=json")->assertOk();

        $this->assertSame(
            0.0,
            SuggestionDebtAuthorization::GET_DEBT(),
            "The debt outlived the search that paid it, so the next search pays it over again."
        );
    }

    /**
     * A search discharges once, however many engines it paid for.
     *
     * makePayment() POSTs to the keyserver synchronously, and the payment loop
     * runs while the user is waiting for the result page — so paying engine by
     * engine put a network round trip on the result path for every paid engine
     * in the fokus. Foki differ in how many that is, which made the tax
     * invisible on the fokus anyone happened to be looking at.
     *
     * The keyserver discharges an amount rather than an engine, so one call for
     * the sum is the same money. This asserts the count, because the amount
     * being right is not the part that regresses.
     */
    public function testAllTheEnginesOfASearchArePaidForInOneCall(): void
    {
        $this->actingAsSearchUser();
        $this->fakeEngineResponses([
            "brave" => $this->engineFixture("brave-web.json"),
            "serper_web" => $this->engineFixture("serper-web.json"),
        ]);

        $this->get("/meta/meta.ger3?eingabe=kaffee&focus=web&out=json")->assertOk();

        $engines = app(\App\Models\Configuration\Searchengines::class);
        $paidFor = array_filter(
            $engines->getEnabledSearchengines() ?: [],
            fn($engine) => !$engine->cached && $engine->configuration->cost > 0
        );

        $this->assertGreaterThan(
            1,
            count($paidFor),
            "Only one engine was paid for, so this cannot tell one call per engine from one per search."
        );

        $discharges = [];
        Http::assertSent(function ($request) use (&$discharges) {
            if (str_contains($request->url(), "/discharge")) {
                $discharges[] = $request->data()["amount"] ?? null;
            }

            return true;
        });

        $this->assertCount(
            1,
            $discharges,
            sprintf(
                "%d engines were paid for in %d keyserver calls. Each one is a synchronous POST on the "
                    . "result path, made while the user waits.",
                count($paidFor),
                count($discharges)
            )
        );
        $this->assertEqualsWithDelta(
            array_sum(array_map(fn($engine) => $engine->configuration->cost, $paidFor)),
            $discharges[0],
            0.0001,
            "Batching the discharges changed what the search costs."
        );
    }

    /**
     * The debt is settled once, not written off twice.
     *
     * The failure this guards against is the obvious way to fix the bug above:
     * calling clearSuggestionDebt() on the KeyUser branch. That method pays the
     * debt itself, so on a branch that has already paid it the key is charged
     * twice and the debt goes negative — which then reads as credit on the next
     * search.
     */
    public function testSettlingTheDebtDoesNotDriveItNegative(): void
    {
        $this->actingAsSearchUser();
        $this->fakeEngineResponses([
            "brave" => $this->engineFixture("brave-web.json"),
        ]);

        SuggestionDebtAuthorization::ADD_DEBT(0.5);

        $this->get("/meta/meta.ger3?eingabe=kaffee&focus=web&out=json")->assertOk();

        $this->assertGreaterThanOrEqual(
            0.0,
            SuggestionDebtAuthorization::GET_DEBT(),
            "The debt was written off more than once, so the key paid for suggestions it did not get."
        );
    }
}
