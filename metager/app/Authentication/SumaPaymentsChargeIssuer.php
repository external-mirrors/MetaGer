<?php

namespace App\Authentication;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Token purchases through suma-payments' checkout (suma-crm
 * docs/keymanager-migration-plan.md, Steps 3 and 7): every method is
 * there, and its picker is the only way ({@see \App\Http\Controllers\ChargeController}).
 *
 * `create()` asks the keymanager to open the order and its checkout
 * session (`POST key/<key>/checkout/suma-payments`); the browser is sent to
 * the picker it answers with, and comes back to {@see ChargeController::returned()}.
 */
final class SumaPaymentsChargeIssuer
{
    /** What suma-payments' picker offers, named on the consent page. */
    public const METHODS = ["banktransfer", "sepa_directdebit", "card", "wero", "paypal", "cash"];

    private string $keyserver;

    public function __construct()
    {
        $keyserver = config("metager.metager.keymanager.server") ?: config("app.url") . "/keys";
        $this->keyserver = $keyserver . "/api/json";
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
