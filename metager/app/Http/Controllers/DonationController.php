<?php

namespace App\Http\Controllers;

use App\Jobs\CreateDirectDebit;
use App\Jobs\DonationNotification;
use App\Localization;
use App\PrometheusExporter;
use App\Rules\IBANValidator;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\ErrorCorrectionLevel;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use LaravelLocalization;
use Illuminate\Support\Facades\Validator;
use PHP_IBAN\IBAN;
use Illuminate\Support\Facades\RateLimiter;
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
            ->errorCorrectionLevel(ErrorCorrectionLevel::High)
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
            ->errorCorrectionLevel(ErrorCorrectionLevel::High)
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
            ->errorCorrectionLevel(ErrorCorrectionLevel::High)
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
            ->errorCorrectionLevel(ErrorCorrectionLevel::High)
            ->build();

        return response($qr->getString(), 200, ["Content-Type" => $qr->getMimeType(), "Content-Disposition" => "attachment; filename=suma_donation.png"]);
    }

    function paypalPayment(Request $request, $amount, $interval, $funding_source)
    {
        $validator = Validator::make(["amount" => $amount, "interval" => $interval], [
            'amount' => ['required', 'numeric', 'min:1', Rule::when($funding_source === "card", 'min:5')],
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
            //"components" => "buttons,funding-eligibility,card-fields,payment-fields,marks"
        ];
        $components = ["buttons"];
        if ($interval === "once") {
            if ($funding_source === "card") {
                $components = array_merge($components, ["card-fields"]);
            } else if ($funding_source != "paypal") {
                $components = array_merge($components, ["funding-eligibility", "payment-fields"]);
            }
        }
        $script_params["components"] = implode(",", $components);



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

    function paypalCreateSubscription(Request $request, $amount, $interval, $funding_source)
    {
        $validator = Validator::make(["amount" => $amount, "interval" => $interval], [
            'amount' => 'required|numeric|min:1',
            'interval' => Rule::in(["monthly", "quarterly", "six-monthly", "annual"])
        ]);
        if ($validator->fails()) {
            abort(400);
        }

        $subscription_plan_locale = Localization::getLanguage() === "de" ? "de" : "en";

        $subscription_data = [
            "plan_id" => config("metager.metager.paypal.subscription_plans.$subscription_plan_locale.$interval"),
            "application_context" => [
                "shipping_preference" => "NO_SHIPPING",
            ],
            "plan" => [
                "billing_cycles" => [
                    [
                        "sequence" => 1,
                        "total_cycles" => 0,
                        "pricing_scheme" => [
                            "fixed_price" => [
                                "currency_code" => "EUR",
                                "value" => $amount,
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $base_url = config("metager.metager.paypal.base_url");
        $access_token = $this->generatePayPalAccessToken();

        $url = $base_url . "/v1/billing/subscriptions";
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                "Authorization: Bearer $access_token",
                "Content-Type: application/json"
            ],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POSTFIELDS => json_encode($subscription_data)
        ]);
        $response = curl_exec($ch);
        $responseCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if ($responseCode === 201) {
            $response = json_decode($response);
            $response_body = [
                "id" => $response->id,
                "redirect_url" => URL::signedRoute("thankyou", ["amount" => $amount, "interval" => $interval, "funding_source" => $funding_source, "timestamp" => time()])
            ];
            return response()->json($response_body);
        } else {
            return response($response, 400, ["Content-Type" => "application/json"]);
        }
    }

    function paypalCreateOrder(Request $request, $amount, $interval, $funding_source)
    {
        $validator = Validator::make(["amount" => $amount, "interval" => $interval], [
            'amount' => ['required', 'numeric', 'min:1', Rule::when($funding_source === "card", 'min:5')],
            'interval' => Rule::in(["once", "monthly", "quarterly", "six-monthly", "annual"])
        ]);
        if ($validator->fails()) {
            abort(400);
        }

        if ($funding_source === "card") {
            $ratelimit_key = 'create-order-cc';

            RateLimiter::hit($ratelimit_key, 60);
            RateLimiter::hit($ratelimit_key . "-user-" . $request->ip(), 86400);

            if (RateLimiter::tooManyAttempts($ratelimit_key, 5) || RateLimiter::tooManyAttempts($ratelimit_key . "-user-" . $request->ip(), 10)) {
                abort(400);
            }
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

        if ($funding_source === "card") {
            PrometheusExporter::CreditcardDonation("started");
            $order_data["payment_source"] = [
                "card" => [
                    "attributes" => [
                        "verification" => [
                            "method" => "SCA_ALWAYS"
                        ]
                    ],
                    "experience_context" => [
                        "shipping_preference" => "NO_SHIPPING",
                    ]
                ]
            ];
        }

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

        if ($funding_source === "card") {
            $order_details = $this->getOrderDetails($access_token, $orderId);
            if (property_exists($order_details->payment_source->card, "authentication_result") && !$this->cardAuthenticated($order_details->payment_source->card->authentication_result)) {
                return response()->json(["error" => "card not authenticated"], 400);
            }
        }
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

        $response = json_decode($response);

        // Validate that the payment is completed
        // $response->status === "COMPLETED"
        // $response->purchase_units->payments-captures contains final_capture = true AND status is completed
        $payment_successfull = false;
        if ($responsecode === 201 && $response->status === "COMPLETED") {
            foreach ($response->purchase_units as $purchase_units) {
                $final_capture = false;
                foreach ($purchase_units->payments->captures as $capture) {
                    if ($capture->status !== "COMPLETED") {
                        break;
                    }
                    if ($capture->final_capture === true) {
                        $final_capture = true;
                    }
                }
                $payment_successfull = $final_capture;
                if (!$payment_successfull) {
                    break;
                }
            }

        }

        if (!$payment_successfull) {
            PrometheusExporter::CreditcardDonation("rejected");
            $response->redirect_to = route("paypalPayment", ["amount" => $amount, "interval" => $interval, "funding_source" => $funding_source]);
        } else {
            PrometheusExporter::CreditcardDonation("successfull");
            DonationNotification::dispatch($amount, $interval, "PayPal")->onQueue("general");
            $response->redirect_to = URL::signedRoute("thankyou", ["amount" => $amount, "interval" => $interval, "funding_source" => $funding_source, "timestamp" => time()]);
        }

        return response()->json($response, $responsecode);
    }

    /**
     * Parses PayPals authentication result for card payments and acts according to
     * https://developer.paypal.com/docs/checkout/advanced/customize/3d-secure/response-parameters/#link-recommendedaction
     */
    private function cardAuthenticated($authentication_result)
    {
        $liability_shift = $authentication_result->liability_shift;
        $authentication_status = null;
        if (property_exists($authentication_result->three_d_secure, "authentication_status")) {
            $authentication_status = $authentication_result->three_d_secure->authentication_status;
        }
        $enrollment_status = $authentication_result->three_d_secure->enrollment_status;
        if ($enrollment_status === "Y") {
            switch ($authentication_status) {
                case "Y":
                    if (in_array($liability_shift, ["POSSIBLE", "YES"])) {
                        return true;
                    } else {
                        return false;
                    }
                case "N":
                    if ($liability_shift === "NO") {
                        return false;
                    } else {
                        return true;
                    }
                case "R":
                    if ($liability_shift === "NO") {
                        return false;
                    } else {
                        return true;
                    }
                case "A":
                    if ($liability_shift === "POSSIBLE") {
                        return true;
                    } else {
                        return false;
                    }
                case "U":
                    if (in_array($liability_shift, ["UNKNOWN", "NO"])) {
                        return false;
                    } else {
                        return true;
                    }
                case "C":
                    if ($liability_shift === "UNKNOWN") {
                        return false;
                    } else {
                        return true;
                    }
                default:
                    return false;
            }
        } else if ($enrollment_status === "N") {
            if ($liability_shift === "NO") {
                return true;
            } else {
                return false;
            }
        } else if ($enrollment_status === "U") {
            switch ($liability_shift) {
                case "NO":
                    return true;
                case "UNKNOWN":
                    return false;
                default:
                    return false;
            }
        } else if ($enrollment_status === "B") {
            if ($liability_shift === "NO") {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }
    private function getOrderDetails($access_token, $orderId)
    {
        $paypal_url = config("metager.metager.paypal.base_url") . "/v2/checkout/orders/$orderId";
        $ch = curl_init($paypal_url);

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => ["Authorization: Bearer $access_token"],
        ]);
        $response = curl_exec($ch);

        curl_close($ch);
        return json_decode($response);
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