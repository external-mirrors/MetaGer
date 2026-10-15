<?php

namespace App\Authentication;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Eine Ladung wiederfinden — für die Rückkehr von suma-payments
 * ({@see \App\Http\Controllers\ChargeController::returned()}). Angelegt wird
 * sie über {@see SumaPaymentsChargeIssuer::create()}.
 *
 * Liest die Ladung beim Keyserver nach, statt sich auf etwas zu verlassen, das
 * nur durch die Weiterleitung mitgereist wäre.
 */
final class ChargeOrderIssuer
{
    private string $keyserver;

    public function __construct()
    {
        $keyserver = config("metager.metager.keymanager.server") ?: config("app.url") . "/keys";
        $this->keyserver = $keyserver . "/api/json";
    }

    /**
     * Liest eine bereits angelegte Ladung anhand ihrer öffentlichen Nummer.
     *
     * Der Schlüssel im Antwortkörper wird von
     * {@see \App\Http\Controllers\ChargeController} gegen den angemeldeten
     * Schlüssel geprüft, bevor irgendetwas davon angezeigt wird — die Nummer
     * ist eine kleine, fortlaufende Zahl und kein Geheimnis.
     *
     * `paid` ist kein Beleg — nur, ob drüben schon eine `Payment`-Zeile zu
     * dieser Ladung steht. Die Rückkehrseite
     * ({@see \App\Http\Controllers\ChargeController::returned()})
     * braucht genau diese eine Auskunft, um "danke" von "wird noch bearbeitet"
     * zu unterscheiden.
     *
     * @return array{public_id: string, amount: int, price: string, expires_at: string, key: string, paid: bool}|null
     */
    public function find(string $publicId): ?array
    {
        try {
            $response = Http::timeout(5)
                ->withHeaders(["Authorization" => "Bearer " . config("metager.metager.keymanager.access_token")])
                ->get($this->keyserver . "/checkout/" . urlencode($publicId));
        } catch (\Throwable $e) {
            Log::warning("keymanager checkout lookup unreachable: " . $e->getMessage());

            return null;
        }

        if (!$response->successful()) {
            return null;
        }

        $data = $this->validated($response->json());
        if ($data === null) {
            return null;
        }

        $key = Arr::get($response->json(), "key");
        if (!is_string($key) || !KeyIssuer::isKey($key)) {
            Log::warning("keymanager checkout lookup answered with something that is not a key");

            return null;
        }

        $data["key"] = strtolower($key);
        $data["paid"] = (bool) Arr::get($response->json(), "paid", false);

        return $data;
    }

    /**
     * Prüft die Felder der Ladung, statt der
     * Seite eine Antwort zu geben, die sie ungeprüft in einen Preis und ein
     * Datum verwandelt.
     *
     * @return array{public_id: string, amount: int, price: string, expires_at: string}|null
     */
    private function validated(mixed $body): ?array
    {
        $body = is_array($body) ? $body : [];

        $publicId = Arr::get($body, "public_id");
        $amount = Arr::get($body, "amount");
        $price = Arr::get($body, "price");
        $expiresAt = Arr::get($body, "expires_at");

        if (
            !is_string($publicId) || $publicId === ""
            || !is_int($amount) || $amount <= 0
            || !(is_string($price) || is_numeric($price))
            || !is_string($expiresAt) || $expiresAt === ""
        ) {
            Log::warning("keymanager checkout answered with an incomplete order");

            return null;
        }

        return [
            "public_id" => $publicId,
            "amount" => $amount,
            "price" => (string) $price,
            "expires_at" => $expiresAt,
        ];
    }
}
