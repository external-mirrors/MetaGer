<?php

namespace App\Authentication;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * VR Payment — Wero, das einzige Verfahren, das die alte Oberfläche
 * (routes/checkout/vrpayment.js) tatsächlich verlinkt, obwohl die Route
 * drüben generisch über `:method` ist.
 *
 * {@see create()} legt die Ladung an und bekommt dafür eine fertige Adresse
 * zurück, an die der Besucher weitergeleitet wird — dieselbe Trennung wie bei
 * {@see MicropaymentChargeIssuer}: diese Klasse sieht nie VR Payments
 * space_id/user_id/api_key, nur die fertige URL.
 */
final class VRPaymentChargeIssuer
{
    public const METHOD = "wero";

    public const PRIVACY_URL = "https://www.vr-payment.de/datenschutz/karteninhaber-online";

    private string $keyserver;

    public function __construct()
    {
        $keyserver = config("metager.metager.keymanager.server") ?: config("app.url") . "/keys";
        $this->keyserver = $keyserver . "/api/json";
    }

    /**
     * @return array{public_id: string, redirect_url: string}|null
     */
    public function create(string $key, int $amount): ?array
    {
        try {
            $response = Http::timeout(5)
                ->withHeaders(["Authorization" => "Bearer " . config("metager.metager.keymanager.access_token")])
                ->post(
                    $this->keyserver . "/key/" . urlencode($key) . "/checkout/vrpayment/" . self::METHOD,
                    ["amount" => $amount]
                );
        } catch (\Throwable $e) {
            Log::warning("keymanager vrpayment checkout unreachable: " . $e->getMessage());

            return null;
        }

        if (!$response->successful()) {
            Log::warning("keymanager vrpayment checkout answered " . $response->status());

            return null;
        }

        $body = $response->json();
        $body = is_array($body) ? $body : [];

        $publicId = Arr::get($body, "public_id");
        $redirectUrl = Arr::get($body, "redirect_url");

        if (
            !is_string($publicId) || $publicId === ""
            || !is_string($redirectUrl) || filter_var($redirectUrl, FILTER_VALIDATE_URL) === false
        ) {
            Log::warning("keymanager vrpayment checkout answered with an incomplete order");

            return null;
        }

        return [
            "public_id" => $publicId,
            "redirect_url" => $redirectUrl,
        ];
    }
}
