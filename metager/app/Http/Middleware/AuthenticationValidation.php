<?php

namespace App\Http\Middleware;

use App\Http\Controllers\SuggestionController;
use App\Models\Authorization\AnonymousTokenPayment;
use App\Models\Authorization\Authorization;
use App\Models\Authorization\KeyAuthorization;
use App\Models\Authorization\SuggestionDebtAuthorization;
use App\Models\Authorization\TokenAuthorization;
use App\Models\Configuration\Searchengines;
use Auth;
use Closure;
use Cookie;
use Illuminate\Http\Request;
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
         * Our newest authentication form using Laravel Authentication Guard
         * All requests are soon authenticated using the key guard
         * In the meantime we still support the old way of authentication
         * 
         * @var \App\Authentication\KeyUser|null $user
         */
        if (($user = Auth::guard("key")->user()) !== null) {
            // Initialize searchengines and settings so we can estimate the cost of the search
            $suma_cost = app(Searchengines::class)->getCost();
            $suggestion_debt = $this->getSuggestionDebt();

            if ($user->authorize($suma_cost + $suggestion_debt) && $user->makePayment($suggestion_debt)) {
                return $next($request);
            } else {
                return redirect(route("startpage", $parameters));
            }
        } else {
            // ToDo; enable this case once the old authentication is removed
            //return redirect(route("startpage", $parameters));
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
        $suggestion_debt = $this->getSuggestionDebt();
        $authorization->setCost($authorization->getCost() + $suggestion_debt);
        $cost = $authorization->getCost();
        $authorized = false;
        if ($authorization instanceof KeyAuthorization) {
            /** Abort if key doesn't cover the acual cost of this request */
            $authorized = $authorization->canDoAuthenticatedSearch();
        } elseif ($authorization instanceof TokenAuthorization) {
            // Handle different versions of Tokenauthorization depending of source (app|webextension) and their respective versions
            if ($request->header("tokensource", "app") === "webextension") {
                if (version_compare($request->header("Mg-Webext", "0.0"), "1.2", ">=") && $request->hasHeader("anonymous-token-payment-id")) {
                    $response = $this->syncTokenPayment($request, $authorization);
                    if (is_bool($response)) {
                        $authorized = $response;
                    } else {
                        return $response;
                    }
                } else {
                    if (!($authorized = $authorization->canDoAuthenticatedSearch())) {
                        /** 
                         * Version 1.2 of webextension introduced a new token payment strategy 
                         * This will continue to support the old method while we are phasing it out
                         * It requires the now removed mgv url parameter to work correctly
                         */
                        if ($request->filled("mgv")) {
                            return redirect(route("startpage", $parameters));
                        }
                        // There is a bug in this version of webextension where a token header is falsely stored as setting
                        // This results in a endless loop with false authentication. It can be mitigated by deleting this setting
                        Cookie::queue(Cookie::forget("tokens", "/", null));
                        $mgv = md5(microtime(true));
                        $url = route("resultpage", parameters: array_merge($request->all(), ["mgv" => $mgv]));
                        return response()->view("resultpages.tokenauthorization", ["title" => "MetaGer Anonymous Tokens", "cost" => $cost, "method" => $request->method(), "resultpage" => $url]);
                    }
                }
            } else {
                if (version_compare($request->header("mg-app", "0.0"), "5.1.7", "<=") || !$request->hasHeader("anonymous-token-payment-id")) {
                    // The android app currently uses the cost token to apply token payments
                    // ANd applies those on the following request
                    if (!($authorized = $authorization->canDoAuthenticatedSearch())) {
                        $url = route("resultpage", $request->all());
                        \Cookie::queue("cost", $cost, 0);
                        return redirect($url);
                    }
                } else {
                    $response = $this->syncTokenPayment($request, $authorization);
                    if (is_bool($response)) {
                        $authorized = $response;
                    } else {
                        return $response;
                    }
                }
            }
        }

        if ($authorized === true) {
            $this->clearSuggestionDebt($suggestion_debt);
            return $next($request);
        } else {
            return redirect(route("startpage", $parameters));
        }
    }

    private function getSuggestionDebt(): float
    {
        // Clear all pending suggestion requests
        $cache_key = SuggestionController::GENERATE_SUGGEST_CACHE_KEY();
        $list = SuggestionController::GET_SUGGESTION_GROUP_LIST($cache_key);
        foreach ($list as $uuid) {
            SuggestionController::ABORT_SUGGESTION_GROUP_REQUEST($uuid, 423);
        }
        return SuggestionDebtAuthorization::GET_DEBT();
    }

    private function clearSuggestionDebt($suggestion_debt)
    {
        $authorization = app(Authorization::class);
        SuggestionDebtAuthorization::ADD_CREDIT(0.1);
        SuggestionDebtAuthorization::UPDATE_SETTINGS();
        if ($suggestion_debt > 0 && $authorization->makePayment($suggestion_debt)) {
            SuggestionDebtAuthorization::ADD_DEBT($suggestion_debt * -1);
        }
    }

    private function syncTokenPayment(Request $request, TokenAuthorization $authorization)
    {
        $authorization->availableTokens = $authorization->token_payment->receive(0.01);
        $payment_uid = $authorization->token_payment->publish();

        if ($authorization->canDoAuthenticatedSearch()) {
            return true;
        }

        $authorized = false;

        if ($payment_uid !== null && !AnonymousTokenPayment::IS_ASYNC_DISABLED()) {
            // Received tokens are already checked to be valid. No need to validate them here again
            $authorization->token_payment->receive();

            if (!is_null($authorization->token_payment->key)) {
                // Something weird happened which caused anonymous token payment to fail. For convenience purposes we fall back to key
                // Authorization in that case
                $key = $authorization->token_payment->key;
                app()->singleton(Authorization::class, function ($app) use ($key) {
                    return new KeyAuthorization($key);
                });
                $authorization = app(Authorization::class);
                $authorized = $authorization->canDoAuthenticatedSearch();
            } else {
                $authorization->availableTokens = $authorization->token_payment->getAvailableTokenCount();
            }
            if ($authorization->canDoAuthenticatedSearch()) {
                $authorized = true;
            }
        } else {
            // Async Token Authentication is either disabled due to rate limit
            // or no payment did go through. We will fallback to a synchronious payment
            if (!($authorized = $authorization->canDoAuthenticatedSearch())) {
                $url = route("resultpage", parameters: $request->all());
                $parameters = [];
                if ($request->filled("eingabe")) {
                    $parameters["eingabe"] = $request->input("eingabe");
                }
                return response()->view("resultpages.tokenauthorization", ["title" => "MetaGer Anonymous Tokens", "payment" => base64_encode($authorization->token_payment->toJSON()), "method" => $request->method(), "page" => $url, "parameters" => $request->all(), "error_url" => route("startpage", $parameters)]);
            }
        }
        return $authorized;
    }
}
