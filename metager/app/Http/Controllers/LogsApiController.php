<?php

namespace App\Http\Controllers;

use App\Mail\LogsLoginCode;
use App\Models\Authorization\LogsUser;
use Auth;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\MessageBag;
use Mail;
use Validator;

class LogsApiController extends Controller
{
    public function overview(Request $request)
    {
        return view('logs.overview', ['title' => __('titles.logs.overview')]);
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
        if (!is_null(Auth::guard("logs")->user()->getAuthIdentifier())) {
            session()->flash("email", Auth::guard("logs")->user()->getAuthIdentifier());
        }
        return view("logs.login", ['title' => __('titles.logs.login')]);
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
}
