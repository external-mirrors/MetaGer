<?php

namespace App\Http\Middleware;

use App\Http\Controllers\AnonymousToken;
use App\Models\Authorization\AnonymousTokenPayment;
use App\Models\Authorization\Authorization;
use App\Models\Authorization\KeyAuthorization;
use App\Models\Authorization\TokenAuthorization;
use App\Models\Configuration\Searchengines;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use Symfony\Component\HttpFoundation\Response;

class AuthenticationValidation
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $parameters = [];
        if ($request->filled("eingabe")) {
            $parameters["eingabe"] = $request->input("eingabe");
        }
        /**
         * Abort if a search is unauthorized
         * not considering actual cost of the request
         * This is a very fast and cost efficient way to filter out spam requests
         */
        if (!app(Authorization::class)->canDoAuthenticatedSearch(false)) {
            return redirect(route("startpage", $parameters));
        }
        /** First authorization check passed. Now we can calculate the actual cost of the search */
        app(Searchengines::class);  // Is needed so we know the cost of a search
        $authorization = app(Authorization::class);
        $cost = $authorization->cost;
        $authorized = false;
        if ($authorization instanceof KeyAuthorization) {
            /** Abort if key doesn't cover the acual cost of this request */
            $authorized = $authorization->canDoAuthenticatedSearch();
        } elseif ($authorization instanceof TokenAuthorization) {
            // Handle different versions of Tokenauthorization depending of source (app|webextension) and their respective versions
            if ($request->header("tokensource", "app") === "webextension") {
                if (version_compare($request->header("Mg-Webext", "0.0"), "1.2", ">=") && $request->hasHeader("anonymous-token-payment-id")) {
                    // New Token authorization system triggered
                    $payment_id = $request->header("anonymous-token-payment-id");
                    if (!uuid_is_valid($payment_id))
                        abort(400);
                    $token_payment = new AnonymousTokenPayment($cost, [], []);
                    $payment_uid = $token_payment->publish($payment_id);
                    if ($payment_uid !== null) {

                    }

                } else {
                    if (!($authorized = $authorization->canDoAuthenticatedSearch())) {
                        /** Version 1.2 of webextension introduced a new token payment strategy */
                        $url = route("resultpage", parameters: $request->all());
                        return response()->view("resultpages.tokenauthorization", ["title" => "MetaGer Anonymous Tokens", "cost" => $cost, "resultpage" => $url]);
                    }
                }
            } else {
                // The android app currently uses the cost token to apply token payments
                // ANd applies those on the following request
                $url = route("resultpage", $request->all());
                \Cookie::queue("cost", $cost, 0);
                return redirect($url);
            }
        }

        if ($authorized === true) {
            return $next($request);
        } else {
            return redirect(route("startpage", $parameters));
        }
    }
}
