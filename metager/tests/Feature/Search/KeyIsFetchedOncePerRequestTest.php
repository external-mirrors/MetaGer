<?php

namespace Tests\Feature\Search;

use App\Authentication\KeyUser;
use App\Models\Authorization\KeyAuthorization;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\Concerns\FakesSearchEngines;
use Tests\Support\FakeFetcher;
use Tests\TestCase;

/**
 * One key, one lookup.
 *
 * Two mechanisms ask the keyserver for the same key's charge on the same
 * request, by two different routes:
 *
 *   - `Auth::guard("key")` → KeyUser::getKeyData(), a direct HTTP call with a
 *     ten-second cache in front of it;
 *   - App\Models\Authorization\KeyAuthorization, which queues a mission for the
 *     `requests:fetcher` worker and then blocks on `brpop` for up to ten
 *     seconds waiting for the reply.
 *
 * Both run on the start page and the result page. AuthenticationValidation
 * returns early for a key user, but MetaGerSearch resolves
 * `app(Authorization::class)` to build the loader cache and the result blades
 * ask it whether the visitor may search — so the second round trip happened
 * anyway, for a number the first one already had.
 *
 * That is twice the keyserver load (and so twice the load on the Postgres
 * behind it) for every authenticated page, plus a blocking Redis pop with a
 * ten-second ceiling sitting on the request path. Both are exactly what the
 * work around this is trying to take off these two routes.
 *
 * ## Why this is not driven through a page
 *
 * It was written that way first and could not fail.
 * App\Providers\AuthorizationServiceProvider reads the key out of the cookie,
 * the header and the query in `register()` — at provider registration, not when
 * the binding is resolved — and in a test the container is built in setUp,
 * before the test has said anything about cookies. So the provider captures an
 * empty key, `new KeyAuthorization("")` returns on its first line without
 * fetching anything, and a page-level assertion that no key lookup was queued
 * passes whether or not the change under test is present. Adding the cookie
 * does not help: it is read at the same too-early moment.
 *
 * A green test that cannot fail is worse than no test, so the duplicate is
 * exercised where it actually happens — at the constructor, which is the whole
 * of what changed.
 *
 * @see \App\Models\Authorization\KeyAuthorization::fetchKeyData()
 */
class KeyIsFetchedOncePerRequestTest extends TestCase
{
    use FakesSearchEngines;

    private const KEY = "9f2c7a1e-3b4d-4e5f-8a90-1b2c3d4e5f60";
    private const CHARGE = 1234.0;

    /**
     * A signed-in visitor whose charge the guard has already fetched — which is
     * the state every page is in by the time it resolves Authorization, because
     * the guard resolves first.
     */
    private function signedIn(): void
    {
        Http::preventStrayRequests();
        Http::fake(["*" => Http::response(["key" => self::KEY, "charge" => self::CHARGE])]);

        Cache::put("keyserver:key:" . self::KEY, [
            "key" => self::KEY,
            "charge" => self::CHARGE,
        ], now()->addMinutes(10));

        $this->be(new KeyUser(self::KEY), "key");
    }

    /**
     * Stand in for the fetch worker, and record what was asked of it.
     *
     * Not optional decoration: the fallback path this test has to keep exercising
     * ends in `Redis::brpop($hash, 10)`, and with nothing answering, each of
     * those tests sits out the full ten seconds. FakeFetcher answers the moment
     * a mission is queued — the same reason every other search test uses it —
     * and its own record of the missions is a better assertion than counting a
     * Redis list anyway: it survives the queue key changing, and it names the
     * mission rather than counting it.
     */
    private function fetchWorker(): FakeFetcher
    {
        return $this->fakeEngineResponses([]);
    }

    /**
     * @param FakeFetcher $worker
     * @return list<string>
     */
    private function missionNames(FakeFetcher $worker): array
    {
        return array_map(fn(array $mission) => $mission["name"] ?? "?", $worker->missions());
    }

    public function testTheLegacyAuthorizationTakesTheChargeTheGuardAlreadyHas(): void
    {
        $this->signedIn();
        $worker = $this->fetchWorker();

        $authorization = new KeyAuthorization(self::KEY);

        $this->assertSame(self::CHARGE, $authorization->availableTokens);
        $this->assertSame(
            [],
            $this->missionNames($worker),
            "a second lookup for the same key was queued for the fetch worker"
        );
    }

    /**
     * The guard's answer belongs to the guard's key and to no other. This class
     * is constructed with an explicit, different key by
     * AuthenticationValidation when an anonymous token payment falls back to
     * one — taking the signed-in visitor's charge there would authorize a key
     * against somebody else's balance.
     */
    public function testADifferentKeyIsStillFetchedOnItsOwn(): void
    {
        $this->signedIn();
        $worker = $this->fetchWorker();

        new KeyAuthorization("11111111-2222-4333-8444-555555555555");

        $this->assertSame(
            ["Key Login"],
            $this->missionNames($worker),
            "the charge of the signed-in key was used for a different key"
        );
    }

    /**
     * With nobody signed in there is nothing to inherit, and the old path has
     * to still work — this is the route an app or webextension client takes
     * when it sends a key the guard does not resolve.
     */
    public function testWithoutAGuardUserTheOldPathIsStillUsed(): void
    {
        $worker = $this->fetchWorker();

        new KeyAuthorization(self::KEY);

        $this->assertSame(["Key Login"], $this->missionNames($worker));
    }

}
