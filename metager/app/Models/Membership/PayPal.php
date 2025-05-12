<?php

namespace App\Models\Membership;

use App\Localization;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use Cache;

class PayPal
{

    public static function GET_ID(): string
    {
        return hash_hmac("sha256", config("metager.metager.paypal.membership.client_id"), config("app.key"));
    }

    public static function CREATE_ORDER(string $civicrm_membership_id): array|null
    {
        $membership = CiviCrm::FIND_MEMBERSHIPS(null, $civicrm_membership_id);
        if ($membership === null || sizeof($membership) === 0) {
            return null;
        } else {
            $membership = $membership[0];
        }
        $payments = CiviCrm::MEMBERSHIP_NEXT_PAYMENTS($civicrm_membership_id, count: 2);
        if ($payments === null)
            return null;


        $contribution = CiviCrm::CREATE_MEMBERSHIP_PAYPAL_CONTRIBUTION($civicrm_membership_id);

        $resulthash = md5("paypal" . microtime(true));

        $amount = $payments[0]["amount"];

        $payment_source = match ($membership["Beitrag.Zahlungsweise:label"]) {
            "PayPal" => "paypal",
        };

        $quantity = 1;
        $unit_amount = $amount;

        $description = "SUMA-EV Mitgliedsbeitrag";
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
                CURLOPT_POSTFIELDS => json_encode([
                    "purchase_units" => [
                        [
                            "description" => $description,
                            "soft_descriptor" => "Mitgliedsbeitrag",
                            "custom_id" => $membership["Beitrag.Zahlungsreferenz"],
                            "invoice_id" => "contribution_" . $contribution,
                            "amount" => [
                                "currency_code" => "EUR",
                                "value" => $amount,
                                "breakdown" => [
                                    "item_total" => [
                                        "currency_code" => "EUR",
                                        "value" => $amount
                                    ],
                                    "tax_total" => [
                                        "currency_code" => "EUR",
                                        "value" => 0
                                    ]
                                ]
                            ],
                            "items" => [
                                [
                                    "name" => $description,
                                    "quantity" => $quantity,
                                    "category" => "DIGITAL_GOODS",
                                    "unit_amount" => [
                                        "currency_code" => "EUR",
                                        "value" => $unit_amount
                                    ],
                                    "tax" => [
                                        "currency_code" => "EUR",
                                        "value" => 0
                                    ]
                                ]
                            ]
                        ]
                    ],
                    "intent" => "CAPTURE",
                    "payment_source" => [
                        $payment_source => [
                            "vault_id" => $membership["Beitrag.PayPal_Vault"]
                        ]
                    ]
                ]),
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


    public static function CREATE_AUTHORIZE_ORDER(string $payment_source, float $monthly_amount, int $number_of_months, string $vault_description, string $error_url, string $success_url, int $membership_tmpid): array|null
    {
        $resulthash = md5("paypal" . microtime(true));

        $amount = $monthly_amount * $number_of_months;

        $vault_description = "SUMA-EV Mitgliedsbeitrag - Fällig im gewählten Zahlungsintervall.";

        $error_url = route("membership_form", request()->except(["reduction", "_token"]));
        $parameters = ["id" => $membership_tmpid, "error_url" => $error_url, "success_url" => $success_url, "expires_at" => now()->addHours(3)->timestamp];
        $parameters["signature"] = hash_hmac("sha256", json_encode($parameters), config("app.key"));
        $success_url = route("membership_paypal_authorized", $parameters);


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
                CURLOPT_POSTFIELDS => json_encode([
                    "purchase_units" => [
                        [
                            "description" => "SUMA-EV Mitgliedsbeitrag",
                            "soft_descriptor" => "Mitgliedsbeitrag",
                            "custom_id" => "pending_$membership_tmpid",
                            "invoice_id" => "pending_$membership_tmpid",
                            "amount" => [
                                "currency_code" => "EUR",
                                "value" => $amount,
                                "breakdown" => [
                                    "item_total" => [
                                        "currency_code" => "EUR",
                                        "value" => $amount
                                    ],
                                    "tax_total" => [
                                        "currency_code" => "EUR",
                                        "value" => 0
                                    ]
                                ]
                            ],
                            "items" => [
                                [
                                    "name" => "SUMA-EV Mitgliedsbeitrag",
                                    "quantity" => $number_of_months,
                                    "category" => "DIGITAL_GOODS",
                                    "unit_amount" => [
                                        "currency_code" => "EUR",
                                        "value" => $monthly_amount
                                    ],
                                    "tax" => [
                                        "currency_code" => "EUR",
                                        "value" => 0
                                    ]
                                ]
                            ]
                        ]
                    ],
                    "intent" => "AUTHORIZE",
                    "payment_source" => [
                        $payment_source => [
                            "attributes" => [
                                "vault" => [
                                    "store_in_vault" => "ON_SUCCESS",
                                    "usage_type" => "MERCHANT",
                                    "usage_pattern" => "SUBSCRIPTION_PREPAID",
                                    "description" => $vault_description
                                ]
                            ],
                            "experience_context" => [
                                "return_url" => $success_url,
                                "cancel_url" => $error_url,
                                "shipping_preference" => "NO_SHIPPING",
                                "locale" => Localization::getLanguage() . "-" . Localization::getRegion()
                            ]
                        ]
                    ]
                ]),
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

    public static function AUTHORIZE_ODER(string $order_id)
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