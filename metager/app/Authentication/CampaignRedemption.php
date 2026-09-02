<?php

namespace App\Authentication;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Einen Gutscheincode einlösen — der Unterbau von
 * {@see \App\Http\Controllers\VoucherController}.
 *
 * Aus dem Keymanager (`/keys/c`) hierher gezogen, im selben Schnitt wie
 * {@see CampaignIssuer} und {@see OrderHistoryIssuer}: die Seiten
 * (Codeeingabe, Vorschau, „hier ist dein Schlüssel“) stehen jetzt hier, und
 * was drüben blieb, ist die API über die `campaigns`/`campaign_vouchers`-
 * Tabellen (`routes/api.js`, `/c/voucher/*` und `/c/campaign/*`).
 *
 * Vier Fragen: was ein gedruckter Code oder ein öffentlicher Kampagnenlink
 * wert ist ({@see teaserByCode()}, {@see teaserByToken()}), und das Einlösen,
 * das einen frischen Schlüssel prägt ({@see redeemByCode()},
 * {@see redeemByToken()}).
 *
 * **Fehler kommen als stabiler Code, nicht als Fließtext.** Der Keyserver
 * antwortet mit `invalid_code`, `invalid_token`, `already_redeemed`,
 * `campaign_inactive`, `budget_exhausted` oder `rate_limited`; der Controller
 * übersetzt ihn. Ein Code, den diese Seite nicht kennt, ist kein Fehler,
 * sondern ein `unreachable` — dieselbe Vorsicht wie in {@see KeyResolver}.
 *
 * **Die scharfe Kante:** {@see redeemByCode()} löst den Gutschein drüben
 * atomar ein und prägt dann den Schlüssel. Bricht die HTTP-Verbindung
 * *danach* ab — Zeitüberschreitung, während der Keyserver schon geschrieben
 * hat —, ist der Code verbraucht und der Besucher sieht eine Fehlermeldung
 * ohne Schlüssel. `Http::timeout(8)` ist für die Lesezugriffe reichlich; für
 * das Einlösen bleibt dieses Fenster stehen und ist hier benannt, nicht
 * versteckt.
 */
final class CampaignRedemption
{
    private string $keyserver;

    public function __construct()
    {
        $keyserver = config("metager.metager.keymanager.server") ?: config("app.url") . "/keys";
        $this->keyserver = $keyserver . "/api/json";
    }

    /** Die Fehlercodes, die der Keyserver liefert und der Controller übersetzt. */
    public const ERRORS = [
        "invalid_code",
        "invalid_token",
        "already_redeemed",
        "campaign_inactive",
        "budget_exhausted",
        "rate_limited",
        "unknown",
    ];

    /**
     * Was ein gedruckter Gutscheincode wert ist — für die Vorschauseite.
     *
     * @return array{ok: true, campaign: array{name: string, tokens: int, validity_days: int}}
     *         |array{ok: false, error: string}
     */
    public function teaserByCode(string $code): array
    {
        return $this->teaser("/c/voucher/" . rawurlencode($code));
    }

    /**
     * Was der öffentliche Link einer Kampagne wert ist — für die Vorschauseite.
     *
     * @return array{ok: true, campaign: array{name: string, tokens: int, validity_days: int}}
     *         |array{ok: false, error: string}
     */
    public function teaserByToken(string $token): array
    {
        return $this->teaser("/c/campaign/" . rawurlencode($token));
    }

    /**
     * Löst einen gedruckten Gutscheincode ein und gibt den frischen Schlüssel
     * zurück.
     *
     * @return array{ok: true, key: string, charge: int, expiration: string, campaign: array{name: string, tokens: int}}
     *         |array{ok: false, error: string}
     */
    public function redeemByCode(string $code): array
    {
        return $this->redeem("/c/voucher/" . rawurlencode($code) . "/redeem");
    }

    /**
     * Löst über den öffentlichen Link einer Kampagne ein — prägt einen
     * Schlüssel, ohne einen Gutschein aus dem endlichen Vorrat zu verbrauchen.
     *
     * @return array{ok: true, key: string, charge: int, expiration: string, campaign: array{name: string, tokens: int}}
     *         |array{ok: false, error: string}
     */
    public function redeemByToken(string $token): array
    {
        return $this->redeem("/c/campaign/" . rawurlencode($token) . "/redeem");
    }

    /**
     * @return array{ok: true, campaign: array{name: string, tokens: int, validity_days: int}}
     *         |array{ok: false, error: string}
     */
    private function teaser(string $path): array
    {
        $response = $this->request("get", $path);
        if ($response === null) {
            return ["ok" => false, "error" => "unreachable"];
        }

        if ($response->successful()) {
            $campaign = $response->json("campaign");
            if (!is_array($campaign)) {
                Log::warning("keymanager voucher teaser answered without a campaign");

                return ["ok" => false, "error" => "unreachable"];
            }

            return [
                "ok" => true,
                "campaign" => [
                    "name" => (string) Arr::get($campaign, "name", ""),
                    "tokens" => (int) Arr::get($campaign, "tokens_per_key", 0),
                    "validity_days" => (int) Arr::get($campaign, "relay_expiration_days", 0),
                ],
            ];
        }

        return ["ok" => false, "error" => $this->errorOf($response)];
    }

    /**
     * @return array{ok: true, key: string, charge: int, expiration: string, campaign: array{name: string, tokens: int}}
     *         |array{ok: false, error: string}
     */
    private function redeem(string $path): array
    {
        $response = $this->request("post", $path);
        if ($response === null) {
            return ["ok" => false, "error" => "unreachable"];
        }

        if ($response->successful()) {
            $key = $response->json("key");
            $campaign = $response->json("campaign");

            if (!is_string($key) || $key === "" || !is_array($campaign)) {
                Log::warning("keymanager voucher redeem answered without a key");

                return ["ok" => false, "error" => "unreachable"];
            }

            return [
                "ok" => true,
                "key" => $key,
                "charge" => (int) $response->json("effective_charge", 0),
                "expiration" => (string) $response->json("expiration", ""),
                "campaign" => [
                    "name" => (string) Arr::get($campaign, "name", ""),
                    "tokens" => (int) Arr::get($campaign, "tokens_per_key", 0),
                ],
            ];
        }

        return ["ok" => false, "error" => $this->errorOf($response)];
    }

    private function request(string $method, string $path): ?\Illuminate\Http\Client\Response
    {
        try {
            return Http::timeout(8)
                ->withHeaders(["Authorization" => "Bearer " . config("metager.metager.keymanager.access_token")])
                ->{$method}($this->keyserver . $path);
        } catch (\Throwable $e) {
            Log::warning("keymanager voucher request unreachable: " . $e->getMessage());

            return null;
        }
    }

    /**
     * Der Fehlercode aus einer Nicht-2xx-Antwort — oder `unreachable`, wenn er
     * keiner ist, den diese Seite übersetzen kann. Ihn durchzureichen hieße,
     * eine unbekannte Zeichenkette dort zu zeigen, wo ein Übersetzungs-
     * schlüssel erwartet wird.
     */
    private function errorOf(\Illuminate\Http\Client\Response $response): string
    {
        $error = $response->json("error");

        if (is_string($error) && in_array($error, self::ERRORS, true)) {
            return $error;
        }

        Log::warning("keymanager voucher endpoint answered " . $response->status() . " with an unknown error");

        return "unreachable";
    }
}
