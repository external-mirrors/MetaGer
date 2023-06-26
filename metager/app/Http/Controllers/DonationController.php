<?php

namespace App\Http\Controllers;

use App\Jobs\CreateDirectDebit;
use App\Jobs\DonationNotification;
use App\Localization;
use App\Rules\IBANValidator;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\ErrorCorrectionLevel\ErrorCorrectionLevelHigh;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use LaravelLocalization;
use Illuminate\Support\Facades\Validator;
use PHP_IBAN\IBAN;
use SepaQr\Data;
use URL;

class DonationController extends Controller
{
    function amount(Request $request)
    {
        if ($request->filled("amount")) {
            return redirect(LaravelLocalization::getLocalizedUrl(null, '/spende/' . $request->input('amount')));
        }

        // Generate qr data uri
        $payment_data = Data::create()
            ->setName("SUMA-EV")
            ->setIban("DE64430609674075033201")
            ->setBic("GENODEM1GLS")
            ->setCurrency("EUR")
            ->setRemittanceText(__('spende.execute-payment.banktransfer.qr-remittance', ["date" => now()->format("d.m.Y")]));
        $qr_uri = Builder::create()
            ->data($payment_data)
            ->errorCorrectionLevel(new ErrorCorrectionLevelHigh())
            ->build()
            ->getDataUri();

        return view('spende.amount')
            ->with('banktransfer_qr_uri', $qr_uri)
            ->with('title', trans('titles.spende'))
            ->with('css', [mix('/css/spende.css')])
            ->with('darkcss', [mix('/css/spende-dark.css')])
            ->with('js', [mix('/js/donation.js')])
            ->with('navbarFocus', 'foerdern');
    }

    function amountQr(Request $request)
    {
        // Generate qr data uri
        $payment_data = Data::create()
            ->setName("SUMA-EV")
            ->setIban("DE64430609674075033201")
            ->setBic("GENODEM1GLS")
            ->setCurrency("EUR")
            ->setRemittanceText(__('spende.execute-payment.banktransfer.qr-remittance', ["date" => now()->format("d.m.Y")]))
            ->setAmount(10);
        $qr = Builder::create()
            ->data($payment_data)
            ->errorCorrectionLevel(new ErrorCorrectionLevelHigh())
            ->build();

        return response($qr->getString(), 200, ["Content-Type" => $qr->getMimeType(), "Content-Disposition" => "attachment; filename=suma_donation.png"]);
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

    function banktransfer(Request $request, $amount, $interval)
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
                "funding_source" => "banktransfer"
            ];
        }

        // Generate qr data uri
        $payment_data = Data::create()
            ->setName("SUMA-EV")
            ->setIban("DE64430609674075033201")
            ->setBic("GENODEM1GLS")
            ->setCurrency("EUR")
            ->setRemittanceText(__('spende.execute-payment.banktransfer.qr-remittance', ["date" => now()->format("d.m.Y")]))
            ->setAmount($amount);
        $qr_uri = Builder::create()
            ->data($payment_data)
            ->errorCorrectionLevel(new ErrorCorrectionLevelHigh())
            ->build()
            ->getDataUri();
        $donation["qr_uri"] = $qr_uri;

        return response(view('spende.payment.banktransfer')
            ->with('donation', $donation)
            ->with('title', trans('titles.spende'))
            ->with('css', [mix('/css/spende.css')])
            ->with('darkcss', [mix('/css/spende-dark.css')])
            ->with('js', [mix('/js/donation.js')]));
    }

    function directdebit(Request $request, $amount, $interval)
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
                "funding_source" => "directdebit"
            ];
        }

        return response(view('spende.payment.directdebit')
            ->with('donation', $donation)
            ->with('title', trans('titles.spende'))
            ->with('css', [mix('/css/spende.css')])
            ->with('darkcss', [mix('/css/spende-dark.css')])
            ->with('js', [mix('/js/donation.js')]));
    }

    function directdebitExecute(Request $request, $amount, $interval)
    {
        $validator = Validator::make(["amount" => $amount, "interval" => $interval, "iban" => $request->input("iban", ""), "name" => $request->input("name")], [
            'amount' => 'required|numeric|min:1',
            'interval' => Rule::in(["once", "monthly", "quarterly", "six-monthly", "annual"]),
            'iban' => ["required", new IBANValidator()],
            "name" => 'required'
        ]);
        $donation = [
            "amount" => round(floatval($amount), 2),
            "interval" => $interval,
            "funding_source" => "directdebit"
        ];
        if ($validator->fails()) {
            $failedParams = $validator->failed();
            if (array_key_exists("amount", $failedParams)) {
                return redirect(LaravelLocalization::getLocalizedUrl(null, '/spende'));
            } elseif (array_key_exists("interval", $failedParams)) {
                return redirect(LaravelLocalization::getLocalizedUrl(null, '/spende/' . $amount));
            } else {
                return response(view('spende.payment.directdebit')
                    ->withErrors($validator)
                    ->with('donation', $donation)
                    ->with('title', trans('titles.spende'))
                    ->with('css', [mix('/css/spende.css')])
                    ->with('darkcss', [mix('/css/spende-dark.css')])
                    ->with('js', [mix('/js/donation.js')]));
            }
        } else {
            $donation["fullname"] = $request->input("name");
            $donation["iban"] = $request->input("iban");
        }

        CreateDirectDebit::dispatch($donation["fullname"], new IBAN($donation["iban"]), $donation["amount"], $donation["interval"] === "annual" ? "yearly" : $donation["interval"])->onQueue("donations");
        DonationNotification::dispatch($donation["amount"], $donation["interval"], "Lastschrift")->onQueue("general");

        // Generate URL to thankyou page
        $url = URL::signedRoute("thankyou", ["amount" => $donation["amount"], "interval" => $donation["interval"], "funding_source" => "directdebit", "timestamp" => time()]);
        return redirect($url);
    }

    function banktransferQr(Request $request, $amount, $interval)
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
                "funding_source" => "banktransfer"
            ];
        }

        // Generate qr data uri
        $payment_data = Data::create()
            ->setName("SUMA-EV")
            ->setIban("DE64430609674075033201")
            ->setBic("GENODEM1GLS")
            ->setCurrency("EUR")
            ->setRemittanceText(__('spende.execute-payment.banktransfer.qr-remittance', ["date" => now()->format("d.m.Y")]))
            ->setAmount($amount);
        $qr = Builder::create()
            ->data($payment_data)
            ->errorCorrectionLevel(new ErrorCorrectionLevelHigh())
            ->build();

        return response($qr->getString(), 200, ["Content-Type" => $qr->getMimeType(), "Content-Disposition" => "attachment; filename=suma_donation.png"]);
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
            if ($funding_source === "card" && $interval === "once") {
                $donation["client_token"] = $this->generatePayPalClientToken();
            }
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
        $csp = "default-src * 'unsafe-inline'";

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

        DonationNotification::dispatch($amount, $interval, "PayPal")->onQueue("general");

        $response = json_decode($response);
        $response->redirect_to = URL::signedRoute("thankyou", ["amount" => $amount, "interval" => $interval, "funding_source" => $funding_source, "timestamp" => time()]);

        return response()->json($response, $responsecode);
    }

    public function donationFinished(Request $request, $amount, $interval, $funding_source)
    {
        $validator = Validator::make(["amount" => $amount, "interval" => $interval], [
            'amount' => 'required|numeric|min:1',
            'interval' => Rule::in(["once", "monthly", "quarterly", "six-monthly", "annual"])
        ]);
        if ($validator->fails() || !$request->hasValidSignature()) {
            abort(404);
        } else {
            $donation = [
                "amount" => round(floatval($amount), 2),
                "interval" => $interval,
                "funding_source" => $funding_source
            ];
        }

        return response(view('spende.danke')
            ->with('donation', $donation)
            ->with('title', trans('titles.spende'))
            ->with('css', [mix('/css/spende.css')])
            ->with('darkcss', [mix('/css/spende-dark.css')])
            ->with('js', [mix('/js/donation.js')]), 200);
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

    /**
     * Generates a client token required for advanced creditcard payments
     */
    private function generatePayPalClientToken()
    {
        $base_url = config("metager.metager.paypal.base_url");
        $accessToken = $this->generatePayPalAccessToken();

        $opts = [
            "http" => [
                "method" => "POST",
                "header" => [
                    "Authorization: Bearer $accessToken",
                    "Content-Type: application/json"
                ]
            ],
        ];
        $opts = stream_context_create($opts);
        $response = file_get_contents($base_url . "/v1/identity/generate-token", false, $opts);
        $response = json_decode($response);
        return $response->client_token;
    }
}