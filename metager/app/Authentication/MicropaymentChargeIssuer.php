<?php

namespace App\Authentication;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Micropayment — Überweisung/Vorkasse (`prepay`), Lastschrift und
 * Sofortüberweisung (`directbanking`), alle drei über einen Anbieter.
 *
 * {@see create()} legt die Ladung an und bekommt dafür eine fertige Adresse
 * zurück, an die der Besucher weitergeleitet wird — das Siegel darüber
 * (`payments.micropayment.access_key`) wird drüben berechnet und bleibt
 * drüben; diese Klasse sieht nie mehr als die fertige URL, genauso wie
 * {@see ChargeOrderIssuer} und {@see ManualChargeIssuer} keinen anderen
 * Anbietersecret sehen.
 */
final class MicropaymentChargeIssuer
{
    /** Dieselben drei Werte, die die Route und die Views gegenprüfen. */
    public const SERVICES = ["prepay", "lastschrift", "directbanking"];

    /**
     * Das Datenschutzdokument je Unterart — dieselben drei Adressen, die
     * `Micropayment.SERVICES` drüben (app/payment_processor/Micropayment.js)
     * pflegt. Rein informativ, kein Geheimnis, deshalb hier verdoppelt statt
     * über einen weiteren Netzwerkaufruf geholt.
     */
    public const PRIVACY_URLS = [
        "prepay" => "https://resources.micropayment.de/billing/documents/privacy-policy/prepay/prepay-gmbh-de.pdf",
        "lastschrift" => "https://resources.micropayment.de/billing/documents/privacy-policy/debit/debit-gmbh-de.pdf",
        "directbanking" => "https://resources.micropayment.de/billing/documents/privacy-policy/sofort/sofort-gmbh-de.pdf",
    ];

    private string $keyserver;

    public function __construct()
    {
        $keyserver = config("metager.metager.keymanager.server") ?: config("app.url") . "/keys";
        $this->keyserver = $keyserver . "/api/json";
    }

    /**
     * @return array{public_id: string, redirect_url: string}|null
     */
    public function create(string $key, int $amount, string $service, ?string $email = null): ?array
    {
        try {
            $response = Http::timeout(5)
                ->withHeaders(["Authorization" => "Bearer " . config("metager.metager.keymanager.access_token")])
                ->post(
                    $this->keyserver . "/key/" . urlencode($key) . "/checkout/micropayment/" . urlencode($service),
                    array_filter([
                        "amount" => $amount,
                        "email" => $email,
                    ], fn ($value) => $value !== null)
                );
        } catch (\Throwable $e) {
            Log::warning("keymanager micropayment checkout unreachable: " . $e->getMessage());

            return null;
        }

        if (!$response->successful()) {
            Log::warning("keymanager micropayment checkout answered " . $response->status());

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
            Log::warning("keymanager micropayment checkout answered with an incomplete order");

            return null;
        }

        return [
            "public_id" => $publicId,
            "redirect_url" => $redirectUrl,
        ];
    }
}
