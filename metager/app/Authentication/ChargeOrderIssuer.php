<?php

namespace App\Authentication;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Eine Ladung anlegen und wiederfinden — der erste Schritt des Bezahlvorgangs,
 * der hierher gezogen ist ({@see \App\Http\Controllers\ChargeController}).
 *
 * Legt nichts an, was nicht ohnehin drüben landen müsste: `PaymentReference`
 * ist dort in Postgres verankert, und {@see create()} tut nichts weiter als
 * `PaymentReference.CREATE_NEW_REQUEST` von hier aus anzustoßen — es wird
 * dabei noch nicht bezahlt, nur ein Auftragsplatz reserviert und der Preis
 * festgehalten.
 *
 * {@see find()} ist der Grund, warum die Barzahlung ohne eine Sitzung
 * auskommt: die Kontrollerroute leitet nach dem Anlegen auf eine eigene, mit
 * GET erreichbare Adresse weiter (POST/redirect/GET statt der alten, erneut
 * gerenderten POST-Antwort, die ein Neuladen verdoppelt hätte), und die liest
 * die Ladung hier noch einmal nach, statt sich auf etwas zu verlassen, das nur
 * durch die Weiterleitung mitgereist wäre.
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
     * Legt eine neue Ladung an, oder liefert null.
     *
     * Null heißt hier zweierlei, und der Aufrufer unterscheidet es nicht:
     * entweder der Keyserver antwortet nicht, oder er lehnt ab, weil der
     * Schlüssel schon drei offene Aufträge trägt (`Key.isChargable()`
     * drüben). Welcher der beiden Fälle es war, weiß
     * {@see \App\Http\Controllers\ChargeController} besser aus
     * {@see \App\Landing\ChargeEligibility} als aus einer Zeichenkette, die
     * über die Grenze gereist ist.
     *
     * @return array{public_id: string, amount: int, price: string, expires_at: string}|null
     */
    public function create(string $key, int $amount): ?array
    {
        try {
            $response = Http::timeout(5)
                ->withHeaders(["Authorization" => "Bearer " . config("metager.metager.keymanager.access_token")])
                ->post($this->keyserver . "/key/" . urlencode($key) . "/checkout", [
                    "amount" => $amount,
                ]);
        } catch (\Throwable $e) {
            Log::warning("keymanager checkout create unreachable: " . $e->getMessage());

            return null;
        }

        if (!$response->successful()) {
            Log::warning("keymanager checkout create answered " . $response->status());

            return null;
        }

        return $this->validated($response->json());
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
     * dieser Ladung steht. Bar kennt das Feld nicht (dort entscheidet die
     * Existenz der Bestellung selbst), aber die Rückkehrseite eines
     * weiterleitenden Anbieters ({@see \App\Http\Controllers\ChargeController::returned()})
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
     * Prüft die drei Felder, die beide Endpunkte gemeinsam haben, statt der
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
