<?php

namespace App\Http\Controllers;

use App\Mail\LogsLoginCode;
use App\Models\Authorization\LogsUser;
use Auth;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\MessageBag;
use InvoiceNinja\Sdk\InvoiceNinja;
use Mail;
use Spatie\LaravelIgnition\Exceptions\InvalidConfig;
use Validator;

class LogsApiController extends Controller
{
    public function overview(Request $request)
    {
        if (empty(config("metager.invoiceninja.access_token"))) {
            throw new InvalidConfig("Invoiceninja is not configured on this system", 500);
        }
        $invoice = [
            "email" => Auth::guard("logs")->user()->getAuthIdentifier(),
            "company" => "",
            "first_name" => "",
            "last_name" => "",
            "street" => "",
            "postal_code" => "",
            "city" => "",
        ];

        $client = $this->getOrCreateClient($invoice["email"]);
        $contact = $this->getContactFromClient($client, $invoice["email"]);

        if (!empty($client["name"])) {
            $invoice["company"] = $client["name"];
        }
        if (!empty($client["address1"])) {
            $invoice["street"] = $client["address1"];
        }
        if (!empty($client["postal_code"])) {
            $invoice["postal_code"] = $client["postal_code"];
        }
        if (!empty($client["city"])) {
            $invoice["city"] = $client["city"];
        }
        if (!empty($contact["first_name"])) {
            $invoice["first_name"] = $contact["first_name"];
        }
        if (!empty($contact["last_name"])) {
            $invoice["last_name"] = $contact["last_name"];
        }

        $edit_invoice = $request->filled("edit_invoice");
        if (empty($invoice["first_name"]) || empty($invoice["last_name"]) || empty($invoice["street"]) || empty($invoice["postal_code"]) || empty($invoice["city"])) {
            $edit_invoice = true;
        }

        // Populate Abo Settings
        $abo = [
            "interval" => "never",
            "next_invoice" => null,
            "last_invoice" => null,
            "interval_price" => 0,
            "monthly_price" => 0
        ];
        $edit_abo = $request->filled("edit_abo");

        return view('logs.overview', ['title' => __('titles.logs.overview')])->with(
            [
                "css" => [mix("/css/logs.css")],
                "invoice" => $invoice,
                "edit_invoice" => $edit_invoice,
                "abo" => $abo,
                "edit_abo" => $edit_abo,
            ]
        );
    }

    public function updateInvoiceData(Request $request)
    {
        $email = Auth::guard("logs")->user()->getAuthIdentifier();
        $client = $this->getOrCreateClient($email);

        $client["name"] = $request->input("company") ?? "";
        $client["address1"] = $request->input("street") ?? "";
        $client["postal_code"] = $request->input("postal_code") ?? "";
        $client["city"] = $request->input("city") ?? "";

        for ($i = 0; $i < sizeof($client["contacts"]); $i++) {
            if ($client["contacts"][$i]["email"] !== $email)
                continue;
            $client["contacts"][$i]["first_name"] = $request->input("first_name") ?? "";
            $client["contacts"][$i]["last_name"] = $request->input("last_name") ?? "";
        }
        $invoice_client = $this->getInvoiceNinjaClient();
        $invoice_client->clients->update($client["id"], [
            "name" => $client["name"],
            "address1" => $client["address1"],
            "postal_code" => $client["postal_code"],
            "city" => $client["city"],
            "contacts" => $client["contacts"]
        ]);
        return redirect(route("logs:overview"));
    }

    public function createAbo(Request $request)
    {
        return view('logs.abo_create', ['title' => __('titles.logs.overview')])->with(
            [
                "css" => [mix("/css/logs.css")],
            ]
        );
    }
    public function nda(Request $request)
    {
        $nda = file_get_contents(storage_path("app/logs_nda.pdf"));
        return response()->make($nda, 200, [
            "Content-Type" => "application/pdf",
            "Content-Disposition" => "attachment; filename=\"metager_nda_" . now()->format("Y-m-d") . ".pdf\"",
        ]);
    }

    public function admin(Request $request)
    {
        if ($request->isMethod("POST")) {
            $validator = Validator::make($request->all(), [
                "email" => "required|email:rfc,dns",
                "discount" => "required|numeric|min:0|max:100",
            ]);
            if ($validator->fails()) {
                return redirect(route("logs:admin"))->withInput()->withErrors($validator);
            }
            $validated = $validator->validated();
            $user = DB::table("logs_users")->where("email", "=", $validated["email"])->first();
            if (is_null($user)) {
                DB::table("logs_users")->insert([
                    "email" => $validated["email"],
                    "discount" => $validated["discount"],
                    "created_at" => now(),
                    "updated_at" => now()
                ]);
            } else {
                DB::table("logs_users")
                    ->where("email", "=", $validated["email"])->update([
                            "email" => $validated["email"],
                            "discount" => $validated["discount"],
                            "updated_at" => now()
                        ]);
            }
            return redirect(route("logs:admin"));
        } else {
            $users = DB::table("logs_users")->limit(20)->get();

            if ($request->filled("action") && $request->filled("email")) {
                if ($request->input("action") === "delete") {
                    DB::table("logs_users")->where("email", "=", $request->input("email"))->delete();
                    return redirect(route("logs:admin"));
                } else if ($request->input("action") === "update") {
                    $user = DB::table("logs_users")->where("email", "=", $request->input("email"))->first();
                    if (!is_null($user)) {
                        return redirect(route("logs:admin"))->withInput((array) $user);
                    }
                }
            }

            return view("logs.admin", ['title' => __('titles.logs.admin')])->with(["users" => $users, "css" => [mix("/css/admin/logs.css")]]);
        }
    }

    public function mail_logincode(Request $request)
    {
        return new LogsLoginCode(session("login_token", "123456"));
    }

    public function login(Request $request)
    {
        if (!is_null(Auth::guard("logs")->user()) && !is_null(Auth::guard("logs")->user()->getAuthIdentifier())) {
            session()->flash("email", Auth::guard("logs")->user()->getAuthIdentifier());
        }
        if ($request->filled("reset")) {
            session()->flush();
            return redirect(route("logs:login"));
        }
        return view("logs.login", ['title' => __('titles.logs.login')])->with(["css" => [mix("/css/logs.css")]]);
    }
    public function login_post(Request $request)
    {
        $email_validation = "email:rfc,dns";
        if (!$request->filled("code")) {
            $email_validation = "required|" . $email_validation;
        }
        $validator = Validator::make($request->input(), [
            "email" => $email_validation,
            'code' => 'regex:/^\d{6}$/i'
        ]);


        if ($validator->fails()) {
            return redirect(route("logs:login"))->withErrors($validator)->withInput();
        }

        $validated = $validator->validated();
        if (isset($validated["code"])) {
            if (Auth::guard("logs")->validate(["username" => $validated["email"], "password" => $validated["code"]])) {
                $url = session("url.intended");
                return redirect($url);
            } else {
                // Code wasn't correct
                $errors = new MessageBag();
                $errors->add("invalid_logincode", "Login Code was invalid");
                return redirect(route("logs:login"))->withErrors($errors);
            }
        } else {
            Auth::guard("logs")->init($validated["email"]);
        }
        return redirect(route("logs:login"))->with(["email" => $validated["email"]]);
    }

    private function getInvoiceNinjaClient()
    {
        $invoice_client = new InvoiceNinja(config("metager.invoiceninja.access_token"));
        $invoice_client->setUrl(config("metager.invoiceninja.url"));
        return $invoice_client;
    }

    private function getOrCreateClient(string $email)
    {
        // Check if there is already an Invoicing Account for this client
        $invoice_client = $this->getInvoiceNinjaClient();

        $client = null;
        $clients = $invoice_client->clients->all([
            "email" => $email
        ]);
        foreach ($clients["data"] as $tmp_client) {
            if ($tmp_client["group_settings_id"] === config("metager.invoiceninja.logs_group_id")) {
                $client = $tmp_client;
            }
        }

        if (is_null($client)) {
            $invoice_client->clients->create([
                "group_settings_id" => config("metager.invoiceninja.logs_group_id"),
                "contacts" => [
                    [
                        'send_email' => true,
                        'email' => $email,
                    ]
                ]
            ]);
            return $this->getOrCreateClient($email);
        }
        return $client;
    }

    private function getContactFromClient($client, $email)
    {
        foreach ($client["contacts"] as $contact) {
            if ($contact["email"] === $email) {
                return $contact;
            }
        }
    }
}
