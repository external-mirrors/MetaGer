<?php

namespace App\Models\Membership;

use App\Localization;
use Arr;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use Cache;
use Lang;

class PayPal
{

    const INTENT_CAPTURE = "CAPTURE";
    const INTENT_AUTHORIZE = "AUTHORIZE";

    const API_METHOD_POST = "POST";
    const API_METHOD_PATCH = "PATCH";
    const API_METHOD_GET = "GET";
    const API_METHOD_DELETE = 'DELETE';

    public static function GET_ID(): string
    {
        return hash_hmac("sha256", config("metager.metager.paypal.membership.client_id"), config("metager.metager.paypal.membership.secret"));
    }

    public static function CREATE_ORDER(MembershipApplication $application): array|null
    {
        $order_data = self::CREATE_ORDER_DATA($application, self::INTENT_CAPTURE);
        return self::PAYPAL_REQUEST("/v2/checkout/orders", self::API_METHOD_POST, $order_data, true);
    }

    public static function CREATE_ORDER_DATA(MembershipApplication $application, $intent): array
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

        $description = Arr::get($order_data, "purchase_units.0.description");
        $custom_id = null;
        $invoice_id = null;
        $amount = null;
        $unit_amount = null;
        $quantity = 1;
        if ($application->crm_membership !== null) {
            $payments = CiviCrm::MEMBERSHIP_NEXT_PAYMENTS($application->crm_membership, count: 2);
            if ($payments === null)
                throw new Exception("Cannot load Payments");

            $amount = $payments[0]["amount"];
            $unit_amount = $amount;
            $payment_source = $application->payment_method;

            if (Arr::get($payments, "0.payment_interval_months", 0) * Arr::get($payments, "0.monthly", 0) === $amount) {
                $quantity = Arr::get($payments, "0.payment_interval_months");
                $unit_amount = Arr::get($payments, "0.monthly");
                $date_start = clone Arr::get($payments, "0.due_date");
                $date_end = clone Arr::get($payments, "1.due_date");
                $date_end->addMonths(-1);
                if ($date_start->diffInMonths($date_end, true) <= 0) {
                    $description .= " " . $date_end->format("M Y");
                } else {
                    $description .= " " . $date_start->format("M Y") . " - " . $date_end->format("M Y");
                }
            }
            $custom_id = $application->payment_reference;
            if ($intent === self::INTENT_CAPTURE) {
                Arr::set($order_data, "payment_source.$payment_source.vault_id", $application->paypal->vault_id);
                Arr::set($order_data, "payment_source.$payment_source.stored_credentials", [
                    "payment_initiator" => "MERCHANT",
                    "payment_type" => "RECURRING",
                ]);
            }
        } elseif ($application->payment_reference !== null && $application->amount !== null && $application->interval !== null) {
            $quantity = match ($application->interval) {
                "monthly" => 1,
                "quarterly" => 3,
                "six-monthly" => 6,
                "annual" => 12
            };
            $unit_amount = $application->amount;
            $amount = $quantity * $unit_amount;
            $custom_id = $application->payment_reference;
            $description .= " " . now()->format("M Y");
            if ($quantity > 1) {
                $description .= " - " . now()->addMonths($quantity - 1)->format("M Y");
            }
        } else {
            throw new Exception("Cannot create Order data for this application");
        }

        Arr::set($order_data, "purchase_units.0.description", $description);
        Arr::set($order_data, "purchase_units.0.items.0.name", $description);
        Arr::set($order_data, "purchase_units.0.custom_id", $custom_id);
        Arr::set($order_data, "purchase_units.0.amount.value", $amount);
        Arr::set($order_data, "purchase_units.0.amount.breakdown.item_total.value", $amount);
        Arr::set($order_data, "purchase_units.0.items.0.quantity", $quantity);
        Arr::set($order_data, "purchase_units.0.items.0.unit_amount.value", $unit_amount);
        return $order_data;
    }


    public static function CREATE_AUTHORIZE_ORDER(MembershipApplication $application, string $success_url, string $error_url): array|null
    {
        $order_data = self::CREATE_ORDER_DATA($application, self::INTENT_AUTHORIZE);

        if ($application->payment_method === "card") {
            Arr::set($order_data, "payment_source.{$application->payment_method}", [
                "attributes" => [
                    "vault" => [
                        "store_in_vault" => "ON_SUCCESS"
                    ],
                    "verification" => [
                        //"method" => "SCA_WHEN_REQUIRED"
                        "method" => "SCA_ALWAYS"
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
            Arr::set($order_data, "payment_source.{$application->payment_method}", [
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

    private static function PAYPAL_REQUEST(string $api_path, string $method = self::API_METHOD_POST, array|null $request_data, bool $return_errors = false): null|array
    {
        $resulthash = md5("paypal" . microtime(true));
        $mission = [
            "resulthash" => $resulthash,
            "url" => config("metager.metager.paypal.base_url") . $api_path,
            "useragent" => "MetaGer",
            "cacheDuration" => 0,   // We'll cache seperately
            "headers" => [
                "Authorization" => "Bearer " . self::GET_ACCESS_TOKEN(),
                "PayPal-Request-Id" => uuid_create(),
                "Prefer" => "return=representation"
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
        } else if ($method === self::API_METHOD_DELETE) {
            $mission["curlopts"][CURLOPT_CUSTOMREQUEST] = "DELETE";
        }
        $mission = json_encode($mission);
        Redis::rpush(\App\MetaGer::FETCHQUEUE_KEY, $mission);
        $results = Redis::brpop($resulthash, 10);
        if (!is_array($results))
            return null;
        $results = json_decode($results[1], true);
        if (!in_array($results["info"]["http_code"], [200, 201])) {
            if ($return_errors) {
                return [
                    "status_code" => $results["info"]["http_code"],
                    "response_body" => json_decode($results["body"], true)
                ];
            } else {
                return null;
            }
        }
        if ($return_errors) {
            return [
                "status_code" => $results["info"]["http_code"],
                "response_body" => json_decode($results["body"], true)
            ];
        } else {
            $body = json_decode($results["body"], true);
            return $body;
        }
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

    public static function VALIDATE_ORDER(string $order_id, array $order = null): array|string|null
    {
        if ($order === null) {
            $order = self::GET_ORDER($order_id);
        } else {
            $order_id = Arr::get($order, "id");
        }

        if ($order === null)
            return null;
        $payment_method = array_key_first(Arr::get($order, "payment_source", []));
        if ($payment_method === null)
            return null;

        if ($payment_method === "paypal")
            return $order;

        // If there already is a processor response we will validate it here aswell
        $payments = Arr::get($order, "purchase_units.0.payments");
        if ($payments !== null) {
            foreach (Arr::get($payments, "authorizations", []) as $authorization) {
                switch (Arr::get($authorization, "status")) {
                    case "DENIED":
                        return self::PARSE_PROCESSOR_RESPONSE_ERROR(Arr::get($authorization, "processor_response", self::INTENT_AUTHORIZE));
                }
            }
            foreach (Arr::get($payments, "captures", []) as $capture) {
                switch (Arr::get($capture, "status")) {
                    case "DECLINED":
                    case "FAILED":
                        return self::PARSE_PROCESSOR_RESPONSE_ERROR(Arr::get($capture, "processor_response"));
                }
            }
        }

        // Card Payment. Validate Authentication result if available
        $authentication_result = Arr::get($order, "payment_source.card.authentication_result");
        if ($authentication_result === null)
            return $order;

        $liability_shift = Arr::get($authentication_result, "liability_shift");
        $enrollment_status = Arr::get($authentication_result, "three_d_secure.enrollment_status");
        $authentication_status = Arr::get($authentication_result, "three_d_secure.authentication_status");
        // Validate authentication result => https://developer.paypal.com/docs/checkout/advanced/customize/3d-secure/response-parameters/
        if ($enrollment_status === "Y") {
            if ($authentication_status === "Y") {
                if (in_array($liability_shift, ["POSSIBLE", "YES"])) {
                    return $order;
                }
            } elseif ($authentication_status === "N") {
                if ($liability_shift === "NO")
                    return __("spende.execute-payment.card.error.generic");
            } elseif ($authentication_status === "R") {
                if ($liability_shift === "NO")
                    return __("spende.execute-payment.card.error.generic");
            } elseif ($authentication_status === "A") {
                if ($liability_shift === "POSSIBLE")
                    return $order;
            } elseif ($authentication_status === "U") {
                if (in_array($liability_shift, ["UNKNOWN", "NO"]))
                    return __("spende.execute-payment.card.error.try_again");
            } elseif ($authentication_status === "C") {
                if ($liability_shift === "UNKNOWN")
                    return __("spende.execute-payment.card.error.try_again");
            } else {
                if ($liability_shift === "NO")
                    return __("spende.execute-payment.card.error.try_again");
            }
        } elseif ($enrollment_status === "N") {
            if ($liability_shift === "NO")
                return $order;
        } elseif ($enrollment_status === "U") {
            if ($liability_shift === "NO")
                return $order;
            elseif ($liability_shift === "UNKNOWN")
                return __("spende.execute-payment.card.error.try_again");
        } elseif ($enrollment_status === "B") {
            if ($liability_shift === "NO")
                return $order;
        } else {
            if ($liability_shift === "UNKNOWN")
                return __("spende.execute-payment.card.error.try_again");
        }
        return $order;
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

    public static function DELETE_PAYMENT_TOKEN(string $payment_token)
    {
        return self::PAYPAL_REQUEST("/v3/vault/payment-tokens/$payment_token", self::API_METHOD_DELETE, null);
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

    private static function PARSE_PROCESSOR_RESPONSE_ERROR(array $processor_response, string $intent = self::INTENT_CAPTURE): string|null
    {
        $response_code = Arr::get($processor_response, "response_code");
        if ($response_code !== null) {
            if (Lang::has("spende.execute-payment.card.error.{$response_code}")) {
                return __("spende.execute-payment.card.error.declined_reason", ["reason" => __("spende.execute-payment.card.error.{$response_code}")]);
            } else {
                return __("spende.execute-payment.card.error.generic");
            }
        } else {
            if ($intent === self::INTENT_CAPTURE) {
                return __("spende.execute-payment.errors.capture_failed");
            } else {
                return __("spende.execute-payment.errors.authorization_denied");
            }
        }
    }
}