<?php

namespace App\Http\Controllers;

use App\Jobs\ContactMail;
use App\Localization;
use App\Rules\IBANValidator;
use Cache;
use Closure;
use Crypt;
use Exception;
use Illuminate\Http\Request;
use LaravelLocalization;
use Validator;

class MembershipController extends Controller
{
    /**
     * First stage of membership form
     * gather information for contact data
     */
    public function contactData(Request $request)
    {
        if (Localization::getLanguage() === "de") {
            $csrf_token = Crypt::encrypt(now()->addHour());
            return response(view("membership.form", ["title" => __("titles.membership"), 'csrf_token' => $csrf_token, "css" => [mix("/css/membership.css")], "darkcss" => [mix("/css/membership-dark.css")], "js" => [mix("/js/membership.js")]]));
        } else {
            return response(view("membership.nonGerman", ["title" => __("titles.membership"), "css" => [mix("/css/membership.css")], "darkcss" => [mix("/css/membership-dark.css")], "js" => [mix("/js/membership.js")]]));
        }
    }

    public function success(Request $request)
    {
        return response(view("membership.success", ["title" => __("titles.membership"), "css" => [mix("/css/membership.css")], "darkcss" => [mix("/css/membership-dark.css")]]));
    }

    public function submitMembershipForm(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "_token" => [
                'required',
                function (string $attribute, mixed $value, Closure $fail) {
                    try {
                        $expiration = Crypt::decrypt($value);
                        if (now()->isAfter($expiration) || Cache::has("membership_" . $expiration->unix())) {
                            $fail("Please try again.");
                        } else {
                            Cache::put("membership_" . $expiration->unix(), true, now()->addHour());
                        }
                    } catch (Exception $e) {
                        $fail("Please try again.");
                    }
                },
            ],
            "name" => 'required',
            "email" => "required|email",
            "amount" => 'required|in:5.00,10.00,15.00,custom',
            "custom-amount" => 'exclude_unless:amount,custom|numeric|required|min:2.5',
            "interval" => 'required|in:annual,six-monthly,quarterly,monthly',
            "payment-method" => 'required|in:directdebit,banktransfer',
            "iban" => ["exclude_unless:payment-method,directdebit", "required", new IBANValidator()]
        ]);
        if ($validator->fails()) {
            $csrf_token = Crypt::encrypt(now()->addHour());
            return response(
                view(
                    "membership.form",
                    [
                        'csrf_token' => $csrf_token,
                        "title" => __("titles.membership"),
                        "css" => [mix("/css/membership.css")],
                        "darkcss" => [mix("/css/membership-dark.css")],
                        "js" => [mix("/js/membership.js")],
                        "errors" => $validator->errors()
                    ]
                )
            );
        }
        $formData = $validator->getData();
        if ($formData["amount"] === "custom") {
            $formData["amount"] = $formData["custom-amount"];
        }
        $formData["amount"] = number_format(round(floatval($formData["amount"]), 2), 2, ",", ".") . "€";

        $message = <<<MESSAGE
        Name: ${formData["name"]}
        Email: ${formData["email"]}
        Betrag: ${formData["amount"]}
        Intervall: ${formData["interval"]}
        Zahlungsart: ${formData["payment-method"]}
        MESSAGE;

        if ($formData["payment-method"] === "directdebit") {
            if (!empty($formData["accountholder"])) {
                $message .= PHP_EOL . "Kontoinhaber: " . $formData["accountholder"];
            }
            $message .= PHP_EOL . "IBAN: " . $formData["iban"];
        }

        // Create Notification
        ContactMail::dispatch("verein@metager.de", "Mitglieder", $formData["name"], $formData["email"], "Neuer Aufnahmeantrag", $message, [], "text/plain")->onQueue("general");

        return redirect(route("membership_success"));
    }
}