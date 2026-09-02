<?php

namespace App\Landing;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * What a MetaGer key costs, as the keymanager states it.
 *
 * The pricing page moved here from `/keys/cost`, where it read `config.price`
 * out of the keymanager's own configuration. The checkout that spends those
 * numbers stayed behind. Writing them into `config/metager/metager.php` as well
 * would mean two repositories each asserting a price and nothing noticing when
 * they disagree — which, on a page next to a live payment flow, is a bug that
 * costs money. So the keymanager keeps the number and answers
 * `GET /api/json/price`, and this asks.
 *
 * The asking has to survive the keymanager being down, because a pricing page
 * that 500s is worse than one showing last week's price — and the price has not
 * changed in years. Hence two cache entries rather than `Cache::remember`:
 *
 *   fresh (1 hour)   the answer we serve without asking again
 *   stale (30 days)  the last answer we ever got, used when the request fails
 *
 * and below both, the values in config, which are only ever reached on a cold
 * cache during an outage.
 */
final class KeyPrice
{
    private const FRESH_KEY = "keymanager:price";
    private const STALE_KEY = "keymanager:price:stale";

    private const FRESH_MINUTES = 60;
    private const STALE_DAYS = 30;

    /**
     * @return array{per_token: float, vat: int, purchasable: list<int>}
     */
    public static function get(): array
    {
        $fresh = Cache::get(self::FRESH_KEY);
        if (self::isPrice($fresh)) {
            return self::normalize($fresh);
        }

        $fetched = self::fetch();
        if ($fetched !== null) {
            Cache::put(self::FRESH_KEY, $fetched, now()->addMinutes(self::FRESH_MINUTES));
            Cache::put(self::STALE_KEY, $fetched, now()->addDays(self::STALE_DAYS));
            return self::normalize($fetched);
        }

        $stale = Cache::get(self::STALE_KEY);
        if (self::isPrice($stale)) {
            return self::normalize($stale);
        }

        return self::normalize(config("metager.metager.keymanager.price"));
    }

    /** What one search costs, in euro. */
    public static function perToken(): float
    {
        return self::get()["per_token"];
    }

    /**
     * The token packages, as [amount => price in euro].
     *
     * The euro figures are whole numbers by construction — every tier is a
     * multiple of 100 tokens at a cent each — and the old page rendered them
     * with `toFixed(0)`. Kept, rather than "improved" into a formatted decimal:
     * `5 €` is what people have been quoted.
     *
     * @return array<int, int>
     */
    public static function tiers(): array
    {
        $price = self::get();

        $tiers = [];
        foreach ($price["purchasable"] as $amount) {
            $tiers[$amount] = (int) round($amount * $price["per_token"]);
        }

        return $tiers;
    }

    /**
     * @return array{per_token: float, vat: int, purchasable: list<int>}|null
     */
    private static function fetch(): ?array
    {
        $keyserver = config("metager.metager.keymanager.server") ?: config("app.url") . "/keys";

        try {
            $response = Http::timeout(2)->get($keyserver . "/api/json/price");
        } catch (\Throwable $e) {
            // A connection error, a DNS failure, a timeout. Never fatal here —
            // the caller is a page render, and the fallbacks above are exactly
            // for this.
            Log::warning("keymanager price unreachable: " . $e->getMessage());
            return null;
        }

        if (!$response->successful()) {
            return null;
        }

        $body = $response->json();

        return self::isPrice($body) ? $body : null;
    }

    /**
     * A price we would be willing to print.
     *
     * Checked rather than trusted because everything downstream is money: an
     * answer missing `per_token`, or carrying an empty tier list, would render
     * a page of free packages rather than fail, and the fallbacks exist to be
     * used in exactly that case.
     */
    private static function isPrice(mixed $value): bool
    {
        if (!is_array($value)) {
            return false;
        }

        $perToken = $value["per_token"] ?? null;
        $purchasable = $value["purchasable"] ?? null;

        if (!is_numeric($perToken) || (float) $perToken <= 0) {
            return false;
        }

        if (!is_array($purchasable) || $purchasable === []) {
            return false;
        }

        foreach ($purchasable as $amount) {
            if (!is_numeric($amount) || (int) $amount <= 0) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param array<string, mixed> $price
     * @return array{per_token: float, vat: int, purchasable: list<int>}
     */
    private static function normalize(array $price): array
    {
        return [
            "per_token" => (float) $price["per_token"],
            "vat" => (int) ($price["vat"] ?? 0),
            "purchasable" => array_values(array_map(
                static fn($amount): int => (int) $amount,
                $price["purchasable"]
            )),
        ];
    }
}
