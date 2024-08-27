<?php

namespace App\Http\Controllers;

use App\Mail\LogsLoginCode;
use App\Models\Logs\LogsAccountProvider;
use Artisan;
use Auth;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\MessageBag;
use Spatie\LaravelIgnition\Exceptions\InvalidConfig;
use Validator;
use Str;
use Hash;

class LogsApiController extends Controller
{
    public function overview(Request $request)
    {
        if (empty(config("metager.invoiceninja.access_token"))) {
            throw new InvalidConfig("Invoiceninja is not configured on this system", 500);
        }

        $logs_account = app(LogsAccountProvider::class);
        $logs_client = $logs_account->client;

        $edit_invoice = $request->filled("edit_invoice") || !$logs_client->isDataComplete();

        $email = Auth::guard("logs")->user()->getAuthIdentifier();

        $nda = null;
        $nda_database = DB::table("logs_nda")->where("user_email", "=", $email);
        if (!is_null($nda_database)) {
            $nda = route("logs:nda", ["signed" => 1]);
        }

        return view('logs.overview', ['title' => __('titles.logs.overview')])->with(
            [
                "css" => [mix("/css/logs.css")],
                "edit_invoice" => $edit_invoice,
                "nda" => $nda
            ]
        );
    }

    public function updateInvoiceData(Request $request)
    {
        app(LogsAccountProvider::class)->client->updateData(
            name: $request->input("company") ?? "",
            address1: $request->input("street") ?? "",
            postal_code: $request->input("postal_code") ?? "",
            city: $request->input("city") ?? "",
            first_name: $request->input("first_name") ?? "",
            last_name: $request->input("last_name") ?? "",
        );

        return redirect(route("logs:overview"));
    }

    public function showAbo(Request $request)
    {
        return view('logs.abo_create', ['title' => __('titles.logs.overview')])->with(
            [
                "css" => [mix("/css/logs.css")],
            ]
        );
    }
    public function createAbo(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "interval" => "required|in:never,monthly,quarterly,six-monthly,annual"
        ]);
        if ($validator->fails()) {
            return redirect(route("logs:abo"))->withInput()->withErrors($validator);
        }
        $validated = $validator->validated();

        app(LogsAccountProvider::class)->abo->update($validated["interval"]);

        Artisan::call("logs:create-order");

        return redirect(route("logs:overview"));
    }
    public function nda(Request $request)
    {
        $nda = file_get_contents(storage_path("app/logs_nda.pdf"));
        $date = now("UTC");
        if ($request->filled("signed")) {
            $nda_database = DB::table("logs_nda")->where("user_email", "=", Auth::guard("logs")->user()->getAuthIdentifier())->first();
            if (!is_null($nda_database)) {
                $nda = $nda_database->nda;
                $date = Carbon::createFromFormat("Y-m-d H:i:s", $nda_database->created_at);
            }
        }
        return response()->make($nda, 200, [
            "Content-Type" => "application/pdf",
            "Content-Disposition" => "attachment; filename=\"metager_nda_" . $date . ".pdf\"",
            "Last-Modified" => $date->format("U")
        ]);
    }

    public function createAccessKey(Request $request)
    {
        $email = Auth::guard("logs")->user()->getAuthIdentifier();
        $max_keys = 10;
        $existing_keys = DB::table("logs_access_key")->where("user_email", $email)->get() ?? [];
        $validator = Validator::make(array_merge($request->all(), ["access_key_size" => sizeof($existing_keys)]), [
            "name" => "required|max:25",
            "access_key_size" => "required|numeric|max:" . $max_keys,
        ]);
        if ($validator->fails()) {
            return redirect(route("logs:overview") . "#api-keys")->withInput()->withErrors($validator);
        }
        $validated = $validator->validated();
        $new_key = Str::uuid();
        DB::table("logs_access_key")->insert([
            "user_email" => $email,
            "name" => $validated["name"],
            "key" => Hash::make(Str::uuid()),
            "created_at" => now("UTC")
        ]);
        return redirect(route("logs:overview") . "#api-keys")->withInput([$validated["name"] => $new_key]);
    }

    public function deleteAccessKey(Request $request)
    {
        $email = Auth::guard("logs")->user()->getAuthIdentifier();
        $validator = Validator::make($request->all(), [
            "id" => "required|numeric|max:1000000"
        ]);
        if ($validator->fails()) {
            return redirect(route("logs:overview") . "#api-keys")->withInput()->withErrors($validator);
        }
        $validated = $validator->validated();
        DB::table("logs_access_key")->where("user_email", $email)->delete($validated["id"]);
        return redirect(route("logs:overview") . "#api-keys");
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
                    "created_at" => now("UTC"),
                    "updated_at" => now("UTC")
                ]);
            } else {
                DB::table("logs_users")
                    ->where("email", "=", $validated["email"])->update([
                            "email" => $validated["email"],
                            "discount" => $validated["discount"],
                            "updated_at" => now("UTC")
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

    public function logsApi(Request $request)
    {

    }
}
