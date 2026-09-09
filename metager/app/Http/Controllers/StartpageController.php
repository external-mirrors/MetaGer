<?php

namespace App\Http\Controllers;

use App\Authentication\CookieSupport;
use App\Http\Middleware\HttpCache;
use App\Localization;
use Illuminate\Support\Facades\Vite;
use Illuminate\Http\Request;
use Response;

class StartpageController extends Controller
{
    /**
     * Load Startpage accordingly to the given URL-Parameter and Mobile
     *
     * @param  int  $id
     */

    public function loadStartPage(Request $request)
    {
        /**
         * Some Browsers generate example urls for adding search engines that look like
         * https://google.de?q=%s
         * 
         * To make this url work for metager we redirect if the parameter q is filled
         * https://metager.de?q=%s
         */
        if ($request->filled("q")) {
            $eingabe = $request->input("q");

            /**
             * Chrome only adds opensearch descriptions when visiting the startpage
             * turns out a redirect also works.
             */
            if ($eingabe === "opensearch" && Localization::hasValidSignature()) {
                if ($request->filled("url")) {
                    return redirect($request->input("url"));
                }
            }

            return redirect(route("resultpage", ["eingabe" => $eingabe]));
        }

        $tiles = TilesController::TILES();

        $view = view('index')
            ->with('title', trans('titles.index'))
            ->with('focus', $request->input('focus', 'web'))
            ->with('request', $request->input('request', 'GET'))
            ->with('tiles', $tiles)
            ->with('css', [Vite::asset('resources/less/metager/pages/startpage/startpage.less')])
            ->with('js', [Vite::asset('resources/js/startpage/app.js')])
            // Not $warning/$info — see the same note on AccountController::show().
            ->with('cookieNotice', CookieSupport::justAuthenticatedWithoutCookie($request)
                ? trans('login.no_cookies_notice')
                : null);

        // This page has two bodies — the landing page and the search bar — and
        // the key guard picks which. Say so on the wire, and give the browser
        // something to revalidate against; HttpCache::revalidatable() has the
        // reasoning, including why the validator is the body's hash and not an
        // enumerated tuple the way the result page's is.
        return HttpCache::revalidatable($request, Response::make($view));
    }

    /**
     * "Would the startpage render me as signed in right now?"
     *
     * Written for the chrome extension, which did not inject its key in time
     * for the first page load of a browser session and so had the startpage
     * render anonymously underneath it; resources/js/utility.js polled this for
     * five seconds and reloaded when the answer changed. Declarative net
     * request rules removed that race, and the poll went with it (e504c185a,
     * "no longer necessary") — leaving this endpoint without a caller.
     *
     * It has one again, and a narrower one: resources/js/startpage/staleLoginCheck.js
     * asks exactly once, and only when the browser has just restored this page
     * from the back/forward cache. A bfcached page is a photograph — it can
     * show "you are logged out" long after the visitor logged in, and nothing
     * in the HTTP cache headers can prevent that (only `no-store` keeps a page
     * out of the bfcache, and that would cost the startpage its validator).
     *
     * The predicate has to be *the page's*, not a second opinion: it is
     * `index.blade.php`'s `$signedIn`, both halves of it. Reading only the
     * legacy Authorization service — as this did — answers 401 for a
     * webextension visitor holding an anonymous token, whom the guard resolves
     * and for whom the page renders the search bar. The re-check would then
     * confirm a staleness that isn't there and leave the real one unnoticed.
     */
    public function isLoggedIn(Request $request)
    {
        $signedIn = \Auth::guard("key")->user() !== null
            || app(abstract: \App\Models\Authorization\Authorization::class)->loggedIn;

        if ($signedIn) {
            return response()->json([
                "is_bugged_extension" => $request->hasHeader("mg-webext") && $request->header("mg-webext", "") === "1.2"
            ], 200);
        } else {
            return response()->json([], 401);
        }
    }

    public function loadPage($subpage)
    {
        return view($subpage, ['title' => 'Datenschutz Richtlinien']);
    }

    public function loadPlugin(Request $request, $locale = "de")
    {
        $link = action('MetaGerSearch@search') . "?eingabe={searchTerms}";

        $plugin_short_name = self::GET_PLUGIN_SHORT_NAME();

        $suggestLink = route("suggest") . "?source=opensearch&query={searchTerms}";

        $response = Response::make(
            view('plugin')
                ->with('link', $link)
                ->with('plugin_short_name', $plugin_short_name)
                ->with('suggestLink', $suggestLink),
            "200"
        );
        $response->header('Content-Type', "application/opensearchdescription+xml");
        $response->header("Cache-Control", "max-age=3600");
        return $response;
    }

    public static function GET_PLUGIN_SHORT_NAME(): string
    {
        $plugin_short_name = trans('plugin.short_name');

        if (preg_match("/^[a-z]{2}-[A-Z]{2}$/", \Request::segment(1))) {
            $plugin_short_name .= " (" . \Request::segment(1) . ")";
        }
        if (!\App::environment("production")) {
            $plugin_short_name .= " (" . \App::environment() . ")";
        }
        return $plugin_short_name;
    }
}