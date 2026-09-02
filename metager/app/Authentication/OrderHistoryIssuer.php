<?php

namespace App\Authentication;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Eine bezahlte Bestellung nachschlagen und ihre Auftragsbestätigung,
 * Rechnung oder Erstattung anstoßen — der Unterbau von
 * {@see \App\Http\Controllers\OrderController}.
 *
 * Getrennt von {@see ChargeOrderIssuer}, obwohl beide denselben Endpunkt
 * (`GET /api/json/checkout/<public_id>`) rufen: `ChargeOrderIssuer` gehört zum
 * Kaufvorgang und liest nur, ob schon bezahlt wurde; hier geht es um die
 * fertige Bestellung mit ihren Zahlungen, lange nachdem der Kauf vorbei ist.
 *
 * Wie bei `ChargeOrderIssuer` reicht der Antwortkörper den `key` mit — die
 * öffentliche Nummer ist klein und fortlaufend, kein Geheimnis, und wessen
 * Bestellung es ist, entscheidet {@see \App\Http\Controllers\OrderController}
 * gegen den angemeldeten Schlüssel, bevor irgendetwas angezeigt wird.
 */
final class OrderHistoryIssuer
{
    private string $keyserver;

    public function __construct()
    {
        $keyserver = config("metager.metager.keymanager.server") ?: config("app.url") . "/keys";
        $this->keyserver = $keyserver . "/api/json";
    }

    /**
     * Liest eine Bestellung anhand ihrer öffentlichen Nummer (`Z…` oder die
     * blanke Zahl), oder null, wenn es sie nicht gibt oder der Keyserver
     * gerade nicht antwortet — der Aufrufer unterscheidet beides nicht, so wie
     * {@see ChargeOrderIssuer::find()}.
     *
     * @return array{
     *     public_id: string,
     *     amount: int,
     *     price: string,
     *     expires_at: string,
     *     created_at: ?string,
     *     key: string,
     *     paid: bool,
     *     payments: list<array{
     *         public_id: string, net: string, vat: string, gross: string,
     *         vat_rate: float, token_count: int, converted_price: ?float,
     *         converted_currency: ?string, payment_processor: ?string,
     *         created_at: ?string, invoice_available: bool,
     *         refund_available: bool, refund_token_count: int, refund_amount: string
     *     }>
     * }|null
     */
    public function find(string $publicId): ?array
    {
        $response = $this->get("/checkout/" . urlencode($publicId));
        if ($response === null || !$response->successful()) {
            return null;
        }

        $body = $response->json();
        $body = is_array($body) ? $body : [];

        $publicIdOut = Arr::get($body, "public_id");
        $amount = Arr::get($body, "amount");
        $price = Arr::get($body, "price");
        $expiresAt = Arr::get($body, "expires_at");
        $key = Arr::get($body, "key");

        if (
            !is_string($publicIdOut) || $publicIdOut === ""
            || !is_int($amount) || $amount <= 0
            || !(is_string($price) || is_numeric($price))
            || !is_string($expiresAt) || $expiresAt === ""
            || !is_string($key) || !KeyIssuer::isKey($key)
        ) {
            Log::warning("keymanager order lookup answered with an incomplete order");

            return null;
        }

        return [
            "public_id" => $publicIdOut,
            "amount" => $amount,
            "price" => (string) $price,
            "expires_at" => $expiresAt,
            "created_at" => is_string(Arr::get($body, "created_at")) ? $body["created_at"] : null,
            "key" => strtolower($key),
            "paid" => (bool) Arr::get($body, "paid", false),
            "payments" => $this->payments(Arr::get($body, "payments")),
        ];
    }

    /**
     * Holt die Auftragsbestätigung (PDF) einer bezahlten Bestellung, oder null.
     *
     * Der Keyserver rendert sie — {@see \App\Http\Controllers\OrderController}
     * reicht die Bytes nur durch, nachdem es die Zugehörigkeit geprüft hat.
     * `$locale` ist das MetaGer-Gebietsschema (`de-DE`); der Keyserver macht
     * daraus über `?lang=` dieselbe Sprachverhandlung wie für jede Seite.
     *
     * @return array{body: string, content_type: string}|null
     */
    public function confirmationPdf(string $publicId, string $locale): ?array
    {
        $response = $this->get(
            "/checkout/" . urlencode($publicId) . "/confirmation.pdf",
            ["lang" => $locale],
        );

        if ($response === null || !$response->successful()) {
            return null;
        }

        $contentType = $response->header("Content-Type");
        if (!is_string($contentType) || !str_starts_with($contentType, "application/pdf")) {
            Log::warning("keymanager confirmation PDF answered with " . ($contentType ?: "no content type"));

            return null;
        }

        return [
            "body" => $response->body(),
            "content_type" => "application/pdf",
        ];
    }

    /**
     * Requests a tax invoice ("Rechnung") for a paid checkout, or the
     * reason it could not be created.
     *
     * Idempotent on the keymanager side — a resubmit of the same form
     * (a reload, a slow connection) gets back the same receipt rather than
     * a second invoice, and this returns success either way; the caller
     * cannot tell the two apart and does not need to.
     *
     * @param array{
     *     company: string, first_name: string, last_name: string,
     *     address1: string, address2: string, zip: string, city: string,
     *     state: string
     * } $fields
     * @return array{ok: true}|array{ok: false, errors: list<string>}
     */
    public function requestInvoice(string $publicId, array $fields): array
    {
        try {
            $response = Http::timeout(8)
                ->withHeaders(["Authorization" => "Bearer " . config("metager.metager.keymanager.access_token")])
                ->post($this->keyserver . "/checkout/" . urlencode($publicId) . "/invoice", $fields);
        } catch (\Throwable $e) {
            Log::warning("keymanager invoice request unreachable: " . $e->getMessage());

            return ["ok" => false, "errors" => ["unreachable"]];
        }

        if ($response->successful()) {
            return ["ok" => true];
        }

        if ($response->status() === 422) {
            $errors = $response->json("errors");
            $fieldNames = [];
            if (is_array($errors)) {
                foreach ($errors as $error) {
                    $name = is_array($error) ? (Arr::get($error, "path") ?? Arr::get($error, "param")) : null;
                    if (is_string($name)) {
                        $fieldNames[] = $name;
                    }
                }
            }

            return ["ok" => false, "errors" => $fieldNames];
        }

        Log::warning("keymanager invoice request failed with status " . $response->status());

        return ["ok" => false, "errors" => ["unreachable"]];
    }

    /**
     * Requests a refund for a paid checkout — opens a Zammad support ticket
     * and credits the unused token balance back onto the key. The money
     * itself is a manual step staff take from that ticket afterwards; this
     * only reports whether the request itself went through.
     *
     * Idempotent on the keymanager side, the same way as
     * {@see requestInvoice()}: a resubmit after the balance is already
     * discharged answers `refund_already_requested` rather than discharging
     * twice, and this method treats that the same as success — the caller
     * cannot tell the two apart and does not need to.
     *
     * @return array{ok: true}|array{ok: false, error: string}
     */
    public function requestRefund(string $publicId, string $message): array
    {
        try {
            $response = Http::timeout(8)
                ->withHeaders(["Authorization" => "Bearer " . config("metager.metager.keymanager.access_token")])
                ->post($this->keyserver . "/checkout/" . urlencode($publicId) . "/refund", ["message" => $message]);
        } catch (\Throwable $e) {
            Log::warning("keymanager refund request unreachable: " . $e->getMessage());

            return ["ok" => false, "error" => "unreachable"];
        }

        if ($response->successful() || $response->status() === 409) {
            return ["ok" => true];
        }

        if ($response->status() === 403) {
            return ["ok" => false, "error" => "not_allowed"];
        }

        Log::warning("keymanager refund request failed with status " . $response->status());

        return ["ok" => false, "error" => "unreachable"];
    }

    /**
     * Holt die Rechnung (PDF) einer bezahlten Bestellung, oder null — wortgleich
     * zu {@see confirmationPdf()}, nur ein anderer Pfad, weil der Keyserver hier
     * je nach Zustand entweder InvoiceNinjas eigenes PDF weiterreicht oder eine
     * ältere, selbst gespeicherte Rechnung ausliefert.
     *
     * @return array{body: string, content_type: string}|null
     */
    public function invoicePdf(string $publicId): ?array
    {
        $response = $this->get("/checkout/" . urlencode($publicId) . "/invoice.pdf");

        if ($response === null || !$response->successful()) {
            return null;
        }

        $contentType = $response->header("Content-Type");
        if (!is_string($contentType) || !str_starts_with($contentType, "application/pdf")) {
            Log::warning("keymanager invoice PDF answered with " . ($contentType ?: "no content type"));

            return null;
        }

        return [
            "body" => $response->body(),
            "content_type" => "application/pdf",
        ];
    }

    /**
     * @param array<string, string> $query
     */
    private function get(string $path, array $query = []): ?\Illuminate\Http\Client\Response
    {
        try {
            return Http::timeout(8)
                ->withHeaders(["Authorization" => "Bearer " . config("metager.metager.keymanager.access_token")])
                ->get($this->keyserver . $path, $query);
        } catch (\Throwable $e) {
            Log::warning("keymanager order request unreachable: " . $e->getMessage());

            return null;
        }
    }

    /**
     * @param mixed $raw
     * @return list<array<string, mixed>>
     */
    private function payments(mixed $raw): array
    {
        if (!is_array($raw)) {
            return [];
        }

        $out = [];
        foreach ($raw as $payment) {
            if (!is_array($payment)) {
                continue;
            }

            $publicId = Arr::get($payment, "public_id");
            $net = Arr::get($payment, "net");
            $vat = Arr::get($payment, "vat");
            $gross = Arr::get($payment, "gross");
            $tokenCount = Arr::get($payment, "token_count");

            if (
                !is_string($publicId) || $publicId === ""
                || !is_numeric($net) || !is_numeric($vat) || !is_numeric($gross)
                || !is_numeric($tokenCount)
            ) {
                Log::warning("keymanager order lookup answered with an incomplete payment line");
                continue;
            }

            $convertedPrice = Arr::get($payment, "converted_price");
            $convertedCurrency = Arr::get($payment, "converted_currency");

            $out[] = [
                "public_id" => $publicId,
                "net" => (string) $net,
                "vat" => (string) $vat,
                "gross" => (string) $gross,
                "vat_rate" => (float) Arr::get($payment, "vat_rate", 0),
                "token_count" => (int) $tokenCount,
                "converted_price" => is_numeric($convertedPrice) ? (float) $convertedPrice : null,
                "converted_currency" => is_string($convertedCurrency) ? $convertedCurrency : null,
                "payment_processor" => is_string(Arr::get($payment, "payment_processor"))
                    ? $payment["payment_processor"]
                    : null,
                "created_at" => is_string(Arr::get($payment, "created_at")) ? $payment["created_at"] : null,
                "invoice_available" => (bool) Arr::get($payment, "invoice_available", false),
                "refund_available" => (bool) Arr::get($payment, "refund_available", false),
                "refund_token_count" => (int) Arr::get($payment, "refund_token_count", 0),
                "refund_amount" => (string) Arr::get($payment, "refund_amount", "0.00"),
            ];
        }

        return $out;
    }
}
