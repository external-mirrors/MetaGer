<?php

namespace App\Authentication;

use App\Support\AppHosts;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * PayPal — the one checkout method whose page is SDK-driven from the
 * browser rather than a plain form POST. `show()`/`createOrder()`/
 * `captureOrder()` mirror keymanager's own three JSON endpoints exactly, in
 * the order the client-side script (resources/js/checkout-paypal.js) calls
 * them: config first (page render), then create (the SDK's `createOrder`
 * callback), then capture (`onApprove`).
 *
 * This class never sees `payments.paypal.secret` or any PayPal API
 * response — only what keymanager hands back: a public client id, an
 * order id to pass to the SDK, and (on capture) MetaGer's own landing URL.
 */
final class PayPalChargeIssuer
{
    public const FUNDING_SOURCES = ["paypal", "card", "p24", "bancontact", "blik", "eps", "mybank"];

    private string $keyserver;

    public function __construct()
    {
        $keyserver = config("metager.metager.keymanager.server") ?: config("app.url") . "/keys";
        $this->keyserver = $keyserver . "/api/json";
    }

    /**
     * The SDK config a checkout page needs to initialize itself.
     *
     * @return array{client_id: string, direct_card_enabled: bool, client_token: ?string}|null
     */
    public function show(Request $request, string $key, string $fundingSource): ?array
    {
        try {
            $response = Http::timeout(5)
                ->withHeaders($this->headers($request))
                ->get($this->keyserver . "/key/" . urlencode($key) . "/checkout/paypal/" . urlencode($fundingSource));
        } catch (\Throwable $e) {
            Log::warning("keymanager paypal config unreachable: " . $e->getMessage());

            return null;
        }

        if (!$response->successful()) {
            Log::warning("keymanager paypal config answered " . $response->status());

            return null;
        }

        $body = $response->json();
        $body = is_array($body) ? $body : [];

        $clientId = Arr::get($body, "client_id");
        if (!is_string($clientId) || $clientId === "") {
            Log::warning("keymanager paypal config answered without a client_id");

            return null;
        }

        return [
            "client_id" => $clientId,
            "direct_card_enabled" => Arr::get($body, "direct_card_enabled") === true,
            "client_token" => is_string(Arr::get($body, "client_token")) ? $body["client_token"] : null,
        ];
    }

    /**
     * `return_origin` rides along here so keymanager stores it on the order at
     * creation time: PayPal's capture response and its async webhook both hand
     * back a "back to MetaGer" URL, and the keymanager only knows the host it
     * was called on server-to-server, not the one the user is on
     * ({@see \App\Support\AppHosts::currentOrigin()}). Null lets the keymanager
     * fall back to its own configured MetaGer URL.
     *
     * @return array{public_id: string, paypal_order_id: string}|null
     */
    public function createOrder(Request $request, string $key, int $amount, string $fundingSource): ?array
    {
        try {
            $response = Http::timeout(5)
                ->withHeaders($this->headers($request))
                ->post(
                    $this->keyserver . "/key/" . urlencode($key) . "/checkout/paypal/" . urlencode($fundingSource) . "/order/create",
                    array_filter([
                        "amount" => $amount,
                        "return_origin" => AppHosts::currentOrigin($request),
                    ], fn ($value) => $value !== null)
                );
        } catch (\Throwable $e) {
            Log::warning("keymanager paypal order/create unreachable: " . $e->getMessage());

            return null;
        }

        if (!$response->successful()) {
            Log::warning("keymanager paypal order/create answered " . $response->status());

            return null;
        }

        $body = $response->json();
        $body = is_array($body) ? $body : [];

        $publicId = Arr::get($body, "public_id");
        $paypalOrderId = Arr::get($body, "paypal_order_id");

        if (!is_string($publicId) || $publicId === "" || !is_string($paypalOrderId) || $paypalOrderId === "") {
            Log::warning("keymanager paypal order/create answered with an incomplete order");

            return null;
        }

        return [
            "public_id" => $publicId,
            "paypal_order_id" => $paypalOrderId,
        ];
    }

    /**
     * Relays keymanager's capture response almost verbatim — status and
     * body — because the client JS's own error-code mapping
     * (`processor_response.response_code`, `error.details[i].description`,
     * the "3DS" string match) depends on seeing PayPal's real error shape,
     * not a repackaged one. `redirect_url` on success is already MetaGer's
     * own `abschluss` URL, built server-side by keymanager.
     *
     * @return array{status: int, body: mixed}
     */
    public function captureOrder(Request $request, string $key, string $fundingSource, string $paymentReference): array
    {
        try {
            $response = Http::timeout(10)
                ->withHeaders($this->headers($request))
                ->post(
                    $this->keyserver . "/key/" . urlencode($key) . "/checkout/paypal/" . urlencode($fundingSource) . "/order/capture",
                    ["payment_reference" => $paymentReference]
                );
        } catch (\Throwable $e) {
            Log::warning("keymanager paypal order/capture unreachable: " . $e->getMessage());

            return ["status" => 502, "body" => ["errors" => [["msg" => "unreachable"]]]];
        }

        return ["status" => $response->status(), "body" => $response->json()];
    }

    /**
     * @return array<string, string>
     */
    private function headers(Request $request): array
    {
        return [
            "Authorization" => "Bearer " . config("metager.metager.keymanager.access_token"),
            // PaypalDirectCardMode's rate limiter keys off this — keymanager
            // is called server-to-server here, so its own req.ip would be
            // MetaGer's pod IP for every visitor. Trusting this header is
            // safe specifically because the only caller of these keymanager
            // endpoints is MetaGer itself, behind that bearer token.
            "X-User-Ip" => $request->ip() ?? "",
        ];
    }
}
