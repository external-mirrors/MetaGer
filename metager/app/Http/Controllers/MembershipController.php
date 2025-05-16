<?php

namespace App\Http\Controllers;

use App\Jobs\ContactMail;
use App\Jobs\MembershipMail;
use App\Jobs\MembershipPayPalUpdateOrder;
use App\Localization;
use App\Mail\WelcomeMail;
use App\Models\Authorization\KeyAuthorization;
use App\Models\Membership\CiviCrm;
use App\Models\Membership\PayPal;
use App\Rules\IBANValidator;
use Arr;
use Cache;
use Closure;
use Cookie;
use Crypt;
use DB;
use Illuminate\Validation\Rule;
use Mail;
use Str;
use Exception;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Redis;
use Validator;


class MembershipController extends Controller
{

    public function test(Request $request)
    {
        $membership_id = 2171; // ToDo dynamically generate list
        PayPal::UPDATE_AUTHORIZED_ORDER($membership_id);
        return response("");
    }
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
            "title" => ['exclude_if:type,company', 'required', 'in:Frau,Herr,Neutral'],
            "firstname" => ['exclude_if:type,company', 'required', 'max:50'],
            "lastname" => ['exclude_if:type,company', 'required', 'max:50'],
            "company" => ['exclude_unless:type,company', 'required', 'max:100'],
            "employees" => ['exclude_unless:type,company', 'required', 'in:1-19,20-199,>200'],
            "email" => "required|email",
            "amount" => 'required|in:5.00,10.00,15.00,custom',
            "custom-amount" => 'exclude_unless:amount,custom|numeric|required|min:2.5',
            "interval" => 'required|in:annual,six-monthly,quarterly,monthly',
            "reduction" => [Rule::excludeIf(floatval($request->input("amount")) >= 5), 'required', 'file', 'max:10485760', 'mimetypes:image/jpeg,image/png,image/jpg,application/pdf'],
            "payment-method" => 'required|in:directdebit,banktransfer,paypal,creditcard',
            "iban" => ["exclude_unless:payment-method,directdebit", "required", new IBANValidator()],
            "accountholder" => ["exclude_unless:payment-method,directdebit", "nullable", "string", "max:100"]
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
        $membership = $validator->validated();
        if ($membership["amount"] === "custom") {
            $membership["amount"] = $membership["custom-amount"];
        }

        $store_key = false;
        $authorization = app(\App\Models\Authorization\Authorization::class);
        if ($authorization instanceof KeyAuthorization && !empty($authorization->key)) {
            $membership["key"] = $authorization->key;
        } else {
            $membership["key"] = $this->generateNewKey();
            $store_key = true;
        }

        $membership["locale"] = Localization::getLanguage() . "-" . Localization::getRegion();

        /**
         * Create or get CiviCRM Contact
         */
        $contact_id = null;
        if (!empty($membership["company"])) {
            $contact = CiviCrm::FIND_COMPANY($membership["company"], $membership["email"]);
            $contact_id = $contact["id"];
        } else {
            $contact = CiviCrm::FIND_CONTACT($membership["title"], $membership["firstname"], $membership["lastname"], $membership["email"]);
            if ($contact === null) {
                $contact = CiviCrm::CREATE_CONTACT($membership["title"], $membership["firstname"], $membership["lastname"], $membership["email"]);
                $contact_id = Arr::get($contact, "id");
            } else {
                $contact_id = $contact["id"];
            }
        }
        if ($contact_id === null)
            throw new Exception("Cannot find or create contact"); // ToDO Better error handling

        /**
         * Create or get CiviCRM Membership
         */
        $memberships = CiviCrm::FIND_MEMBERSHIPS($contact_id);
        if (sizeof($memberships) > 0) {
            throw new Exception("Contact already has an active membership"); // ToDO Better error handling
        }
        $civicrm_membership = CiviCrm::CREATE_MEMBERSHIP($contact_id, $membership);
        $civicrm_membership = CiviCrm::FIND_MEMBERSHIPS(null, $civicrm_membership["id"]);
        $civicrm_membership = Arr::get($civicrm_membership, "0");

        if (empty($membership["company"]) && $membership["amount"] < 5) {
            /**
             * @var \Illuminate\Http\UploadedFile
             */
            $file = $membership["reduction"];
            DB::table("membership_reduction")->insert(["file_path" => storage_path("metager/" . $file->getBasename()), "file_mimetype" => $file->getMimeType(), 'expires_at' => $membership["expires_at"], 'membership_id' => $civicrm_membership["id"]]);
            $file->move(storage_path("metager"), $file->getBasename());
        }

        $success_url = route("membership_success");
        if ($store_key) {
            $final_url = route("membership_success");
            $expires = now()->addMinutes(1)->timestamp;
            $signature = hash_hmac("sha256", $final_url . $expires, config("app.key"));
            $success_url = route("loadSettings", ["key" => $membership["key"], "redirect_url" => $final_url, "expires" => $expires, "signature" => $signature]);
        }
        $error_url = route("membership_form", request()->except(["reduction", "_token"]));

        if ($membership["payment-method"] === "paypal") {
            return $this->createPayPalAuthorizeOrder($civicrm_membership["id"], $membership["payment-method"], $success_url, $error_url);
        } elseif ($membership["payment-method"] === "card") {
            // ToDo: Add
        }

        if (config("metager.metager.civicrm.enabled")) {
            $membership["amount"] = number_format(round(floatval($membership["amount"]), 2), 2, ",", ".") . "€";
            $message = <<<MESSAGE
            Name: {$membership["name"]}
            Email: {$membership["email"]}
            Betrag: {$membership["amount"]}
            Intervall: {$membership["interval"]}
            Zahlungsart: {$membership["payment-method"]}
            MESSAGE;
            // Create Notification
            ContactMail::dispatch("verein@metager.de", "Mitglieder", $membership["name"], $membership["email"], "Neuer Aufnahmeantrag", $message, [], "text/plain")->onQueue("general");
        }
        return redirect($success_url);
    }

    public function paypalWebhook(Request $request)
    {
        if (!PayPal::VALIDATE_WEBHOOK($request))
            abort(401);

        switch ($request->input("event_type")) {
            case "VAULT.PAYMENT-TOKEN.CREATED":
                // There is a new Payment Token. Check if a order ID is attached
                $order_id = $request->input("resource.metadata.order_id");
                if ($order_id !== null) {
                    $updated = DB::table("membership_paypal")->where("order_id", "=", $order_id)->update(["vault_id" => $request->input("resource.id")]);
                    if ($updated > 0) {
                        return response()->json([]);
                    }
                }
                abort(404);
            case "VAULT.PAYMENT-TOKEN.DELETED":
                DB::table("membership_paypal")->where("vault_id", "=", $request->input("resource.id"))->update(["vault_id" => null]);
                // ToDo: Delete payment token in Civicrm aswell
                return response()->json([]);
            case "PAYMENT.AUTHORIZATION.CREATED":
                $authorization_id = $request->input("resource.id");
                $authorization_status = $request->input("resource.status");
                if ($authorization_status === "CREATED") {
                    $custom_id = $request->input("resource.custom_id");
                    if (preg_match("/^pending_(\d+)$/", $custom_id, $matches)) {
                        $custom_id = $matches[1];
                        $membership_entry = DB::table("membership")->where("id", "=", $custom_id)->first();
                        if ($membership_entry !== null) {
                            DB::table("membership_paypal")->where("id", "=", $membership_entry->paypal)->update(["authorization_id" => $authorization_id]);
                        }
                    }
                } else {
                    DB::table("membership_paypal")->where("authorization_id", "=", $authorization_id)->update(["authorization_id" => null]);
                }
                return response()->json([]);
            case "PAYMENT.AUTHORIZATION.VOIDED":
                DB::table("membership_paypal")->where("authorization_id", "=", $request->input("resource.id"))->update(["authorization_id" => null]);
                return response()->json([]);
            case "PAYMENT.CAPTURE.COMPLETED":
                $invoice_id = $request->input("resource.invoice_id");
                if (preg_match("/^contribution_(\d+)$/", $invoice_id, $matches)) {
                    $contribution_id = (int) $matches[1];
                    $amount = (float) $request->input("resource.amount.value", $request->input("resource.amount.total"));
                    $date = new Carbon($request->input("resource.create_time"));
                    $date->setTimezone("Europe/Berlin");
                    $transaction_id = $request->input("resource.id");
                    CiviCrm::CREATE_MEMBERSHIP_PAYPAL_PAYMENT($contribution_id, $amount, $date, $transaction_id);
                    return response()->json([]);
                } else {
                    return response()->json([]);
                }
            case "PAYMENT.CAPTURE.REVERSED":
            case "PAYMENT.CAPTURE.REFUNDED":
                $invoice_id = $request->input("resource.invoice_id");
                if (preg_match("/^contribution_(\d+)$/", $invoice_id, $matches)) {
                    $contribution_id = (int) $matches[1];
                    $amount = (float) $request->input("resource.amount.value", $request->input("resource.amount.total"));
                    $amount = abs($amount) * -1;
                    $date = new Carbon($request->input("resource.create_time"));
                    $date->setTimezone("Europe/Berlin");
                    $transaction_id = $request->input("resource.id");
                    CiviCrm::CREATE_MEMBERSHIP_PAYPAL_PAYMENT($contribution_id, $amount, $date, $transaction_id);
                    return response()->json([]);
                } else {
                    return response()->json([]);
                }
            default:
                return response()->json([]);
        }
        abort(400);
    }

    public function paypalHandleAuthorized(Request $request, $id)
    {
        try {
            $parameters = ["id" => intval($id), "error_url" => $request->input("error_url"), "success_url" => $request->input("success_url"), "expires_at" => intval($request->input("expires_at"))];
            $expiration = Carbon::createFromTimestamp($parameters["expires_at"]);
            if (!hash_equals(hash_hmac("sha256", json_encode($parameters), config("app.key")), $request->input("signature", "")) || now()->isAfter($expiration)) {
                abort(401);
            }
        } catch (Exception $e) {
            abort(401);
        }
        $error_url = $request->input("error_url");
        $success_url = $request->input("success_url");
        try {
            $membership_record = DB::table("membership")->where("id", "=", $id)->firstOrFail();
            $paypal_record = DB::table("membership_paypal")->where("id", "=", $membership_record->paypal)->firstOrFail();
        } catch (Exception $e) {
            return redirect($error_url);
        }

        $order = PayPal::GET_ORDER($paypal_record->order_id);

        if ($order["intent"] === "AUTHORIZE" && $order["status"] === "APPROVED") {
            $order = PayPal::AUTHORIZE_ODER($paypal_record->order_id);
        } else if ($order["status"] !== "COMPLETED") {
            return redirect($error_url);
        }

        // Check if PaymentSource was vaulted
        if (array_key_exists($membership_record->payment_method, $order["payment_source"])) {
            if ($order["payment_source"][$membership_record->payment_method]["attributes"]["vault"]["status"] === "VAULTED") {
                $paypal_record->vault_id = $order["payment_source"][$membership_record->payment_method]["attributes"]["vault"]["id"];
                DB::table("membership_paypal")->where("id", "=", $membership_record->paypal)->update(["vault_id" => $paypal_record->vault_id]);
            }
        }
        if (sizeof($order["purchase_units"]) === 1 && sizeof($order["purchase_units"][0]["payments"]["authorizations"]) === 1) {
            $paypal_record->authorization_id = $order["purchase_units"][0]["payments"]["authorizations"][0]["id"];
            DB::table("membership_paypal")->where("id", "=", $membership_record->paypal)->update(["authorization_id" => $paypal_record->authorization_id]);
        }
        // Check if Authorization ID exists
        if ($paypal_record->vault_id !== null && $paypal_record->authorization_id !== null) {
            return redirect($success_url);
        } else {
            return redirect($error_url);
        }
    }

    public function adminIndex(Request $request)
    {
        $membership_applications = CiviCrm::FIND_MEMBERSHIP_APPLICATIONS();

        $reductions = DB::table("membership_reduction")
            ->select(["membership_reduction.id", "file_path", "file_mimetype", "membership_id", "title", "firstname", "lastname", "company", "email", "amount", "interval"])
            ->leftJoin("membership", "membership.id", "=", "membership_reduction.membership_id")
            ->get();
        return response(view(
            "admin.membership.index",
            [
                "title" => "Aufnahmeanträge",
                "membership_applications" => $membership_applications,
                "reductions" => $reductions,
                "css" => [mix("/css/admin/membership.css")],
                "js" => [mix("/js/admin/membership.js")]
            ]
        ));
    }

    public function adminMembershipReduction(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "reduction_id" => "required|numeric"
        ]);
        if ($validator->fails()) {
            abort(404);
        }
        $reduction_id = $validator->validated()["reduction_id"];
        $entry = DB::table("membership_reduction")->where("id", "=", $reduction_id)->first();
        if ($entry === null || !file_exists($entry->file_path)) {
            abort(404);
        }
        $filename = "reduction_validation";
        switch ($entry->file_mimetype) {
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
        return response()->file($entry->file_path, ["Content-Type" => $entry->file_mimetype, "Content-Disposition" => "inline; filename=$filename"]);
    }

    public function adminMembershipReductionAccept(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "id" => "required|numeric",
            "reduction_until" => ["required", "date", Rule::date()->afterOrEqual(now()), Rule::date()->beforeOrEqual(now()->addYears(20))]
        ]);
        if ($validator->fails()) {
            return redirect(route("membership_admin_overview", ["error" => "Cannot find specified ID"]));
        }
        $id = $validator->validated()["id"];
        $date = $validator->validated()["reduction_until"];

        $reduction_entry = DB::table("membership_reduction")->find($id);
        if ($reduction_entry === null) {
            return redirect(route("membership_admin_overview", ["error" => "Cannot find specified ID"]));
        }
        $membership_entry = DB::table("membership")->where("id", "=", $reduction_entry->membership_id)->first();
        if ($membership_entry === null) {
            return redirect(route("membership_admin_overview", ["error" => "Cannot find specified ID"]));
        }
        DB::table("membership")->where("id", "=", $membership_entry->id)->update(["reduced_until" => $date]);
        DB::table("membership_reduction")->where("id", "=", $id)->delete();
        if (file_exists($reduction_entry->file_path)) {
            unlink($reduction_entry->file_path);
        }
        return redirect(route("membership_admin_overview", ["success" => "Reduzierter Beitrag erfolgreich bestätigt"]));
    }

    public function adminAccept(Request $request)
    {
        $membership_id = $request->input("id");

        $result = CiviCRM::ACCEPT_MEMBERSHIP_APPLICATION($membership_id);

        if (Arr::get($result, "count", 0) !== 1) {
            return redirect(route("membership_admin_overview", ["error" => "Couldn't accept membership"]));
        }

        $mail = new WelcomeMail($membership_id);
        if (Mail::mailer("membership")->send($mail) === null) {
            return redirect(route("membership_admin_overview", ["error" => "Couldn't send welcome Mail"]));
        }
        return redirect(route("membership_admin_overview", ["success" => "Membership Request accepted"]));
    }

    public function adminDeny(Request $request)
    {
        $membership_id = $request->input("id");

        CiviCrm::DELETE_MEMBERSHIP_APPLICATION($membership_id);

        return redirect(route("membership_admin_overview", ["success" => "Membership Request deleted"]));
    }

    private function createPayPalAuthorizeOrder(string $membership_id, string $payment_source, string $success_url, string $error_url)
    {
        $parameters = ["id" => $membership_id, "error_url" => $error_url, "success_url" => $success_url, "expires_at" => now()->addHours(3)->timestamp];
        $parameters["signature"] = hash_hmac("sha256", json_encode($parameters), config("app.key"));
        $success_url = route("membership_paypal_authorized", $parameters);

        $order = PayPal::CREATE_AUTHORIZE_ORDER($membership_id, $payment_source, $success_url, $error_url);

        if ($order === null)
            return redirect($error_url);

        if ($order["status"] === "PAYER_ACTION_REQUIRED") {
            foreach ($order["links"] as $link) {
                if ($link["rel"] === "payer-action") {
                    $paypal_id = DB::table("membership_paypal")->insertGetId(["order_id" => $order["id"], "expires_at" => now()->addDays(3)]);
                    DB::table("membership")->where("id", "=", $membership_id)->update(["paypal" => $paypal_id]);
                    return redirect($link["href"]);
                }
            }
        }
        return redirect($error_url);
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