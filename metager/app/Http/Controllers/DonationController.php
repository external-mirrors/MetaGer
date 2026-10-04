<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Vite;
use App\Donations\DonationCheckoutIssuer;
use App\Localization;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\ErrorCorrectionLevel;
use Illuminate\Http\Request;
use Illuminate\Support\MessageBag;
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
        }

        return $this->intervalPage($amount);
    }

    /**
     * The last step on this side: amount and interval are settled, and
     * suma-payments' own picker does the rest — the payment method (bank
     * transfer included), an optional email (required for Wero), and who
     * the donor is comes with the payment itself. Aborting there comes back
     * to the interval step.
     */
    function checkout(Request $request, $amount)
    {
        $validator = Validator::make(["amount" => $amount, "interval" => $request->input("interval")], [
            'amount' => 'required|numeric|min:1',
            'interval' => ['required', Rule::in(["once", "monthly", "quarterly", "six-monthly", "annual"])],
        ]);
        if ($validator->fails()) {
            return array_key_exists("amount", $validator->failed())
                ? redirect(LaravelLocalization::getLocalizedUrl(null, '/spende'))
                : redirect(LaravelLocalization::getLocalizedUrl(null, '/spende/' . $amount));
        }

        $interval = $request->input("interval");
        $amount = round(floatval($amount), 2);
        $checkoutUrl = app(DonationCheckoutIssuer::class)->create([
            "amount" => $amount,
            "recurring" => $interval !== "once",
            "frequency" => $interval !== "once" ? $interval : null,
            "locale" => Localization::getLanguage(),
            "return_url" => URL::signedRoute("thankyou", ["amount" => $amount, "interval" => $interval, "timestamp" => time()]),
            "cancel_url" => LaravelLocalization::getLocalizedUrl(null, '/spende/' . $amount),
        ]);

        // Web routes run without a session here, so the error is rendered
        // straight into the page rather than flashed across a redirect.
        if ($checkoutUrl === null) {
            return $this->intervalPage($amount, new MessageBag(['crm' => __('spende.execute-payment.error.unavailable')]));
        }

        return redirect($checkoutUrl);
    }

    private function intervalPage($amount, ?MessageBag $errors = null)
    {
        return response(view('spende.interval')
            ->withErrors($errors ?? new MessageBag())
            ->with('donation', [
                "amount" => round(floatval($amount), 2)
            ])
            ->with('title', trans('titles.spende'))
            ->with('css', [Vite::asset('resources/less/metager/pages/spende/base.less')])
            ->with('darkcss', [Vite::asset('resources/less/metager/pages/spende/base-dark.less')])
            ->with('js', [Vite::asset('resources/js/donation/base.js')]));
    }

    /**
     * suma-payments appends the method the donor picked as `payment_method`
     * on the way back, which is not part of what was signed.
     */
    public function donationFinished(Request $request, $amount, $interval)
    {
        $validator = Validator::make(["amount" => $amount, "interval" => $interval], [
            'amount' => 'required|numeric|min:1',
            'interval' => Rule::in(["once", "monthly", "quarterly", "six-monthly", "annual"])
        ]);
        if ($validator->fails() || !Localization::hasValidSignature(["payment_method"])) {
            abort(404);
        }

        $donation = [
            "amount" => round(floatval($amount), 2),
            "interval" => $interval,
            "funding_source" => match ($request->query("payment_method")) {
                "sepa_directdebit" => "directdebit",
                // A single donation's one-off Wero; the label is the same.
                "wero" => "wero_link",
                "banktransfer", "paypal", "card", "wero_link" => $request->query("payment_method"),
                default => null,
            },
        ];

        return response(view('spende.danke')
            ->with('donation', $donation)
            ->with('title', trans('titles.spende'))
            ->with('css', [Vite::asset('resources/less/metager/pages/spende/base.less')])
            ->with('darkcss', [Vite::asset('resources/less/metager/pages/spende/base-dark.less')])
            ->with('js', [Vite::asset('resources/js/donation/base.js')]), 200);
    }
}