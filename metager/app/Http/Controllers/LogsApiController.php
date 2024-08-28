<?php

namespace App\Http\Controllers;

use App\Mail\LogsLoginCode;
use App\Models\Logs\LogsAccountProvider;
use Artisan;
use Auth;
use Carbon\Carbon;
use Cookie;
use DB;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\MessageBag;
use Illuminate\Validation\ValidationException;
use RateLimiter;
use Spatie\LaravelIgnition\Exceptions\InvalidConfig;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;
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
        Artisan::call("logs:create-invoice");

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
        $key_id = DB::table("logs_access_key")->insertGetId([
            "user_email" => $email,
            "name" => $validated["name"],
            "key" => Hash::make($new_key),
            "created_at" => now("UTC")
        ]);
        $new_key .= "-" . $key_id;
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
            $user = DB::table("logs_user")->where("email", "=", $validated["email"])->first();
            if (is_null($user)) {
                DB::table("logs_user")->insert([
                    "email" => $validated["email"],
                    "discount" => $validated["discount"],
                    "created_at" => now("UTC"),
                    "updated_at" => now("UTC")
                ]);
            } else {
                DB::table("logs_user")
                    ->where("email", "=", $validated["email"])->update([
                            "email" => $validated["email"],
                            "discount" => $validated["discount"],
                            "updated_at" => now("UTC")
                        ]);
            }
            return redirect(route("logs:admin"));
        } else {
            $users = DB::table("logs_user")->limit(20)->get();

            if ($request->filled("action") && $request->filled("email")) {
                if ($request->input("action") === "delete") {
                    DB::table("logs_user")->where("email", "=", $request->input("email"))->delete();
                    return redirect(route("logs:admin"));
                } else if ($request->input("action") === "update") {
                    $user = DB::table("logs_user")->where("email", "=", $request->input("email"))->first();
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
        $request->headers->set('Accept', 'application/json'); // Force JSON responses
        // Validate authorization
        $email = null;
        if ($request->hasHeader("authorization")) {
            $submitted_token = trim(str_replace("Bearer ", "", $request->header("authorization")));
            $token = "";
            if (strrpos($submitted_token, "-")) {
                $token = substr($submitted_token, 0, strrpos($submitted_token, "-"));
                $id = substr($submitted_token, strrpos($submitted_token, "-") + 1);
            }
            // Validate Token
            if (Str::isUuid($token) && is_numeric($id)) {
                $key_entry = DB::table("logs_access_key")->where("id", $id)->first();
                if (!is_null($key_entry) && Hash::check($token, $key_entry->key)) {
                    $email = $key_entry->user_email;
                }
            }
        }

        if ($email == null) {
            throw new AuthorizationException("You are not authorized to access this resource");
        }


        $validator = Validator::make($request->all(), [
            "start_date" => "required|date|date_format:Y-m-d H:i:s",
            "end_date" => "date|date_format:Y-m-d H:i:s|after:start_date",
            "order" => "in:ascending,descending"
        ]);
        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
        $validated = $validator->validated();
        $start_date = new Carbon($validated["start_date"], "UTC");

        // Validate access for this timerange
        $order_access = DB::table("logs_order")->where("from", "<=", $start_date)->where("to", ">=", $start_date)->first();
        if (is_null($order_access)) {
            throw new AuthorizationException("There is no order linked to your account which includes the specified start_date");
        }

        // Request went through all checks. Now we can check the RateLimiting
        $rl_key = "logs:rl:$email";
        $rl_max_attempts = 60;
        $rl_decay_seconds = 3600;
        $rl_current_attempts = RateLimiter::attempts($rl_key);
        $rl_available_in = RateLimiter::availableIn($rl_key);
        if (RateLimiter::tooManyAttempts(key: $rl_key, maxAttempts: $rl_max_attempts)) {
            throw new TooManyRequestsHttpException($rl_available_in, "Too many requests from this account", null, 0, [
                "X-Rate-Limit-Max" => $rl_max_attempts,
                "X-Rate-Limit-Current" => $rl_current_attempts,
                "X-Rate-Limit-More-In" => $rl_available_in
            ]);
        } else {
            RateLimiter::increment(key: $rl_key, decaySeconds: $rl_decay_seconds);
            $rl_current_attempts = RateLimiter::attempts($rl_key);
            $rl_available_in = RateLimiter::availableIn($rl_key);
        }


        $order = "asc";
        if (isset($validated["order"]) && $validated["order"] === "descending") {
            $order = "desc";
        }


        $default_end_date = clone $start_date;
        $default_end_date->addHours(23)->addMinutes(59)->addSeconds(59)->micros(999999);

        // Make sure to leave some Minutes for logs to come into our DB
        if ($default_end_date->isAfter(now("UTC")->subMinutes(5))) {
            $default_end_date = now("UTC")->subMinutes(5);
        }

        // Check if we can use the end_date supplied by the user
        $end_date = clone $default_end_date;
        if (isset($validated["end_date"])) {
            $end_date = (new Carbon($validated["end_date"], "UTC"))->micros(999999);
            if ($end_date->isAfter($default_end_date)) {
                $end_date = clone $default_end_date;
            }
        }

        // Create CSV Response
        return response()->streamDownload(
            function () use ($start_date, $end_date, $order) {
                // Fetch chunked DB entries
                DB::connection("logs")->table("logs_partitioned")->select(["time", "query"])->whereBetween("time", [$start_date, $end_date])->orderBy("time", $order)->chunk(25000, function (Collection $log_entries) {
                    $f = fopen("php://memory", "r+");
                    foreach ($log_entries as $log_entry) {
                        fputcsv($f, (array) $log_entry, ",", "\"", "\\", PHP_EOL);
                    }
                    rewind($f);
                    echo stream_get_contents($f);
                    fclose($f);
                });
            },
            "mglogs_" . $start_date->getTimestamp() . "_" . $end_date->getTimestamp() . ".csv",
            [
                "Content-Type" => "text/csv",
                "X-Rate-Limit-Max" => $rl_max_attempts,
                "X-Rate-Limit-Current" => $rl_current_attempts,
                "X-Rate-Limit-More-In" => $rl_available_in,
                "X-End-Date-Used" => $end_date->format("Y-m-d H:i:s")
            ]
        );

    }
}
