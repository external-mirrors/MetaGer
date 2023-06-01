<?php

namespace App\Http\Controllers;

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
        $csp = "default-src 'self'; script-src 'self' 'nonce-$nonce'; script-src-elem 'self' 'nonce-$nonce'; script-src-attr 'self'; style-src 'self'; style-src-elem 'self'; style-src-attr 'self'; img-src 'self' data:; font-src 'self'; connect-src 'self'; frame-src 'self'; frame-ancestors 'self'; form-action 'self' www.paypal.com";

        return response(view('spende.paymentMethod')
            ->with('donation', $donation)
            ->with('nonce', $nonce)
            ->with('paypal_sdk', $paypal_sdk)
            ->with('title', trans('titles.spende'))
            ->with('css', [mix('/css/spende.css')])
            ->with('darkcss', [mix('/css/spende-dark.css')])
            ->with('js', [mix('/js/donation.js')]), 200, ["Content-Security-Policy" => $csp]);
    }
}