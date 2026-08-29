<?php

namespace Tests\Feature;

use App\Landing\KeyPrice;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * The price on /preise is the keymanager's price, and the page renders it even
 * when the keymanager cannot be reached.
 *
 * Both halves matter. The first is why this class exists at all — the checkout
 * that spends these numbers lives in the other repository, and a pricing page
 * quoting a figure the checkout will not honour is the one bug here that costs
 * money. The second is why it is not a plain `Cache::remember`: a deploy of the
 * keymanager must not be able to take MetaGer's pricing page down with it.
 */
class KeyPriceTest extends TestCase
{
    private const ANSWER = [
        "per_token" => 0.02,
        "vat" => 19,
        "purchasable" => [100, 250],
    ];

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
        Http::preventStrayRequests();
    }

    private function fakePrice(array $body = self::ANSWER): void
    {
        Http::fake(["*/api/json/price" => Http::response($body)]);
    }

    public function testItAsksTheKeymanager(): void
    {
        $this->fakePrice();

        $this->assertSame(self::ANSWER, KeyPrice::get());
    }

    public function testItAsksOnlyOncePerHour(): void
    {
        $this->fakePrice();

        KeyPrice::get();
        KeyPrice::get();
        KeyPrice::get();

        Http::assertSentCount(1);
    }

    /**
     * The whole point of the two-entry cache: the fresh copy expires, the
     * keymanager is down, and the page still renders yesterday's price rather
     * than a 500.
     */
    public function testAnOutageFallsBackToTheLastAnswerItGot(): void
    {
        $this->fakePrice();
        KeyPrice::get();

        Cache::forget("keymanager:price");
        Http::fake(["*/api/json/price" => Http::response("", 503)]);

        $this->assertSame(self::ANSWER, KeyPrice::get());
    }

    /** A connection that never opens is an outage like any other, not an exception. */
    public function testAConnectionFailureIsAnOutageAndNotAnError(): void
    {
        $this->fakePrice();
        KeyPrice::get();
        Cache::forget("keymanager:price");

        Http::fake(fn() => throw new \Illuminate\Http\Client\ConnectionException("no route to host"));

        $this->assertSame(self::ANSWER, KeyPrice::get());
    }

    /** Cold cache and an outage at the same time: config, which is the only thing left. */
    public function testWithNothingCachedAndNothingReachableItUsesTheConfiguredPrice(): void
    {
        Http::fake(["*/api/json/price" => Http::response("", 500)]);

        $this->assertSame([
            "per_token" => 0.01,
            "vat" => 7,
            "purchasable" => [500, 1000, 2000, 3000, 4000, 6000],
        ], KeyPrice::get());
    }

    /**
     * A malformed answer is worse than no answer, because it renders.
     *
     * @param array<string, mixed>|string $body
     */
    #[\PHPUnit\Framework\Attributes\DataProvider("nonsense")]
    public function testAnAnswerThatIsNotAPriceIsRefused(array|string $body): void
    {
        Http::fake(["*/api/json/price" => Http::response($body)]);

        // Falls all the way through to config, i.e. the answer was not believed.
        $this->assertSame(0.01, KeyPrice::get()["per_token"]);
    }

    /** @return array<string, array{0: array<string, mixed>|string}> */
    public static function nonsense(): array
    {
        return [
            "no price at all" => [["vat" => 7]],
            "free tokens" => [["per_token" => 0, "vat" => 7, "purchasable" => [500]]],
            "no tiers" => [["per_token" => 0.01, "vat" => 7, "purchasable" => []]],
            "a tier of nothing" => [["per_token" => 0.01, "vat" => 7, "purchasable" => [500, 0]]],
            "tiers that are not numbers" => [["per_token" => 0.01, "vat" => 7, "purchasable" => ["viele"]]],
            "not an object" => ["kaputt"],
        ];
    }

    /** What the page actually draws: amount → euro, in the configured order. */
    public function testTheTiersAreTheOnesThePageDraws(): void
    {
        Http::fake(["*/api/json/price" => Http::response([
            "per_token" => 0.01,
            "vat" => 7,
            "purchasable" => [500, 1000, 6000],
        ])]);

        $this->assertSame([500 => 5, 1000 => 10, 6000 => 60], KeyPrice::tiers());
    }
}
