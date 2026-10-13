<?php

namespace App\Authentication;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Token purchases through suma-payments' checkout (suma-crm
 * docs/keymanager-migration-plan.md, Step 3).
 *
 * The payment methods move there one by one; the keymanager knows which
 * (`suma_payments.methods`, read through `GET checkout/suma-payments/methods`
 * and kept a minute). MetaGer leaves out its own tile for each moved method
 * and offers them together through suma-payments' picker instead — once all
 * have moved, the picker is the only way ({@see \App\Http\Controllers\ChargeController}).
 *
 * `create()` asks the keymanager to open the order and its checkout
 * session (`POST key/<key>/checkout/suma-payments`); the browser is sent to
 * the picker it answers with, and comes back to {@see ChargeController::returned()}.
 */
final class SumaPaymentsChargeIssuer
{
    /** Everything MetaGer still offers itself — moved together, its own pages go. */
    public const ALL = ["banktransfer", "sepa_directdebit", "card", "wero", "paypal", "cash"];

    private string $keyserver;

    public function __construct()
    {
        $keyserver = config("metager.metager.keymanager.server") ?: config("app.url") . "/keys";
        $this->keyserver = $keyserver . "/api/json";
    }

    /**
     * The methods already at suma-payments; none while the keymanager can't
     * be asked — then MetaGer's own pages stay, as before.
     *
     * @return list<string>
     */
    public function methods(): array
    {
        return Cache::remember("keymanager:suma-payments-methods", 60, function () {
            try {
                $response = Http::timeout(3)
                    ->withHeaders(["Authorization" => "Bearer " . config("metager.metager.keymanager.access_token")])
                    ->get($this->keyserver . "/checkout/suma-payments/methods");
            } catch (\Throwable $e) {
                Log::warning("keymanager suma-payments methods unreachable: " . $e->getMessage());

                return [];
            }
            $methods = $response->successful() ? Arr::get($response->json() ?? [], "methods") : null;

            return is_array($methods) ? array_values(array_filter($methods, "is_string")) : [];
        });
    }

    /** Whether every method has moved, so the picker is all there is. */
    public function allMoved(): bool
    {
        return array_diff(self::ALL, $this->methods()) === [];
    }

    /** @return array{public_id: string, redirect_url: string}|null */
    public function create(string $key, int $amount, ?string $returnOrigin, ?string $locale): ?array
    {
        try {
            $response = Http::timeout(10)
                ->withHeaders(["Authorization" => "Bearer " . config("metager.metager.keymanager.access_token")])
                ->post($this->keyserver . "/key/" . urlencode($key) . "/checkout/suma-payments", array_filter([
                    "amount" => $amount,
                    "return_origin" => $returnOrigin,
                    "locale" => $locale,
                ], fn ($value) => $value !== null));
        } catch (\Throwable $e) {
            Log::warning("keymanager suma-payments checkout unreachable: " . $e->getMessage());

            return null;
        }

        if (!$response->successful()) {
            Log::warning("keymanager suma-payments checkout answered " . $response->status());

            return null;
        }

        $publicId = Arr::get($response->json() ?? [], "public_id");
        $redirectUrl = Arr::get($response->json() ?? [], "redirect_url");
        if (!is_string($publicId) || $publicId === "" || !is_string($redirectUrl) || filter_var($redirectUrl, FILTER_VALIDATE_URL) === false) {
            Log::warning("keymanager suma-payments checkout answered with an incomplete order");

            return null;
        }

        return ["public_id" => $publicId, "redirect_url" => $redirectUrl];
    }
}
