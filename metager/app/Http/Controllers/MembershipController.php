<?php

namespace App\Http\Controllers;

use App;
use App\Localization;
use App\Mail\Membership\ApplicationDeny;
use App\Mail\Membership\MembershipAdminApplicationNotification;
use App\Mail\Membership\MembershipAdminPaymentFailed;
use App\Mail\Membership\PaymentMethodFailed;
use App\Mail\Membership\PaymentReminder;
use App\Mail\Membership\ReductionDeny;
use App\Mail\Membership\ReductionReminder;
use App\Mail\Membership\WelcomeMail;
use App\Models\Authorization\KeyAuthorization;
use App\Models\Membership\CiviCrm;
use App\Models\Membership\MembershipApplication;
use App\Models\Membership\MembershipPaymentPaypal;
use App\Models\Membership\PayPal;
use App\Rules\IBANValidator;
use Arr;
use Artisan;
use Cache;
use Closure;
use Crypt;
use Illuminate\Support\Fluent;
use Illuminate\Validation\Rule;
use Lang;
use Mail;
use RateLimiter;
use Exception;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Redis;
use Validator;


class MembershipController extends Controller
{

    public function test(Request $request)
    {
        $mail = new WelcomeMail(2291);
        return $mail;
    }
    /**
     * First stage of membership form
     * gather information for contact data
     */
    public function contactData(Request $request, $application_id = null)
    {
        if (Localization::getLanguage() === "de") {
            $csrf_token = Crypt::encrypt(now()->addHour());

            $application = null;
            if ($application_id !== null) {
                $application = uuid_is_valid($application_id) ? MembershipApplication::find($application_id) : null;
                $request_data = array_merge($request->except("edit"), ["application_id" => $application_id]);
                if ($application === null) {
                    $edit_data = json_decode(base64_decode($application_id), true);
                    if ($edit_data === null) {
                        return redirect(route("membership_form"));
                    }
                    $signature = Arr::pull($edit_data, "signature");
                    $edit_data = collect($edit_data)->sortKeys();
                    $signature_calced = hash_hmac("sha256", json_encode($edit_data), config("app.key"));
                    if (!hash_equals($signature_calced, $signature)) {
                        return redirect(route("membership_form"));
                    }
                    $expiration = new Carbon($edit_data["expiration"]);
                    if ($expiration->isPast()) {
                        return redirect(route("membership_form"));
                    }
                    $application = MembershipApplication::where("crm_membership", "=", $edit_data["crm_membership"])->first();
                    if ($application !== null) {
                        return redirect(route("membership_form", array_merge($request_data, ["application_id" => $application->id])));
                    }
                    $application = Arr::get(CiviCrm::FIND_MEMBERSHIPS(membership_id: $edit_data["crm_membership"]), "0");
                    if ($application === null) {
                        return redirect(route("membership_form"));
                    }
                }

                // Do not allow edits for existing contacts
                if (($application === null || !$application->is_update) && $request->input("edit", "") === "contact") {
                    if ($application->contact !== null) {
                        $application->contact()->delete();
                    } elseif ($application->company !== null) {
                        $application->company()->delete();
                        $application->amount = null;
                        $application->save();
                        $request_data["type"] = "company";
                    }
                    return redirect(route("membership_form", $request_data) . "#contact-data");
                } elseif ($request->input("edit", "") === "membership-fee") {
                    $application = $application->editable();
                    $request_data["application_id"] = $application->id;
                    if (in_array($application->amount, [(float) "10.00", (float) "15.00", (float) "20.00"])) {
                        $request_data["amount"] = number_format($application->amount, 2);
                    } elseif ($application->amount !== null) {
                        $request_data["amount"] = "custom";
                        $request_data["custom-amount"] = number_format($application->amount, 2);
                    }
                    $application->amount = null;
                    $application->save();
                    if ($application->reduction !== null)
                        $application->reduction->delete();
                    return redirect(route("membership_form", $request_data) . "#membership-fee");
                } elseif ($request->input("edit", "") === "membership-payment") {
                    $application = $application->editable();
                    $request_data["application_id"] = $application->id;
                    $request_data["interval"] = $application->interval;
                    $application->interval = null;
                    $application->save();
                    return redirect(route("membership_form", $request_data) . "#membership-payment");
                } elseif ($request->input("edit", "") === "membership-payment-method") {
                    $application = $application->editable();
                    $request_data["application_id"] = $application->id;
                    $request_data["payment-method"] = $application->payment_method;
                    $application->payment_method = null;
                    $application->save();
                    if ($application->directdebit !== null)
                        $application->directdebit->delete();
                    if ($application->paypal !== null)
                        $application->paypal->delete();
                    return redirect(route("membership_form", $request_data) . "#mmembership-payment-method");
                }

                if ($application->payment_method === null) {
                    if ($application->paypal !== null)
                        $application->paypal()->delete();
                }

                if (
                    !$application->is_update &&
                    ($application->contact !== null || $application->company !== null) && $application->amount !== null && $application->interval !== null &&
                    (
                        $application->payment_method === "banktransfer" ||
                        ($application->payment_method === "directdebit" && $application->directdebit !== null)
                    )
                ) {
                    return redirect(route("membership_success", ["application_id" => $application->id]));
                }
            }

            $csp = "default-src * 'unsafe-inline' 'unsafe-eval'";

            return response(view(
                "membership.form",
                [
                    "title" => __("titles.membership"),
                    'csrf_token' => $csrf_token,
                    "css" => [mix("/css/membership.css")],
                    "darkcss" => [mix("/css/membership-dark.css")],
                    "js" => [mix("/js/membership.js")],
                    "application" => $application
                ]
            ), 200, ["Content-Security-Policy" => $csp]);
        } else {
            return response(view("membership.nonGerman", ["title" => __("titles.membership"), "css" => [mix("/css/membership.css")], "darkcss" => [mix("/css/membership-dark.css")], "js" => [mix("/js/membership.js")]]));
        }
    }

    public function abortApplication(Request $request, string $application_id)
    {
        $application = MembershipApplication::find($application_id);
        if ($application !== null) {
            $application->delete();
        }
        return redirect(route("membership_form"));
    }

    public function success(Request $request, $application_id = null)
    {
        $application = null;
        if ($application_id !== null) {
            $application = MembershipApplication::finishedUser()->where("id", "=", $application_id)->first();
            if ($application === null) {
                return redirect(route("membership_form", ["application_id" => $application_id]));
            }
        } else {
            return redirect(route("membership_form"));
        }
        return response(view(
            "membership.success",
            [
                "application" => $application,
                "title" => __("titles.membership"),
                "css" => [mix("/css/membership.css")],
                "darkcss" => [mix("/css/membership-dark.css")]
            ]
        ));
    }

    public function getToken(Request $request)
    {
        $executed = RateLimiter::attempt("_token", 3, function () {

        }, 60);
        if ($executed) {
            return response()->json([
                "token" => Crypt::encrypt(now()->addHour())
            ]);
        } else {
            return response()->json([
                "message" => "Too many Tokens"
            ], 429);
        }
    }

    public function submitMembershipForm(Request $request, $application_id = null)
    {
        $application = null;
        if ($application_id !== null) {
            $application = MembershipApplication::find($application_id);
        }

        $validator = Validator::make(array_merge($request->all(), ["application_id" => $application_id]), [
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
        ]);

        $min_amount = 5;
        if ($application !== null) {
            if ($application->contact !== null)
                $min_amount = 2.5;
            else if ($application->company !== null) {
                if ($application->company->employees === "20-199")
                    $min_amount = 100;
                elseif ($application->company->employees === ">200")
                    $min_amount = 200;
            }
        }

        $validator->sometimes("title", ['exclude_if:type,company', 'required', 'in:Frau,Herr,Neutral'], function (Fluent $input) use ($application) {
            return ($application === null || ($application->contact === null && $application->company === null)) && $input->type === "person";
        });
        $validator->sometimes("firstname", ['exclude_if:type,company', 'required', 'max:50'], function (Fluent $input) use ($application) {
            return ($application === null || ($application->contact === null && $application->company === null)) && $input->type === "person";
        });
        $validator->sometimes("lastname", ['exclude_if:type,company', 'required', 'max:50'], function (Fluent $input) use ($application) {
            return ($application === null || ($application->contact === null && $application->company === null)) && $input->type === "person";
        });
        $validator->sometimes("company", ['exclude_unless:type,company', 'required', 'max:100'], function (Fluent $input) use ($application) {
            return ($application === null || ($application->contact === null && $application->company === null)) && $input->type === "company";
        });
        $validator->sometimes("employees", ['exclude_unless:type,company', 'required', 'in:1-19,20-199,>200'], function (Fluent $input) use ($application) {
            return ($application === null || ($application->contact === null && $application->company === null)) && $input->type === "company";
        });
        $validator->sometimes("email", "required|email", function (Fluent $input) use ($application) {
            return $application === null || ($application->contact === null && $application->company === null);
        });
        $validator->sometimes("amount", [
            "required",
            function ($attribute, $value, $fail) use ($min_amount) {
                $numeric_value = (float) $value;
                if ($value !== "custom" && $numeric_value < $min_amount) {
                    $fail(__("validation.min.numeric", ["attribute" => $attribute, "min" => $min_amount]));
                } elseif (!in_array($value, ["10.00", "15.00", "20.00", "custom"])) {
                    $fail(__("validation.in", ["attribute" => $attribute]));
                }
            }
        ], function (Fluent $input) use ($application) {
            return $application !== null && ($application->contact !== null || $application->company !== null) && $application->amount === null;
        });
        $validator->sometimes("custom-amount", [
            Rule::excludeIf($request->input("amount") !== "custom"),
            "numeric",
            "required",
            "min:$min_amount"
        ], function (Fluent $input) use ($application) {
            return $application !== null && ($application->contact !== null || $application->company !== null) && $application->amount === null;
        });
        $validator->sometimes("reduction", [Rule::excludeIf(floatval($request->input("amount")) >= 5 || $request->input("custom-amount") >= 5), 'required', 'file', 'max:10485760', 'mimetypes:image/jpeg,image/png,image/jpg,application/pdf'], function (Fluent $input) use ($application) {
            return $application !== null && ($application->contact !== null || $application->company !== null) && $application->amount === null;
        });
        $validator->sometimes("interval", 'required|in:annual,six-monthly,quarterly,monthly', function (Fluent $input) use ($application) {
            return $application !== null && ($application->contact !== null || $application->company !== null) && $application->amount !== null && $application->interval === null;
        });
        $validator->sometimes("payment-method", 'required|in:directdebit,banktransfer,paypal,card', function (Fluent $input) use ($application) {
            return $application !== null && ($application->contact !== null || $application->company !== null) && $application->amount !== null && $application->interval !== null;
        });
        $validator->sometimes("iban", ["exclude_unless:payment-method,directdebit", "required", new IBANValidator()], function (Fluent $input) use ($application) {
            return $application !== null && ($application->contact !== null || $application->company !== null) && $application->amount !== null && $application->interval !== null;
        });
        $validator->sometimes("bic", ["exclude_unless:payment-method,directdebit", "nullable"], function (Fluent $input) use ($application) {
            return $application !== null && ($application->contact !== null || $application->company !== null) && $application->amount !== null && $application->interval !== null;
        });
        $validator->sometimes("accountholder", ["exclude_unless:payment-method,directdebit", "nullable", "string", "max:100"], function (Fluent $input) use ($application) {
            return $application !== null && ($application->contact !== null || $application->company !== null) && $application->amount !== null && $application->interval !== null;
        });

        if ($validator->fails()) {
            $csrf_token = Crypt::encrypt(now()->addHour());

            $application = null;
            if ($application_id !== null) {
                $application = MembershipApplication::find($application_id);
            }

            return response(
                view(
                    "membership.form",
                    [
                        'csrf_token' => $csrf_token,
                        "title" => __("titles.membership"),
                        "css" => [mix("/css/membership.css")],
                        "darkcss" => [mix("/css/membership-dark.css")],
                        "js" => [mix("/js/membership.js")],
                        "errors" => $validator->errors(),
                        "application" => $application
                    ]
                )
            );
        }

        $form_data = $validator->validated();

        if ($application === null) {
            if ($application_id !== null) {
                return redirect(route("membership_form"));
            }
            $application = MembershipApplication::create(["locale" => Localization::getLanguage() . "-" . Localization::getRegion()]);
        }

        $request_data = array_merge($request->except(["edit", "_token"]), ["application_id" => $application->id]);
        $membership_form_url = route("membership_form", $request_data);

        if ($application->contact === null && $application->company === null) {
            $key = null;
            $authorization = app(\App\Models\Authorization\Authorization::class);
            $success_url = $membership_form_url . "#membership-fee";
            if ($authorization instanceof KeyAuthorization && !empty($authorization->key)) {
                $key = $authorization->key;
            } else {
                $key = $this->generateNewKey();
                $expires = now()->addMinutes(1)->timestamp;
                $signature = hash_hmac("sha256", $success_url . $expires, config("app.key"));
                $success_url = route("loadSettings", ["key" => $key, "redirect_url" => $success_url, "expires" => $expires, "signature" => $signature]);
            }
            $application->key = $key;
            $application->save();
            if ($request->input("type", "person") === "person") {
                $application->contact()->create([
                    "title" => $form_data["title"],
                    "first_name" => $form_data["firstname"],
                    "last_name" => $form_data["lastname"],
                    "email" => $form_data["email"],
                    "application_id" => $application->id
                ]);
            } else if ($request->input("type", "person") === "company") {
                $application->company()->create([
                    "company" => $form_data["company"],
                    "employees" => $form_data["employees"],
                    "email" => $form_data["email"]
                ]);
            }
            return redirect($success_url);
        } elseif ($application->amount === null) {
            $amount = $form_data["amount"];
            if ($amount === "custom") {
                $amount = $form_data["custom-amount"];
            }
            $application->amount = (float) $amount;

            // Check for a submitted reduction
            /** @var \File */
            $file = Arr::get($form_data, "reduction");
            if ($file !== null) {
                $application->reduction()->create([
                    "file_path" => storage_path("metager/" . $file->getBasename()),
                    "file_mimetype" => $file->getMimeType(),
                ]);
                $file->move(storage_path("metager"), $file->getBasename());
            }
            $application->save();
            return redirect($membership_form_url . "#membership-payment");
        } elseif ($application->interval === null) {
            $application->interval = $form_data["interval"];
            $application->save();
            return redirect($membership_form_url . "#membership-payment-method");
        } elseif ($application->payment_method === null) {
            switch ($form_data["payment-method"]) {
                case "banktransfer":
                    $application->payment_method = $form_data["payment-method"];
                    $application->save();
                    break;
                case "directdebit":
                    $attributes = [
                        "iban" => $form_data["iban"],
                        "bic" => $form_data["bic"],
                        "accountholder" => $form_data["accountholder"]
                    ];
                    if ($application->directdebit !== null)
                        $application->directdebit()->delete();
                    $application->directdebit()->create($attributes);
                    $application->payment_method = $form_data["payment-method"];
                    $application->save();
                    break;
                case "paypal":
                case "card":
                    return $this->createPayPalAuthorizeOrder(
                        $application,
                        $form_data["payment-method"],
                        route("membership_success", ["application_id" => $application->id]),
                        $membership_form_url . "#membership-payment-method"
                    );
            }

            if (!$application->is_update) {
                Artisan::call("membership:notify-admin", ["subject" => "[SUMA-EV] Neuer Aufnahmeantrag"]);
            }
            return redirect($membership_form_url);
        } else {
            return redirect(route("membership_success", ["key" => $application->key]));
        }
    }

    public function paypalWebhook(Request $request)
    {
        if (!PayPal::VALIDATE_WEBHOOK($request))
            abort(401);

        switch ($request->input("event_type")) {
            case "VAULT.PAYMENT-TOKEN.CREATED":
                // There is a new Payment Token. Check if a order ID is attached
                $order_id = $request->input("resource.metadata.order_id");
                $vault_id = $request->input("resource.id");
                if ($order_id !== null) {
                    $paypal = MembershipPaymentPaypal::where("order_id", "=", $order_id)->whereNotNull("application_id")->first();
                    if ($paypal !== null) {
                        $paypal->vault_id = $vault_id;
                        $paypal->save();
                    }
                    return response()->json([]);
                }
                abort(404);
            case "VAULT.PAYMENT-TOKEN.DELETED":
                $vault_id = $request->input("resource.id");
                MembershipPaymentPaypal::where("vault_id", "=", $vault_id)->delete();
                if (($membership_data = CiviCrm::REMOVE_MEMBERSHIP_PAYPAL_VAULT($vault_id)) !== null) {
                    $membership_id = Arr::get($membership_data, "values.0.id");
                    if ($membership_id !== null) {
                        $membership = Arr::get(CiviCrm::FIND_MEMBERSHIPS(membership_id: $membership_id), "0");
                        if ($membership !== null) {
                            $notification = new PaymentMethodFailed($membership);
                            Mail::mailer("membership")->send($notification);
                        }
                    }
                }
                return response()->json([]);
            case "PAYMENT.AUTHORIZATION.CREATED":
                $authorization_id = $request->input("resource.id");
                $authorization_status = $request->input("resource.status");
                $order_id = $request->input("resource.supplementary_data.related_ids.order_id");
                $paypal = MembershipPaymentPaypal::where("order_id", "=", $order_id)->first();
                if ($paypal !== null) {
                    $paypal->authorization_id = $authorization_id;
                    $paypal->authorization_status = $authorization_status;
                    $paypal->save();
                }
                return response()->json([]);
            case "PAYMENT.AUTHORIZATION.VOIDED":
                $authorization_id = $request->input("resource.id");
                $authorization_status = $request->input("resource.status");
                MembershipPaymentPaypal::where("authorization_id", "=", $authorization_id)->delete();
                return response()->json([]);
            case "PAYMENT.CAPTURE.COMPLETED":
                if (CiviCrm::HANDLE_PAYPAL_CAPTURE($request->input("resource")) === null) {
                    abort(500, "Couldn't create contribution payment");
                } else {
                    $order_id = $request->input("resource.supplementary_data.related_ids.order_id");
                    if ($order_id !== null) {
                        $paypal = MembershipPaymentPaypal::where("order_id", "=", $order_id)->first();
                        if ($paypal !== null) {
                            if ($paypal->vault_id !== null) {
                                $paypal->order_id = null;
                                $paypal->authorization_id = null;
                                $paypal->authorization_status = null;
                                $paypal->save();
                            } else {
                                $paypal->delete();
                            }
                        }
                    }
                    return response()->json(["status" => "success"]);
                }
            case "PAYMENT.CAPTURE.REVERSED":
            case "PAYMENT.CAPTURE.REFUNDED":
                if (CiviCrm::HANDLE_PAYPAL_REFUND($request->input("resource")) === null) {
                    abort(500, "Couldn't create contribution payment");
                } else {
                    return response()->json(["status" => "success"]);
                }
            default:
                return response()->json([]);
        }
    }

    public function paypalHandleAuthorized(Request $request, $application_id)
    {
        try {
            $parameters = [
                "application_id" => $application_id,
                "error_url" => $request->input("error_url"),
                "success_url" => $request->input("success_url"),
                "expires_at" => intval($request->input("expires_at"))
            ];
            $expiration = Carbon::createFromTimestamp($parameters["expires_at"]);
            if (!hash_equals(hash_hmac("sha256", json_encode($parameters), config("app.key")), $request->input("signature", "")) || now()->isAfter($expiration)) {
                abort(401);
            }
        } catch (Exception $e) {
            abort(401);
        }
        $error_url = $request->input("error_url");
        $success_url = $request->input("success_url");
        $application = MembershipApplication::find($application_id);

        if ($application === null) {
            return $request->wantsJson() ? response()->json(["message" => "There is no pending Paypal registration.", "cancel_url" => $error_url], 404) : redirect($error_url);
        }

        $order_id = Cache::pull("membership:paypal:orderid:{$application->id}");

        if ($order_id !== null) {
            // PayPal Vault Request with immediate payment
            $order = PayPal::VALIDATE_ORDER($order_id);
            if ($order === null) {
                return $request->wantsJson() ? response()->json(["message" => "The PayPal Order could not be validated", "cancel_url" => $error_url], 400) : redirect($error_url);
            } else if (is_string($order)) {
                return $request->wantsJson() ? response()->json(["message" => $order, "cancel_url" => $error_url], 400) : redirect($error_url);
            }

            if ($order["intent"] === "AUTHORIZE" && in_array($order["status"], ["SAVED", "APPROVED", "CREATED"])) {
                $order = PayPal::AUTHORIZE_ORDER($order_id);
                $order = PayPal::VALIDATE_ORDER(Arr::get($order, "id"), $order);
                if ($order === null) {
                    return $request->wantsJson() ? response()->json(["message" => "The PayPal Order could not be validated", "cancel_url" => $error_url], 400) : redirect($error_url);
                } else if (is_string($order)) {
                    return $request->wantsJson() ? response()->json(["message" => $order, "cancel_url" => $error_url], 400) : redirect($error_url);
                }
            } else if ($order["status"] !== "COMPLETED") {
                return $request->wantsJson() ? response()->json(["message" => "Couldn't authorize PayPal order.", "cancel_url" => $error_url], 400) : redirect($error_url);
            }

            $application->payment_method = array_key_first(Arr::get($order, "payment_source", []));

            $vault_id = Arr::get($order, "payment_source.{$application->payment_method}.attributes.vault.id");
            $authorization_id = Arr::get($order, "purchase_units.0.payments.authorizations.0.id");
            $authorization_status = Arr::get($order, "purchase_units.0.payments.authorizations.0.status");


            // Check if Authorization ID exists
            if ($authorization_status === "CREATED") {
                $paypal_order_data = [
                    "order_id" => $order_id,
                    "vault_id" => $vault_id,
                ];
                if ($authorization_id !== null)
                    $paypal_order_data["authorization_id"] = $authorization_id;
                if ($authorization_status !== null)
                    $paypal_order_data["authorization_status"] = $authorization_status;

                $application->paypal()->create($paypal_order_data);
                $application->save();   // Save payment method
                if (App::environment("production") && !$application->is_update) {
                    Artisan::call("membership:notify-admin", ["subject" => "[SUMA-EV] Neuer Aufnahmeantrag"]);
                }
                return $request->wantsJson() ? response()->json(["success_url" => $success_url, "cancel_url" => $error_url]) : redirect($success_url);
            } else {
                // Check if we can identify an error code
                $message = "Authorization couldn't be created";
                if ($application->payment_method === "card") {
                    $response_code = Arr::get($order, "purchase_units.0.payments.authorizations.0.processor_response.response_code");
                    if ($response_code !== null) {
                        if (Lang::has("spende.execute-payment.card.error.{$response_code}")) {
                            $message = __("spende.execute-payment.card.error.declined_reason", ["reason" => __("spende.execute-payment.card.error.{$response_code}")]);
                        } else {
                            $message = __("spende.execute-payment.card.error.generic");
                        }
                    }
                }
                return $request->wantsJson() ? response()->json(["message" => $message, "cancel_url" => $error_url], 400) : redirect($error_url);
            }
        } else {
            return $request->wantsJson() ? response()->json(["message" => "There is no pending Paypal registration.", "cancel_url" => $error_url], 404) : redirect($error_url);
        }
    }

    public function paypalHandleCancelled(Request $request, $application_id)
    {
        try {
            $parameters = [
                "application_id" => $application_id,
                "error_url" => $request->input("error_url"),
                "expires_at" => intval($request->input("expires_at"))
            ];
            $expiration = Carbon::createFromTimestamp($parameters["expires_at"]);
            if (!hash_equals(hash_hmac("sha256", json_encode($parameters), config("app.key")), $request->input("signature", "")) || now()->isAfter($expiration)) {
                abort(401);
            }
        } catch (Exception $e) {
            abort(401);
        }

        $application = MembershipApplication::find($application_id);
        if ($application !== null) {
            if ($application->paypal !== null)
                $application->paypal()->delete();
        }

        $error_url = $request->input("error_url");
        return $request->wantsJson() ? response()->json(["status" => "OK", "error_url" => $error_url]) : redirect($error_url);
    }

    public function adminIndex(Request $request)
    {
        $membership_applications = MembershipApplication::finishedAdmin()->get();
        $membership_update_requests = MembershipApplication::updateRequestsAdmin()->get();
        $reduction_requests = MembershipApplication::reductionRequests()->get();
        $unfinished_applications = MembershipApplication::unfinishedUser()->get();
        return response(view(
            "admin.membership.index",
            [
                "title" => "Aufnahmeanträge",
                "membership_applications" => $membership_applications,
                "membership_update_requests" => $membership_update_requests,
                "reduction_requests" => $reduction_requests,
                "unfinished_applications" => $unfinished_applications,
                "css" => [mix("/css/admin/membership.css")],
                "js" => [mix("/js/admin/membership.js")]
            ]
        ));
    }

    public function adminMembershipReduction(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "reduction_id" => "required|uuid"
        ]);
        if ($validator->fails()) {
            abort(404);
        }
        $reduction_id = $validator->validated()["reduction_id"];
        $entry = MembershipApplication::reductionRequests()->whereRelation("reduction", "id", "=", $reduction_id)->first();
        if ($entry === null || !file_exists($entry->reduction->file_path)) {
            abort(404);
        }
        $filename = "reduction_validation";
        switch ($entry->reduction->file_mimetype) {
            case "application/pdf":
                $filename .= ".pdf";
                break;
            case "image/jpeg":
                $filename .= ".jpeg";
                break;
            case "image/jpg":
                $filename .= ".jpg";
                break;
            case "image/png":
                $filename .= ".png";
                break;
        }
        return response()->file($entry->reduction->file_path, ["Content-Type" => $entry->reduction->file_mimetype, "Content-Disposition" => "inline; filename=$filename"]);
    }

    public function adminMembershipReductionAccept(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "id" => "required|uuid",
            "reduction_until" => ["required", "date", Rule::date()->afterOrEqual(now()), Rule::date()->beforeOrEqual(now()->addYears(20))]
        ]);
        if ($validator->fails()) {
            return redirect(route("membership_admin_overview", ["error" => "Cannot find specified ID"]));
        }
        $formdata = $validator->validated();
        $id = $formdata["id"];
        $date = $formdata["reduction_until"];

        $application = MembershipApplication::reductionRequests()->whereRelation("reduction", "id", "=", $formdata["id"])->first();
        if ($application === null) {
            return redirect(route("membership_admin_overview", ["error" => "Cannot find application"]));
        }

        $application->reduction->expires_at = $date;
        if ($application->reduction->save()) {
            return redirect(route("membership_admin_overview", ["success" => "Successfully accepted reduction application"]));
        } else {
            return redirect(route("membership_admin_overview", ["error" => "Couldn't update application"]));
        }
    }

    public function adminMembershipReductionDeny(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "id" => "required|uuid",
            "message" => "required|max:500",
        ]);
        if ($validator->fails()) {
            return redirect(route("membership_admin_overview", ["error" => "Cannot find specified ID"]));
        }
        $formdata = $validator->validated();

        $application = MembershipApplication::reductionRequests()->whereRelation("reduction", "id", "=", $formdata["id"])->first();
        if ($application === null) {
            return redirect(route("membership_admin_overview", ["error" => "Cannot find application"]));
        }

        $mail = new ReductionDeny($application, $formdata["message"]);
        if (Mail::mailer("membership")->send($mail) === null) {
            return redirect(route("membership_admin_overview", ["error" => "Couldn't send Mail"]));
        } else {
            $application->reduction->delete();
            $application->amount = null;
            $application->save();
            return redirect(route("membership_admin_overview", ["success" => "Antrag auf verminderten Beitrag abgelehnt"]));
        }
    }

    public function adminAccept(Request $request)
    {
        $application =
            $request->filled("update-request") ?
            MembershipApplication::updateRequestsAdmin()->where("id", "=", $request->input("id", ""))->first() :
            MembershipApplication::finishedAdmin()->where("id", "=", $request->input("id", ""))->first();
        if ($application === null) {
            return redirect(route("membership_admin_overview", ["error" => "Couldn't find application id {$request->input("id")}"]));
        }
        if (!$application->is_update) {
            // Create CiviCRM contact
            if ($application->crm_contact === null) {
                if ($application->company !== null) {
                    $contact = CiviCrm::FIND_COMPANY($application->company);
                    if ($contact === null) {
                        $contact = CiviCrm::CREATE_COMPANY($application->company);
                        if ($contact !== null && Arr::get($contact, "id") !== null) {
                            $application->crm_contact = Arr::get($contact, "id");
                            $application->save();
                            $application->contact()->delete();
                        } else {
                            return redirect(route("membership_admin_overview", ["error" => "[Create CRM contact] An error occured while creating the CRM contact. Please try again."]));
                        }
                    } else if (Arr::get($contact, "id") !== null) {
                        $application->crm_contact = Arr::get($contact, "id");
                        $application->save();
                        $application->contact()->delete();
                    } else {
                        return redirect(route("membership_admin_overview", ["error" => "[Create CRM contact] Couldn't parse remote server response. Please try again."]));
                    }
                } elseif ($application->contact !== null) {
                    $contact = CiviCrm::FIND_CONTACT($application->contact);
                    if ($contact === null) {
                        $contact = CiviCrm::CREATE_CONTACT($application->contact);
                        if ($contact !== null && Arr::get($contact, "id") !== null) {
                            $application->crm_contact = Arr::get($contact, "id");
                            $application->save();
                            $application->contact()->delete();
                        } else {
                            return redirect(route("membership_admin_overview", ["error" => "[Create CRM contact] An error occured while creating the CRM contact. Please try again."]));
                        }
                    } else if (Arr::get($contact, "id") !== null) {
                        $application->crm_contact = Arr::get($contact, "id");
                        $application->save();
                    } else {
                        return redirect(route("membership_admin_overview", ["error" => "[Create CRM contact] Couldn't parse remote server response. Please try again."]));
                    }
                }
            }

            /**
             * Create CiviCRM Membership
             */
            if ($application->crm_membership === null) {
                $memberships = CiviCrm::FIND_MEMBERSHIPS($application->crm_contact);
                if (sizeof($memberships) > 0) {
                    return redirect(route("membership_admin_overview", ["error" => "[Create CRM membership] Contact already has an active membership"]));
                }
                $civicrm_membership = CiviCrm::CREATE_MEMBERSHIP($application);
                if ($civicrm_membership === null) {
                    return redirect(route("membership_admin_overview", ["error" => "[Create CRM membership] An error occured while creating a new membership"]));
                } else {
                    $application->crm_membership = Arr::get($civicrm_membership, "id");
                    $application->amount = null;
                    $application->interval = null;
                    $application->locale = null;
                    $application->key = null;
                    $application->payment_reference = null;
                    $application->save();
                    if ($application->reduction !== null)
                        $application->reduction->delete();
                }
            }
        }


        // Add Payment method to CiviCRM
        if (CiviCrm::UPDATE_MEMBERSHIP($application) !== null) {
            if ($application->paypal !== null) {
                if ($application->paypal->order_id !== null && $application->paypal->authorization_status === "CREATED") {
                    $application->paypal->vault_id = null;
                    $application->paypal->save();
                    $payments = CiviCrm::MEMBERSHIP_NEXT_PAYMENTS($application->crm_membership);
                    $due_date = Arr::get($payments, "0.due_date");
                    if (now()->diffInDays($due_date) <= 14) {
                        if (($order = PayPal::CAPTURE_PAYMENT(authorization_id: $application->paypal->authorization_id)) !== null) {
                            if ($order !== null) {
                                // We'll only process one purchase unit since we do not create orders with more than that
                                $captures = Arr::get($order, "purchase_units.0.payments.captures", []);
                                foreach ($captures as $capture) {
                                    CiviCrm::HANDLE_PAYPAL_CAPTURE($capture);
                                }
                            }
                        }
                    } else {
                        if (PayPal::VOID_AUTHORIZATION(authorization_id: $application->paypal->authorization_id)) {
                            $application->paypal->order_id = null;
                            $application->paypal->authorization_id = null;
                            $application->paypal->authorization_status = null;
                            $application->save();
                        }
                    }
                } else {
                    $application->paypal->delete();
                }
            } elseif ($application->directdebit !== null) {
                $application->directdebit->delete();
            }
            if ($application->reduction !== null) {
                $application->reduction->delete();
            }
        } else {
            return redirect(route("membership_admin_overview", ["error" => "Couldn't update membership"]));
        }

        if (!$application->is_update) {
            $mail = new WelcomeMail($application->crm_membership, $request->input("message", ""));
            if (Mail::mailer("membership")->send($mail) === null) {
                return redirect(route("membership_admin_overview", ["error" => "Couldn't send welcome Mail"]));
            }
        }

        $application->delete();

        return redirect(route("membership_admin_overview", ["success" => "Membership Request accepted"]));
    }

    public function adminDeny(Request $request)
    {
        $application =
            $request->filled("update-request") ?
            MembershipApplication::updateRequestsAdmin()->where("id", "=", $request->input("id", ""))->first() :
            MembershipApplication::finishedAdmin()->where("id", "=", $request->input("id", ""))->first();
        if ($application === null) {
            return redirect(route("membership_admin_overview", ["error" => "Couldn't find application id {$request->input("id")}"]));
        }

        if ($application->directdebit !== null) {
            $application->directdebit->delete();
        }
        if ($application->paypal !== null) {
            $application->paypal->delete();
        }
        $application->delete();

        if ($request->filled("message")) {
            $mail = new ApplicationDeny($application, $request->input("message", ""));
            Mail::mailer("membership")->send($mail);
        }

        return redirect(route("membership_admin_overview", ["success" => "Membership Request deleted"]));
    }

    private function createPayPalAuthorizeOrder(MembershipApplication $application, string $payment_method, string $success_url, string $error_url)
    {
        $parameters = ["application_id" => $application->id, "error_url" => $error_url, "success_url" => $success_url, "expires_at" => now()->addHours(3)->timestamp];
        $parameters["signature"] = hash_hmac("sha256", json_encode($parameters), config("app.key"));
        $success_url = route("membership_paypal_authorized", $parameters);

        $parameters = ["application_id" => $application->id, "error_url" => $error_url, "expires_at" => now()->addHours(3)->timestamp];
        $parameters["signature"] = hash_hmac("sha256", json_encode($parameters), config("app.key"));
        $error_url = route("membership_paypal_cancelled", $parameters);

        // We need a mandate reference so we can cross reference any given paypal order to the membership
        $start_date = now();
        while ($application->payment_reference === null) {
            if (now()->diffInSeconds($start_date, true) > 5) {
                return redirect($error_url);
            }
            $date = now();
            sleep(1);
            $memberships = CiviCrm::FIND_MEMBERSHIPS(mandate: $date->format("YmdHis"));
            if ($memberships !== null && sizeof($memberships) === 0) {
                $application->payment_reference = "M" . $date->format("YmdHis");
                $application->save();
            }
        }
        $application->payment_method = $payment_method;
        $order = PayPal::CREATE_AUTHORIZE_ORDER($application, $success_url, $error_url);
        $application->payment_method = null;

        if ($order === null) {
            return \Request::wantsJson() ? response()->json(["message" => "Error creating order", "cancel_url" => $error_url], 400) : redirect($error_url);
        }

        Cache::put("membership:paypal:orderid:{$application->id}", $order["id"], now()->addDay());

        if ($order["status"] === "PAYER_ACTION_REQUIRED") {
            foreach ($order["links"] as $link) {
                if ($link["rel"] === "payer-action") {
                    return \Request::wantsJson() ? response()->json(["order_id" => $order["id"], "cancel_url" => $error_url, "success_url" => $success_url]) : redirect($link["href"]);
                }
            }
        } else if (Arr::get($order, "status") === "CREATED") {
            return \Request::wantsJson() ? response()->json(["order_id" => $order["id"], "success_url" => $success_url, "cancel_url" => $error_url]) : redirect($success_url);
        }
        return \Request::wantsJson() ? response()->json(["message" => "Error creating order", "cancel_url" => $error_url], 400) : redirect($error_url);
    }

    private function generateNewKey()
    {
        $start_time = now();
        do {
            $key = uuid_create();
            $resulthash = md5("membership:key" . microtime(true));
            $mission = [
                "resulthash" => $resulthash,
                "url" => config("metager.metager.keymanager.server") . "/api/json/key/$key",
                "useragent" => "MetaGer",
                "cacheDuration" => 0,   // We'll cache seperately
                "headers" => [
                    "Authorization" => "Bearer " . config("metager.metager.keymanager.access_token"),
                ],
                "proxy" => false,
                "name" => "PayPal",
            ];
            $mission = json_encode($mission);
            Redis::rpush(\App\MetaGer::FETCHQUEUE_KEY, $mission);
            $results = Redis::brpop($resulthash, 10);
            if (!is_array($results)) {
                sleep(1);
                continue;
            }

            $results = json_decode($results[1], true);
            if (!in_array($results["info"]["http_code"], [200])) {
                sleep(1);
                continue;
            }
            $body = json_decode($results["body"], true);
            if ($body["charge"] === 0) {
                return $key;
            }
        } while (now()->diffInSeconds($start_time, true) < 10);


        return null;
    }



}