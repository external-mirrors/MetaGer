<?php

namespace App\Authentication;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Die Entwicklungs-Zahlart — lädt sofort auf, ohne echte Zahlung.
 *
 * Ported aus `routes/checkout/manual.js`, das dort mit einem bloßen String
 * warf und außerhalb einer Entwicklungsumgebung mit 500 antwortete; drüben
 * antwortet der Endpunkt jetzt mit 404, damit die Route so aussieht, als gäbe
 * es sie nicht, statt eine Ausweichmöglichkeit anzukündigen. Dieselbe Antwort
 * bekommt jeder Aufruf hier, wenn `app()->environment('local')` falsch ist —
 * siehe {@see \App\Http\Controllers\ChargeController}.
 *
 * Eigene Klasse statt einer zweiten Methode auf {@see ChargeOrderIssuer}: die
 * Zahlart lädt sofort auf, legt keine wartende Ladung an und antwortet mit
 * Feldern, die zu einem Guthaben gehören, nicht zu einer offenen Bestellung —
 * derselbe Unterschied, aus dem drüben zwei Endpunkte statt einem wurden.
 */
final class ManualChargeIssuer
{
    private string $keyserver;

    public function __construct()
    {
        $keyserver = config("metager.metager.keymanager.server") ?: config("app.url") . "/keys";
        $this->keyserver = $keyserver . "/api/json";
    }

    /**
     * @return array{key: string, key_charge: float, payment_reference: string, charged: float}|null
     */
    public function charge(string $key, int $amount): ?array
    {
        try {
            $response = Http::timeout(5)
                ->withHeaders(["Authorization" => "Bearer " . config("metager.metager.keymanager.access_token")])
                ->post($this->keyserver . "/key/" . urlencode($key) . "/checkout/manual", [
                    "amount" => $amount,
                ]);
        } catch (\Throwable $e) {
            Log::warning("keymanager manual checkout unreachable: " . $e->getMessage());

            return null;
        }

        if (!$response->successful()) {
            Log::warning("keymanager manual checkout answered " . $response->status());

            return null;
        }

        $body = $response->json();
        $body = is_array($body) ? $body : [];

        $responseKey = Arr::get($body, "key");
        $keyCharge = Arr::get($body, "key_charge");
        $paymentReference = Arr::get($body, "payment_reference");
        $charged = Arr::get($body, "charged");

        if (
            !is_string($responseKey) || !KeyIssuer::isKey($responseKey)
            || !is_numeric($keyCharge)
            || !is_string($paymentReference) || $paymentReference === ""
            || !is_numeric($charged)
        ) {
            Log::warning("keymanager manual checkout answered with an incomplete charge");

            return null;
        }

        return [
            "key" => strtolower($responseKey),
            "key_charge" => (float) $keyCharge,
            "payment_reference" => $paymentReference,
            "charged" => (float) $charged,
        ];
    }
}
