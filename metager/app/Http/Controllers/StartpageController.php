<?php

namespace App\Http\Controllers;

use App\Localization;
use App\Models\Authorization\KeyAuthorization;
use Cache;
use Illuminate\Http\Request;
use Jenssegers\Agent\Agent;
use Response;
use Str;

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
            if ($eingabe === "opensearch" && $request->hasValidSignature()) {
                if ($request->filled("url")) {
                    return redirect($request->input("url"));
                }
            }

            return redirect(route("resultpage", ["eingabe" => $eingabe]));
        }

        $tiles = TilesController::TILES();

        return view('index')
            ->with('title', trans('titles.index'))
            ->with('focus', $request->input('focus', 'web'))
            ->with('request', $request->input('request', 'GET'))
            ->with('tiles', $tiles)
            ->with('css', [mix('css/themes/startpage/light.css')])
            ->with('js', [mix('js/startpage/app.js')])
            ->with('darkcss', [mix('css/themes/startpage/dark.css')]);
    }

    /**
     * The chrome extension has currently a problem when loading MetaGer as a startpage
     * The extension is not yet initialized when the startpage is loaded and as such the key is not loaded on first load
     * As a temporary fix we can check the login status asynchroniously on the startpage for a few seconds and reload if status changes
     * 
     * @param \Illuminate\Http\Request $request
     * @return void
     */
    public function isLoggedIn(Request $request)
    {
        if (app(abstract: \App\Models\Authorization\Authorization::class)->loggedIn) {
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

        $suggestLink = route("suggest") . "?query={searchTerms}";

        $response = Response::make(
            view('plugin')
                ->with('link', $link)
                ->with('plugin_short_name', $plugin_short_name)
                ->with('suggestLink', $suggestLink),
            "200"
        );
        $response->header('Content-Type', "application/opensearchdescription+xml");
        $response->header("Cache-Control", "no-store");
        return $response;
    }

    public static function GET_PLUGIN_SHORT_NAME(): string
    {
        $plugin_short_name = trans('plugin.short_name');

        if (preg_match("/^[a-z]{2}-[A-Z]{2}$/", \Request::segment(1))) {
            $plugin_short_name .= " (" . \Request::segment(1) . ")";
        }
        if (!\App::environment("production")) {
            $plugin_short_name = "(dev) " . $plugin_short_name;
        }
        return $plugin_short_name;
    }

    public function berlin(Request $request)
    {
        $link = "";
        $password = "";
        if ($request->filled('eingabe')) {
            $password = config("metager.metager.keys.berlin");
            $password = md5($request->input('eingabe') . " -host:userpage.fu-berlin.de" . $password);
            $link = "/meta/meta.ger3?eingabe=" . $request->input('eingabe') . " -host:userpage.fu-berlin.de&focus=web&password=" . $password . "&encoding=utf8&lang=all&site=fu-berlin.de&quicktips=off&out=results-with-style";
        }
        return view('berlin')
            ->with('title', 'Testseite für die FU-Berlin')
            ->with('link', $link)
            ->with('password', $password);
    }
}