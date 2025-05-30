<?php

namespace App\Models\Membership;

use App\Localization;
use Arr;
use DB;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use Cache;

class PayPal
{

    const INTENT_CAPTURE = "CAPTURE";
    const INTENT_AUTHORIZE = "AUTHORIZE";

    const API_METHOD_POST = "POST";
    const API_METHOD_PATCH = "PATCH";
    const API_METHOD_GET = "GET";

    public static function GET_ID(): string
    {
        return hash_hmac("sha256", config("metager.metager.paypal.membership.client_id"), config("app.key"));
    }

    public static function CREATE_ORDER(string $civicrm_membership_id): array|null
    {
        $order_data = self::CREATE_ORDER_DATA($civicrm_membership_id, self::INTENT_CAPTURE);

        $resulthash = md5("paypal" . microtime(true));

        $mission = [
            "resulthash" => $resulthash,
            "url" => config("metager.metager.paypal.base_url") . "/v2/checkout/orders",
            "useragent" => "MetaGer",
            "cacheDuration" => 0,   // We'll cache seperately
            "headers" => [
                "Content-Type" => "application/json",
                "Authorization" => "Bearer " . self::GET_ACCESS_TOKEN(),
                "PayPal-Request-Id" => uuid_create()
            ],
            "name" => "PayPal",
            "curlopts" => [
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => json_encode($order_data),
            ]
        ];
        $mission = json_encode($mission);
        Redis::rpush(\App\MetaGer::FETCHQUEUE_KEY, $mission);
        $results = Redis::brpop($resulthash, 10);
        if (!is_array($results))
            return null;
        $results = json_decode($results[1], true);
        if (!in_array($results["info"]["http_code"], [200, 201])) {
            return null;
        }
        $body = json_decode($results["body"], true);
        return $body;
    }

    public static function CREATE_ORDER_DATA(string $civicrm_membership_id, $intent): array
    {
        $order_data = [
            "purchase_units" => [
                [
                    "description" => __("membership/order.default_description"),
                    "soft_descriptor" => __("membership/order.default_softdescription"),
                    "custom_id" => "pending",
                    "invoice_id" => "pending",
                    "amount" => [
                        "currency_code" => "EUR",
                        "value" => 0,
                        "breakdown" => [
                            "item_total" => [
                                "currency_code" => "EUR",
                                "value" => 0
                            ],
                            "tax_total" => [
                                "currency_code" => "EUR",
                                "value" => 0
                            ]
                        ]
                    ],
                    "items" => [
                        [
                            "name" => __("membership/order.default_description"),
                            "quantity" => 0,
                            "category" => "DIGITAL_GOODS",
                            "unit_amount" => [
                                "currency_code" => "EUR",
                                "value" => 0
                            ],
                            "tax" => [
                                "currency_code" => "EUR",
                                "value" => 0
                            ]
                        ]
                    ]
                ]
            ],
            "intent" => $intent,
        ];

        $membership = CiviCrm::FIND_MEMBERSHIPS(null, $civicrm_membership_id);
        if ($membership === null || sizeof($membership) === 0) {
            throw new Exception("Cannot find membership");
        } else {
            $membership = $membership[0];
        }
        $payments = CiviCrm::MEMBERSHIP_NEXT_PAYMENTS($civicrm_membership_id, count: 2);
        if ($payments === null)
            throw new Exception("Cannot load Payments");


        $contribution = CiviCrm::CREATE_MEMBERSHIP_PAYPAL_CONTRIBUTION($civicrm_membership_id);

        $amount = $payments[0]["amount"];

        $payment_source = match ($membership["Beitrag.Zahlungsweise:label"]) {
            "PayPal" => "paypal",
            "Creditcard" => "card"
        };

        $quantity = 1;
        $unit_amount = $amount;

        $description = "";
        if ($payments[0]["payment_interval_months"] * $payments[0]["monthly"] === $amount) {
            $quantity = $payments[0]["payment_interval_months"];
            $unit_amount = $payments[0]["monthly"];

            $date_start = clone $payments[0]["due_date"];
            $date_end = clone $payments[1]["due_date"];
            $date_end->addMonths(-1);
            if ($date_start->diffInMonths($date_end, true) <= 0) {
                $description .= " " . $date_end->format("M Y");
            } else {
                $description .= " " . $date_start->format("M Y") . " - " . $date_end->format("M Y");
            }
        }
        Arr::set($order_data, "purchase_units.0.description", Arr::get($order_data, "purchase_units.0.description") . $description);
        Arr::set($order_data, "purchase_units.0.items.0.name", Arr::get($order_data, "purchase_units.0.description") . $description);
        Arr::set($order_data, "purchase_units.0.custom_id", $membership["Beitrag.Zahlungsreferenz"]);
        Arr::set($order_data, "purchase_units.0.invoice_id", "contribution_" . $contribution);
        Arr::set($order_data, "purchase_units.0.amount.value", $amount);
        Arr::set($order_data, "purchase_units.0.amount.breakdown.item_total.value", $amount);
        Arr::set($order_data, "purchase_units.0.items.0.quantity", $quantity);
        Arr::set($order_data, "purchase_units.0.items.0.unit_amount.value", $unit_amount);
        Arr::set($order_data, "payment_source.$payment_source.vault_id", $membership["Beitrag.PayPal_Vault"]);
        return $order_data;
    }


    public static function CREATE_AUTHORIZE_ORDER(string $civicrm_membership_id, string $payment_source, string $success_url, string $error_url): array|null
    {
        $order_data = self::CREATE_ORDER_DATA($civicrm_membership_id, self::INTENT_AUTHORIZE);

        if ($payment_source === "card") {
            Arr::set($order_data, "payment_source.$payment_source", [
                "attributes" => [
                    "vault" => [
                        "store_in_vault" => "ON_SUCCESS"
                    ],
                    "verification" => [
                        "method" => "SCA_WHEN_REQUIRED"
                    ]
                ],
                "stored_credentials" => [
                    "payment_initiator" => "CUSTOMER",
                    "payment_type" => "RECURRING",
                    "usage" => "FIRST"
                ],
                "experience_context" => [
                    "return_url" => $success_url,
                    "cancel_url" => $error_url,
                ]
            ]);
        } else {
            Arr::set($order_data, "payment_source.$payment_source", [
                "attributes" => [
                    "vault" => [
                        "store_in_vault" => "ON_SUCCESS",
                        "usage_type" => "MERCHANT",
                        "usage_pattern" => "SUBSCRIPTION_PREPAID",
                        "description" => __("membership/order.vault.description")
                    ]
                ],
                "experience_context" => [
                    "return_url" => $success_url,
                    "cancel_url" => $error_url,
                    "shipping_preference" => "NO_SHIPPING",
                    "locale" => Localization::getLanguage() . "-" . Localization::getRegion()
                ]
            ]);
        }
        return self::PAYPAL_REQUEST("/v2/checkout/orders", self::API_METHOD_POST, $order_data);
    }

    public static function CAPTURE_PAYMENT(string $authorization_id)
    {
        return self::PAYPAL_REQUEST("/v2/payments/authorizations/{$authorization_id}/capture", self::API_METHOD_POST, []);
    }

    public static function UPDATE_AUTHORIZED_ORDER(string $civicrm_membership_id)
    {
        $order_id = DB::table("membership")
            ->leftJoin("membership_paypal", "membership_paypal.id", "=", "membership.paypal")
            ->where("civicrm_membership_id", "=", $civicrm_membership_id)->firstOrFail("order_id")->order_id;
        $order_data = self::CREATE_ORDER_DATA($civicrm_membership_id, self::INTENT_CAPTURE);

        $patch_data = [];
        $patch_data[] = [
            "op" => "replace",
            "path" => "/purchase_units/@reference_id=='default'/description",
            "value" => Arr::get($order_data, "purchase_units.0.description")
        ];
        $order = self::GET_ORDER($order_id);
        if (self::PAYPAL_REQUEST("/v2/checkout/orders/$order_id", self::API_METHOD_PATCH, $patch_data) === null) {
            throw new Exception("Couldn't patch PayPal Order");
        }
    }

    private static function PAYPAL_REQUEST(string $api_path, string $method = self::API_METHOD_POST, array|null $request_data): null|array
    {
        $resulthash = md5("paypal" . microtime(true));
        $mission = [
            "resulthash" => $resulthash,
            "url" => config("metager.metager.paypal.base_url") . $api_path,
            "useragent" => "MetaGer",
            "cacheDuration" => 0,   // We'll cache seperately
            "headers" => [
                "Authorization" => "Bearer " . self::GET_ACCESS_TOKEN(),
                "PayPal-Request-Id" => uuid_create()
            ],
            "name" => "PayPal",
            "curlopts" => []
        ];
        if ($method === self::API_METHOD_POST) {
            $mission["headers"]["Content-Type"] = "application/json";
            $mission["curlopts"][CURLOPT_POST] = true;
            if (!empty($request_data)) {
                $mission["curlopts"][CURLOPT_POSTFIELDS] = json_encode($request_data);
            } else {
                $mission["curlopts"][CURLOPT_POSTFIELDS] = json_encode($request_data, JSON_FORCE_OBJECT);
            }
        } else if ($method === self::API_METHOD_PATCH) {
            $mission["headers"]["Content-Type"] = "application/json";
            $mission["curlopts"][CURLOPT_CUSTOMREQUEST] = "PATCH";
            if (!empty($request_data)) {
                $mission["curlopts"][CURLOPT_POSTFIELDS] = json_encode($request_data);
            } else {
                $mission["curlopts"][CURLOPT_POSTFIELDS] = json_encode($request_data, JSON_FORCE_OBJECT);
            }
        }
        $mission = json_encode($mission);
        Redis::rpush(\App\MetaGer::FETCHQUEUE_KEY, $mission);
        $results = Redis::brpop($resulthash, 10);
        if (!is_array($results))
            return null;
        $results = json_decode($results[1], true);
        if (!in_array($results["info"]["http_code"], [200, 201])) {
            return null;
        }
        $body = json_decode($results["body"], true);
        return $body;
    }

    public static function VALIDATE_WEBHOOK(Request $request)
    {
        // Verify Webhook
        $resulthash = md5("paypal:webhook:" . $request->header("PAYPAL-CERT-URL"));
        $certificate = Cache::get($resulthash);
        if ($certificate === null) {
            $mission = [
                "resulthash" => $resulthash,
                "url" => $request->header("PAYPAL-CERT-URL"),
                "useragent" => "MetaGer",
                "cacheDuration" => 60,   // We'll cache seperately
                "name" => "PayPal",
            ];
            $mission = json_encode($mission);
            Redis::rpush(\App\MetaGer::FETCHQUEUE_KEY, $mission);
            $results = Redis::brpop($resulthash, 10);
            if (!is_array($results))
                abort(400);
            $results = json_decode($results[1], true);
            if ($results["info"]["http_code"] !== 200) {
                abort(400);
            }
            $certificate = $results["body"];
        }

        $webhook_validation = openssl_verify(
            data: implode(separator: "|", array: [
                $request->header("PAYPAL-TRANSMISSION-ID"),
                $request->header("PAYPAL-TRANSMISSION-TIME"),
                config("metager.metager.paypal.membership.webhook_id"),
                crc32($request->getContent())
            ]),
            signature: base64_decode($request->header("PAYPAL-TRANSMISSION-SIG")),
            public_key: $certificate,
            algorithm: "sha256WithRSAEncryption"
        );
        if ($webhook_validation !== 1)
            return false;
        return true;
    }

    public static function GET_ORDER(string $order_id)
    {
        $resulthash = md5("paypal:order" . microtime(true));

        $mission = [
            "resulthash" => $resulthash,
            "url" => config("metager.metager.paypal.base_url") . "/v2/checkout/orders/$order_id",
            "useragent" => "MetaGer",
            "cacheDuration" => 0,   // We'll cache seperately
            "headers" => [
                "Content-Type" => "application/json",
                "Authorization" => "Bearer " . self::GET_ACCESS_TOKEN(),
            ],
            "name" => "PayPal",
        ];
        $mission = json_encode($mission);
        Redis::rpush(\App\MetaGer::FETCHQUEUE_KEY, $mission);
        $results = Redis::brpop($resulthash, 10);
        if (!is_array($results))
            return null;
        $results = json_decode($results[1], true);
        if ($results["info"]["http_code"] !== 200) {
            return null;
        }
        $body = json_decode($results["body"], true);

        return $body;
    }

    public static function AUTHORIZE_ORDER(string $order_id)
    {
        $resulthash = md5("paypal:order" . microtime(true));

        $mission = [
            "resulthash" => $resulthash,
            "url" => config("metager.metager.paypal.base_url") . "/v2/checkout/orders/$order_id/authorize",
            "useragent" => "MetaGer",
            "cacheDuration" => 0,   // We'll cache seperately
            "headers" => [
                "Content-Type" => "application/json",
                "PayPal-Request-Id" => uuid_create(),
                "Authorization" => "Bearer " . self::GET_ACCESS_TOKEN(),
            ],
            "name" => "PayPal",
            "curlopts" => [
                CURLOPT_POST => true
            ]
        ];
        $mission = json_encode($mission);
        Redis::rpush(\App\MetaGer::FETCHQUEUE_KEY, $mission);
        $results = Redis::brpop($resulthash, 10);
        if (!is_array($results))
            return null;
        $results = json_decode($results[1], true);
        if (!in_array($results["info"]["http_code"], [200, 201])) {
            return null;
        }
        $body = json_decode($results["body"], true);

        return $body;
    }

    public static function REAUTHORIZE_ORDER(string $authorization_id)
    {
        $resulthash = md5("paypal:order" . microtime(true));

        $mission = [
            "resulthash" => $resulthash,
            "url" => config("metager.metager.paypal.base_url") . "/v2/payments/authorizations/$authorization_id/reauthorize",
            "useragent" => "MetaGer",
            "cacheDuration" => 0,   // We'll cache seperately
            "headers" => [
                "Content-Type" => "application/json",
                "PayPal-Request-Id" => uuid_create(),
                "Authorization" => "Bearer " . self::GET_ACCESS_TOKEN(),
            ],
            "name" => "PayPal",
            "curlopts" => [
                CURLOPT_POST => true
            ]
        ];
        $mission = json_encode($mission);
        Redis::rpush(\App\MetaGer::FETCHQUEUE_KEY, $mission);
        $results = Redis::brpop($resulthash, 10);
        if (!is_array($results))
            return null;
        $results = json_decode($results[1], true);
        if (!in_array($results["info"]["http_code"], [200, 201])) {
            return null;
        }
        $body = json_decode($results["body"], true);

        return $body;
    }

    public static function VOID_AUTHORIZATION(string $authorization_id)
    {
        $resulthash = md5("paypal:order" . microtime(true));

        $mission = [
            "resulthash" => $resulthash,
            "url" => config("metager.metager.paypal.base_url") . "/v2/payments/authorizations/$authorization_id/void",
            "useragent" => "MetaGer",
            "cacheDuration" => 0,   // We'll cache seperately
            "headers" => [
                "Content-Type" => "application/json",
                "PayPal-Request-Id" => uuid_create(),
                "Prefer" => "return=representation",
                "Authorization" => "Bearer " . self::GET_ACCESS_TOKEN(),
            ],
            "name" => "PayPal",
            "curlopts" => [
                CURLOPT_POST => true
            ]
        ];
        $mission = json_encode($mission);
        Redis::rpush(\App\MetaGer::FETCHQUEUE_KEY, $mission);
        $results = Redis::brpop($resulthash, 10);
        if (!is_array($results))
            return false;
        $results = json_decode($results[1], true);
        if (!in_array($results["info"]["http_code"], [200, 204, 404])) {
            return false;
        }

        return true;
    }

    private static function GET_ACCESS_TOKEN(): string|null
    {
        $cache_key = "paypal:membership:access_token";
        $access_token = Cache::get($cache_key);
        if ($access_token !== null)
            return $access_token;
        $resulthash = md5("paypal" . microtime(true));
        $mission = [
            "resulthash" => $resulthash,
            "url" => config("metager.metager.paypal.base_url") . "/v1/oauth2/token",
            "useragent" => "MetaGer",
            "cacheDuration" => 0,   // We'll cache seperately
            "headers" => [
                "Content-Type" => "application/xwww-form-urlencoded"
            ],
            "name" => "PayPal",
            "curlopts" => [
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => "grant_type=client_credentials",
                CURLOPT_USERNAME => config("metager.metager.paypal.membership.client_id"),
                CURLOPT_PASSWORD => config("metager.metager.paypal.membership.secret")
            ]
        ];
        $mission = json_encode($mission);
        Redis::rpush(\App\MetaGer::FETCHQUEUE_KEY, $mission);
        $results = Redis::brpop($resulthash, 10);
        if (!is_array($results))
            return null;
        $results = json_decode($results[1], true);
        if ($results["info"]["http_code"] !== 200) {
            return null;
        }
        $body = json_decode($results["body"], true);
        $access_token = $body["access_token"];
        $expires_in = max($body["expires_in"] - 10, 0);
        Cache::put($cache_key, $access_token, now()->addSeconds($expires_in));
        return $access_token;
    }
}