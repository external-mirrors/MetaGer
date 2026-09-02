<?php

namespace App\Authentication;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Ein Schlüssels eigene Gutscheinaktionen verwalten — der Unterbau von
 * {@see \App\Http\Controllers\CampaignController}.
 *
 * Aus dem Keymanager (`/keys/key/<uuid>/campaigns`) hierher gezogen, im
 * selben Schnitt wie {@see OrderHistoryIssuer}: was drüben blieb, ist die
 * API (`/api/json/key/<uuid>/campaigns`, in `routes/api.js` neben der
 * Bestell-API); hier steht die Seite. Jede Aktion trägt den angemeldeten
 * Schlüssel im Pfad — wem eine Kampagne gehört, entscheidet der Keyserver
 * gegen `source_key`, bevor irgendetwas zurückkommt; ein `:id`, das nicht zu
 * `$key` gehört, ist dort eine 404, nie eine fremde Kampagne.
 *
 * Absichtlich nicht hier: die OIDC-geschützte Admin-Oberfläche und der
 * öffentliche Einlöseweg (`/c/...`) — beide bleiben, wo sie sind.
 */
final class CampaignIssuer
{
    private string $keyserver;

    public function __construct()
    {
        $keyserver = config("metager.metager.keymanager.server") ?: config("app.url") . "/keys";
        $this->keyserver = $keyserver . "/api/json";
    }

    /**
     * Die Kampagnen eines Schlüssels, plus wie viel Guthaben es noch geben
     * könnte, um eine weitere damit zu decken — oder null, wenn der Keyserver
     * gerade nicht antwortet.
     *
     * @return array{
     *     campaigns: list<array{
     *         id: int, name: string, active: bool, disabled: bool,
     *         tokens_per_key: int, total_volume: int, backing_expires_at: ?string,
     *         public_token: string,
     *         stats: ?array{vouchers_total: int, vouchers_redeemed: int, backing_charge: int}
     *     }>,
     *     max_campaign_volume: int
     * }|null
     *
     * `max_campaign_volume` kommt als **Bruchzahl** herüber und wird hier auf
     * ganze Token abgerundet. Der Keyserver rechnet `Key.get_non_relay_charge()`
     * als `Math.round(summe * 10) / 10` — ein Schlüssel, von dem je ein
     * Dezitoken abgebucht wurde (`/api/json/token`, `tokens + decitokens/10`),
     * antwortet also mit `459.5` und nicht mit `459`. Ein `is_int()` darauf war
     * für jeden benutzten Schlüssel falsch: die Seite behauptete „0 Token“ über
     * einem vollen Guthaben und setzte `max="0"` ins Formular, womit sich gar
     * keine Kampagne mehr anlegen ließ.
     *
     * Abgerundet und nicht als Bruchzahl weitergereicht, weil beide Abnehmer
     * ganze Token wollen: die Anlege-Route drüben validiert `total_volume` mit
     * `isInt()`, und die Kontoseite zeigt Guthaben ohnehin als `floor($charge)`.
     */
    public function list(string $key): ?array
    {
        $response = $this->get("/key/" . urlencode($key) . "/campaigns");
        if ($response === null || !$response->successful()) {
            return null;
        }

        $body = $response->json();
        $body = is_array($body) ? $body : [];
        $maxVolume = Arr::get($body, "max_campaign_volume");

        return [
            "campaigns" => $this->campaigns(Arr::get($body, "campaigns")),
            "max_campaign_volume" => is_numeric($maxVolume) ? (int) floor((float) $maxVolume) : 0,
        ];
    }

    /**
     * Legt eine neue Gutscheinaktion an.
     *
     * `Campaign.CREATE` drüben bleibt bei Fließtext-Fehlern (geteilt mit der
     * Admin-Oberfläche); der Keyserver prüft jede Regel, die für eine
     * Nutzer-Kampagne überhaupt erreichbar ist, vorher selbst und liefert
     * dafür einen stabilen Code (siehe die Route in `routes/api.js`) — der
     * hier durchgereicht wird, damit {@see \App\Http\Controllers\
     * CampaignController} ihn lokalisiert anzeigen kann, statt Fließtext zu
     * raten.
     *
     * @param array{name: string, tokens_per_key: string, total_volume: string, voucher_count?: string} $fields
     * @return array{ok: true}|array{ok: false, code: string}
     */
    public function create(string $key, array $fields): array
    {
        try {
            $response = Http::timeout(8)
                ->withHeaders(["Authorization" => "Bearer " . config("metager.metager.keymanager.access_token")])
                ->post($this->keyserver . "/key/" . urlencode($key) . "/campaigns", $fields);
        } catch (\Throwable $e) {
            Log::warning("keymanager campaign create unreachable: " . $e->getMessage());

            return ["ok" => false, "code" => "unreachable"];
        }

        if ($response->successful()) {
            return ["ok" => true];
        }

        if ($response->status() === 422) {
            $code = $response->json("error");

            return ["ok" => false, "code" => is_string($code) && $code !== "" ? $code : "invalid"];
        }

        Log::warning("keymanager campaign create failed with status " . $response->status());

        return ["ok" => false, "code" => "unreachable"];
    }

    /** Deaktiviert eine Kampagne. `false` deckt sowohl "nicht erreichbar" als auch "gehört nicht $key" ab — beides zeigt dieselbe Rückkehr zur Liste. */
    public function disable(string $key, int $id): bool
    {
        return $this->post("/key/" . urlencode($key) . "/campaigns/" . $id . "/disable");
    }

    /**
     * Löscht eine Kampagne endgültig. Der Keyserver lehnt eine noch aktive
     * mit 409 ab — die Oberfläche zeigt den Löschen-Knopf ohnehin nur bei
     * einer bereits deaktivierten, also nur bei einem Wettlauf erreichbar;
     * auch das ist hier nur "hat nicht geklappt", zurück zur Liste.
     */
    public function delete(string $key, int $id): bool
    {
        return $this->post("/key/" . urlencode($key) . "/campaigns/" . $id . "/delete");
    }

    /**
     * Die Gutscheinkarten (PDF) einer Kampagne, oder null.
     *
     * Wortgleich zu {@see OrderHistoryIssuer::confirmationPdf()} — der
     * Keyserver rendert, hier werden nur die Bytes durchgereicht.
     *
     * @return array{body: string, content_type: string}|null
     */
    public function cardsPdf(string $key, int $id): ?array
    {
        $response = $this->get("/key/" . urlencode($key) . "/campaigns/" . $id . "/cards.pdf");
        if ($response === null || !$response->successful()) {
            return null;
        }

        $contentType = $response->header("Content-Type");
        if (!is_string($contentType) || !str_starts_with($contentType, "application/pdf")) {
            Log::warning("keymanager campaign cards PDF answered with " . ($contentType ?: "no content type"));

            return null;
        }

        return [
            "body" => $response->body(),
            "content_type" => "application/pdf",
        ];
    }

    private function post(string $path): bool
    {
        try {
            $response = Http::timeout(8)
                ->withHeaders(["Authorization" => "Bearer " . config("metager.metager.keymanager.access_token")])
                ->post($this->keyserver . $path);
        } catch (\Throwable $e) {
            Log::warning("keymanager campaign request unreachable: " . $e->getMessage());

            return false;
        }

        return $response->successful();
    }

    private function get(string $path): ?\Illuminate\Http\Client\Response
    {
        try {
            return Http::timeout(8)
                ->withHeaders(["Authorization" => "Bearer " . config("metager.metager.keymanager.access_token")])
                ->get($this->keyserver . $path);
        } catch (\Throwable $e) {
            Log::warning("keymanager campaign request unreachable: " . $e->getMessage());

            return null;
        }
    }

    /**
     * @param mixed $raw
     * @return list<array<string, mixed>>
     */
    private function campaigns(mixed $raw): array
    {
        if (!is_array($raw)) {
            return [];
        }

        $out = [];
        foreach ($raw as $row) {
            if (!is_array($row)) {
                continue;
            }

            $id = Arr::get($row, "id");
            $name = Arr::get($row, "name");
            $publicToken = Arr::get($row, "public_token");

            if (!is_int($id) || !is_string($name) || $name === "" || !is_string($publicToken) || $publicToken === "") {
                Log::warning("keymanager campaign list answered with an incomplete campaign");
                continue;
            }

            $stats = Arr::get($row, "stats");
            $backingExpiresAt = Arr::get($row, "backing_expires_at");

            $out[] = [
                "id" => $id,
                "name" => $name,
                "active" => (bool) Arr::get($row, "active", false),
                "disabled" => (bool) Arr::get($row, "disabled", false),
                "tokens_per_key" => (int) Arr::get($row, "tokens_per_key", 0),
                "total_volume" => (int) Arr::get($row, "total_volume", 0),
                "backing_expires_at" => is_string($backingExpiresAt) ? $backingExpiresAt : null,
                "public_token" => $publicToken,
                "stats" => is_array($stats) ? [
                    "vouchers_total" => (int) Arr::get($stats, "vouchers_total", 0),
                    "vouchers_redeemed" => (int) Arr::get($stats, "vouchers_redeemed", 0),
                    "backing_charge" => (int) Arr::get($stats, "backing_charge", 0),
                ] : null,
            ];
        }

        return $out;
    }
}
