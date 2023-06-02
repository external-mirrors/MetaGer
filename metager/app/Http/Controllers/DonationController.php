<?php

namespace App\Http\Controllers;

use App\Localization;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use LaravelLocalization;
use Illuminate\Support\Facades\Validator;

class DonationController extends Controller
{
    function amount(Request $request)
    {
        if ($request->filled("amount")) {
            return redirect(LaravelLocalization::getLocalizedUrl(null, '/spende/' . $request->input('amount')));
        }
        return view('spende.amount')
            ->with('title', trans('titles.spende'))
            ->with('css', [mix('/css/spende.css')])
            ->with('darkcss', [mix('/css/spende-dark.css')])
            ->with('js', [mix('/js/donation.js')])
            ->with('navbarFocus', 'foerdern');
    }

    function interval(Request $request, $amount)
    {
        $validator = Validator::make(["amount" => $amount], [
            'amount' => 'required|numeric|min:1'
        ]);
        if ($validator->fails()) {
            return redirect(LaravelLocalization::getLocalizedUrl(null, '/spende'));
        } else {
            $amount = round(floatval($amount), 2);
        }
        return view('spende.interval')
            ->with('donation', [
                "amount" => $amount
            ])
            ->with('title', trans('titles.spende'))
            ->with('css', [mix('/css/spende.css')])
            ->with('darkcss', [mix('/css/spende-dark.css')])
            ->with('js', [mix('/js/donation.js')]);
    }

    function paymentMethod(Request $request, $amount, $interval)
    {
        $validator = Validator::make(["amount" => $amount, "interval" => $interval], [
            'amount' => 'required|numeric|min:1',
            'interval' => Rule::in(["once", "monthly", "quarterly", "six-monthly", "annual"])
        ]);
        if ($validator->fails()) {
            $failedParams = $validator->failed();
            if (array_key_exists("amount", $failedParams)) {
                return redirect(LaravelLocalization::getLocalizedUrl(null, '/spende'));
            } else {
                return redirect(LaravelLocalization::getLocalizedUrl(null, '/spende/' . $amount));
            }
        } else {
            $donation = [
                "amount" => round(floatval($amount), 2),
                "interval" => $interval
            ];
        }

        $script_params = [
            "client-id" => config("metager.metager.paypal.client_id"),
            "components" => "buttons,funding-eligibility,marks"
        ];

        if ($interval !== "once") {
            $script_params["vault"] = "true";
            $script_params["intent"] = "subscription";
        }

        $paypal_sdk = "https://www.paypal.com/sdk/js";

        $paypal_sdk .= "?" . http_build_query($script_params);
        $nonce = time();
        $csp = "default-src 'self'; script-src 'self' 'nonce-$nonce'; script-src-elem 'self' 'nonce-$nonce'; script-src-attr 'self'; style-src 'self'; style-src-elem 'self' 'unsafe-inline'; style-src-attr 'self'; img-src 'self' www.paypalobjects.com data:; font-src 'self'; connect-src 'self'; frame-src 'self'; frame-ancestors 'self'; form-action 'self' www.paypal.com";

        return response(view('spende.paymentMethod')
            ->with('donation', $donation)
            ->with('nonce', $nonce)
            ->with('paypal_sdk', $paypal_sdk)
            ->with('title', trans('titles.spende'))
            ->with('css', [mix('/css/spende.css')])
            ->with('darkcss', [mix('/css/spende-dark.css')])
            ->with('js', [mix('/js/donation.js')]), 200, ["Content-Security-Policy" => $csp]);
    }

    function paypalPayment(Request $request, $amount, $interval, $funding_source)
    {
        $validator = Validator::make(["amount" => $amount, "interval" => $interval], [
            'amount' => 'required|numeric|min:1',
            'interval' => Rule::in(["once", "monthly", "quarterly", "six-monthly", "annual"])
        ]);
        if ($validator->fails()) {
            $failedParams = $validator->failed();
            if (array_key_exists("amount", $failedParams)) {
                return redirect(LaravelLocalization::getLocalizedUrl(null, '/spende'));
            } else {
                return redirect(LaravelLocalization::getLocalizedUrl(null, '/spende/' . $amount));
            }
        } else {
            $donation = [
                "amount" => round(floatval($amount), 2),
                "interval" => $interval,
                "funding_source" => $funding_source
            ];
        }

        $script_params = [
            "client-id" => config("metager.metager.paypal.client_id"),
            "currency" => "EUR",
            "components" => "buttons,funding-eligibility,hosted-fields,payment-fields,marks"
        ];

        if ($interval !== "once") {
            $script_params["vault"] = "true";
            $script_params["intent"] = "subscription";
            if (Localization::getLanguage() === "de") {
                $lang = "de";
            } else {
                $lang = "en";
            }
            $donation["plan_id"] = config("metager.metager.paypal.subscription_plans.$lang.$interval");
        }

        $paypal_sdk = "https://www.paypal.com/sdk/js";

        $paypal_sdk .= "?" . http_build_query($script_params);
        $nonce = time();
        $csp = "default-src 'self'; script-src 'self' 'nonce-$nonce'; script-src-elem 'self' 'nonce-$nonce'; script-src-attr 'self'; style-src 'self'; style-src-elem 'self' 'unsafe-inline'; style-src-attr 'self'; img-src 'self' www.paypalobjects.com data:; font-src 'self'; connect-src 'self'; frame-src 'self' www.paypal.com www.sandbox.paypal.com; frame-ancestors 'self'; form-action 'self' www.paypal.com";

        return response(view('spende.payment.paypal')
            ->with('donation', $donation)
            ->with('nonce', $nonce)
            ->with('paypal_sdk', $paypal_sdk)
            ->with('title', trans('titles.spende'))
            ->with('css', [mix('/css/spende.css')])
            ->with('darkcss', [mix('/css/spende-dark.css')])
            ->with('js', [mix('/js/donation.js')]), 200, ["Content-Security-Policy" => $csp]);
    }

    function paypalCreateOrder(Request $request, $amount, $interval, $funding_source)
    {
        $validator = Validator::make(["amount" => $amount, "interval" => $interval], [
            'amount' => 'required|numeric|min:1',
            'interval' => Rule::in(["once", "monthly", "quarterly", "six-monthly", "annual"])
        ]);
        if ($validator->fails()) {
            abort(400);
        }

        $amount = round(floatval($amount), 2);

        $order_data = [
            "purchase_units" => [
                [
                    "amount" => [
                        "currency_code" => "EUR",
                        "value" => $amount,
                        "breakdown" => [
                            "item_total" => [
                                "currency_code" => "EUR",
                                "value" => $amount
                            ]
                        ],
                    ],
                    "items" => [
                        [
                            "name" => __('spende.execute-payment.item-name'),
                            "quantity" => "1",
                            "category" => "DONATION",
                            "unit_amount" => [
                                "currency_code" => "EUR",
                                "value" => $amount
                            ]
                        ]
                    ],
                ],
            ],
            "intent" => "CAPTURE",
            "application_context" => [
                "shipping_preference" => 'NO_SHIPPING'
            ]
        ];

        $base_url = config("metager.metager.paypal.base_url");
        $access_token = $this->generatePayPalAccessToken();

        $url = $base_url . "/v2/checkout/orders";
        $opts = [
            "http" => [
                "method" => "POST",
                "header" => [
                    "Authorization: Bearer " . $access_token,
                    "Content-Type: application/json"
                ],
                "content" => json_encode($order_data),
                "ignore_errors" => true
            ],
        ];
        $opts = stream_context_create($opts);
        $response = file_get_contents($url, false, $opts);
        preg_match('/([0-9])\d+/', $http_response_header[0], $matches);
        $responsecode = intval($matches[0]);
        return response()->json(json_decode($response), $responsecode);
    }

    public function paypalCaptureOrder(Request $request, $amount, $interval, $funding_source)
    {
        $validator = Validator::make(["amount" => $amount, "interval" => $interval], [
            'amount' => 'required|numeric|min:1',
            'interval' => Rule::in(["once", "monthly", "quarterly", "six-monthly", "annual"])
        ]);
        if ($validator->fails()) {
            abort(400);
        }

        $amount = round(floatval($amount), 2);
        $orderId = $request->input("orderID", "");
        if (empty($orderId)) {
            abort(400);
        }
        $base_url = config("metager.metager.paypal.base_url");
        $access_token = $this->generatePayPalAccessToken();

        $url = $base_url . "/v2/checkout/orders/$orderId/capture";
        $opts = [
            "http" => [
                "method" => "POST",
                "header" => [
                    "Authorization: Bearer " . $access_token,
                    "Content-Type: application/json"
                ],
                "ignore_errors" => true
            ],
        ];
        $opts = stream_context_create($opts);
        $response = file_get_contents($url, false, $opts);
        preg_match('/([0-9])\d+/', $http_response_header[0], $matches);
        $responsecode = intval($matches[0]);
        return response()->json(json_decode($response), $responsecode);
    }

    private function generatePayPalAccessToken()
    {
        $base_url = config("metager.metager.paypal.base_url");
        $client_id = config("metager.metager.paypal.client_id");
        $app_secret = config("metager.metager.paypal.secret");

        $opts = [
            "http" => [
                "method" => "POST",
                "header" => [
                    "Authorization: Basic " . base64_encode($client_id . ":" . $app_secret),
                    "Content-Type: application/x-www-form-urlencoded"
                ],
                "content" => "grant_type=client_credentials"
            ],
        ];
        $opts = stream_context_create($opts);
        $response = file_get_contents($base_url . "/v1/oauth2/token", false, $opts);
        $response = json_decode($response);
        return $response->access_token;
    }
}