<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Vite;
use App\Donations\DonationCheckoutIssuer;
use App\Jobs\DonationNotification;
use App\Localization;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\ErrorCorrectionLevel;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use LaravelLocalization;
use Illuminate\Support\Facades\Validator;
use SepaQr\SepaQrData;
use URL;

class DonationController extends Controller
{
    function amount(Request $request)
    {
        if ($request->filled("amount")) {
            return redirect(LaravelLocalization::getLocalizedUrl(null, '/spende/' . $request->input('amount')));
        }

        // Generate qr data uri
        $payment_data = (new SepaQrData())
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
            ->with('css', [Vite::asset('resources/less/metager/pages/spende/base.less')])
            ->with('darkcss', [Vite::asset('resources/less/metager/pages/spende/base-dark.less')])
            ->with('js', [Vite::asset('resources/js/donation/base.js')])
            ->with('navbarFocus', 'foerdern');
    }

    function amountQr(Request $request)
    {
        // Generate qr data uri
        $payment_data = (new SepaQrData())
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
            ->with('css', [Vite::asset('resources/less/metager/pages/spende/base.less')])
            ->with('darkcss', [Vite::asset('resources/less/metager/pages/spende/base-dark.less')])
            ->with('js', [Vite::asset('resources/js/donation/base.js')]);
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

        // No PayPal SDK on this page anymore — funding-source picking (wallet
        // vs. giropay/sofort/ideal/etc.) now happens entirely on suma-payments'
        // hosted checkout page; this page only offers three static tiles
        // (banktransfer, directdebit, paypal) plus `card`, which stays on its
        // own untouched direct-SDK path.
        return view('spende.paymentMethod')
            ->with('donation', $donation)
            ->with('title', trans('titles.spende'))
            ->with('css', [Vite::asset('resources/less/metager/pages/spende/base.less')])
            ->with('darkcss', [Vite::asset('resources/less/metager/pages/spende/base-dark.less')])
            ->with('js', [Vite::asset('resources/js/donation/base.js')]);
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
        $payment_data = (new SepaQrData())
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
            ->with('css', [Vite::asset('resources/less/metager/pages/spende/base.less')])
            ->with('darkcss', [Vite::asset('resources/less/metager/pages/spende/base-dark.less')])
            ->with('js', [Vite::asset('resources/js/donation/base.js')]));
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
            ->with('css', [Vite::asset('resources/less/metager/pages/spende/base.less')])
            ->with('darkcss', [Vite::asset('resources/less/metager/pages/spende/base-dark.less')])
            ->with('js', [Vite::asset('resources/js/donation/base.js')]));
    }

    function directdebitExecute(Request $request, $amount, $interval)
    {
        $validator = Validator::make(["amount" => $amount, "interval" => $interval, "name" => $request->input("name")], [
            'amount' => 'required|numeric|min:1',
            'interval' => Rule::in(["once", "monthly", "quarterly", "six-monthly", "annual"]),
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
                    ->with('css', [Vite::asset('resources/less/metager/pages/spende/base.less')])
                    ->with('darkcss', [Vite::asset('resources/less/metager/pages/spende/base-dark.less')])
                    ->with('js', [Vite::asset('resources/js/donation/base.js')]));
            }
        } else {
            $donation["fullname"] = $request->input("name");
        }

        // IBAN is no longer collected here — suma-payments' own hosted
        // checkout page collects it once suma-crm establishes the mandate.
        $checkoutUrl = app(DonationCheckoutIssuer::class)->create([
            "amount" => $donation["amount"],
            "method" => "directdebit",
            "recurring" => $interval !== "once",
            "frequency" => $interval !== "once" ? $interval : null,
            "name" => $donation["fullname"],
            "locale" => Localization::getLanguage(),
            "return_url" => URL::signedRoute("thankyou", ["amount" => $donation["amount"], "interval" => $donation["interval"], "funding_source" => "directdebit", "timestamp" => time()]),
        ]);

        if ($checkoutUrl === null) {
            return redirect(LaravelLocalization::getLocalizedUrl(null, '/spende/' . $amount . '/' . $interval . '/directdebit'))
                ->withErrors(['crm' => __('spende.execute-payment.error.unavailable')]);
        }

        DonationNotification::dispatch($donation["amount"], $donation["interval"], "Lastschrift")->onQueue("general");

        return redirect($checkoutUrl);
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
        $payment_data = (new SepaQrData())
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

    /**
     * Both the PayPal wallet tile and the card tile hand straight off to
     * suma-crm now (cutover-plan.md C2/C6) — suma-crm's checkout session
     * already offers PayPal's full APM breadth (wallet + giropay/sofort/...)
     * on suma-payments' own hosted page, and VR Payment's Hosted Payment
     * Page does the same job card used to do via PayPal's card-fields SDK
     * (client-token generation, order create/capture, 3DS liability-shift
     * parsing) — all of that is gone; suma-payments' own checkout tile is
     * the one place a raw PAN is ever handled now (cutover-plan.md §4.10).
     * A recurring donation of either method needs the donor's name the same
     * way directdebit does — StoreDonationRequest requires one whenever
     * `recurring` is true, regardless of method.
     */
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
        }

        $donation = [
            "amount" => round(floatval($amount), 2),
            "interval" => $interval,
            "funding_source" => $funding_source
        ];

        $checkoutUrl = app(DonationCheckoutIssuer::class)->create([
            "amount" => $donation["amount"],
            "method" => $funding_source,
            "recurring" => $interval !== "once",
            "frequency" => $interval !== "once" ? $interval : null,
            "name" => $interval !== "once" ? $request->query("name") : null,
            "locale" => Localization::getLanguage(),
            "return_url" => URL::signedRoute("thankyou", ["amount" => $donation["amount"], "interval" => $interval, "funding_source" => $funding_source, "timestamp" => time()]),
        ]);

        if ($checkoutUrl === null) {
            return redirect(LaravelLocalization::getLocalizedUrl(null, '/spende/' . $amount . '/' . $interval))
                ->withErrors(['crm' => __('spende.execute-payment.error.unavailable')]);
        }

        DonationNotification::dispatch($donation["amount"], $donation["interval"], $funding_source === "card" ? "Kreditkarte" : "PayPal")->onQueue("general");

        return redirect($checkoutUrl);
    }

    /**
     * `wero_link` (cutover-plan.md §4.11/C6) is recurring-only — there is no
     * one-shot Wero donation through this endpoint (suma-crm's
     * StoreDonationRequest rejects one) — so, unlike directdebit, this has no
     * "once" case to support and the interval segment here is never
     * "once". Collects an email alongside the name: the donor never
     * interacts with a hosted payment page at all for this method, they get
     * a permanent landing-page link mailed to them each period instead.
     */
    function weroLink(Request $request, $amount, $interval)
    {
        $validator = Validator::make(["amount" => $amount, "interval" => $interval], [
            'amount' => 'required|numeric|min:1',
            'interval' => Rule::in(["monthly", "quarterly", "six-monthly", "annual"])
        ]);
        if ($validator->fails()) {
            $failedParams = $validator->failed();
            if (array_key_exists("amount", $failedParams)) {
                return redirect(LaravelLocalization::getLocalizedUrl(null, '/spende'));
            } else {
                return redirect(LaravelLocalization::getLocalizedUrl(null, '/spende/' . $amount));
            }
        }

        $donation = [
            "amount" => round(floatval($amount), 2),
            "interval" => $interval,
            "funding_source" => "wero_link"
        ];

        return response(view('spende.payment.wero_link')
            ->with('donation', $donation)
            ->with('title', trans('titles.spende'))
            ->with('css', [Vite::asset('resources/less/metager/pages/spende/base.less')])
            ->with('darkcss', [Vite::asset('resources/less/metager/pages/spende/base-dark.less')])
            ->with('js', [Vite::asset('resources/js/donation/base.js')]));
    }

    function weroLinkExecute(Request $request, $amount, $interval)
    {
        $validator = Validator::make(["amount" => $amount, "interval" => $interval, "name" => $request->input("name"), "email" => $request->input("email")], [
            'amount' => 'required|numeric|min:1',
            'interval' => Rule::in(["monthly", "quarterly", "six-monthly", "annual"]),
            "name" => 'required',
            "email" => 'required|email',
        ]);
        $donation = [
            "amount" => round(floatval($amount), 2),
            "interval" => $interval,
            "funding_source" => "wero_link"
        ];
        if ($validator->fails()) {
            $failedParams = $validator->failed();
            if (array_key_exists("amount", $failedParams)) {
                return redirect(LaravelLocalization::getLocalizedUrl(null, '/spende'));
            } elseif (array_key_exists("interval", $failedParams)) {
                return redirect(LaravelLocalization::getLocalizedUrl(null, '/spende/' . $amount));
            } else {
                return response(view('spende.payment.wero_link')
                    ->withErrors($validator)
                    ->with('donation', $donation)
                    ->with('title', trans('titles.spende'))
                    ->with('css', [Vite::asset('resources/less/metager/pages/spende/base.less')])
                    ->with('darkcss', [Vite::asset('resources/less/metager/pages/spende/base-dark.less')])
                    ->with('js', [Vite::asset('resources/js/donation/base.js')]));
            }
        }

        $checkoutUrl = app(DonationCheckoutIssuer::class)->create([
            "amount" => $donation["amount"],
            "method" => "wero_link",
            "recurring" => true,
            "frequency" => $interval,
            "name" => $request->input("name"),
            "email" => $request->input("email"),
            "locale" => Localization::getLanguage(),
            "return_url" => URL::signedRoute("thankyou", ["amount" => $donation["amount"], "interval" => $interval, "funding_source" => "wero_link", "timestamp" => time()]),
        ]);

        if ($checkoutUrl === null) {
            return redirect(LaravelLocalization::getLocalizedUrl(null, '/spende/' . $amount . '/' . $interval . '/wero_link'))
                ->withErrors(['crm' => __('spende.execute-payment.error.unavailable')]);
        }

        DonationNotification::dispatch($donation["amount"], $donation["interval"], "Wero")->onQueue("general");

        return redirect($checkoutUrl);
    }

    public function donationFinished(Request $request, $amount, $interval, $funding_source)
    {
        $validator = Validator::make(["amount" => $amount, "interval" => $interval], [
            'amount' => 'required|numeric|min:1',
            'interval' => Rule::in(["once", "monthly", "quarterly", "six-monthly", "annual"])
        ]);
        if ($validator->fails() || !Localization::hasValidSignature()) {
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
            ->with('css', [Vite::asset('resources/less/metager/pages/spende/base.less')])
            ->with('darkcss', [Vite::asset('resources/less/metager/pages/spende/base-dark.less')])
            ->with('js', [Vite::asset('resources/js/donation/base.js')]), 200);
    }
}