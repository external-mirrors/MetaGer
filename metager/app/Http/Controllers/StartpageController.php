<?php

namespace App\Http\Controllers;

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
            return redirect(route("resultpage", ["eingabe" => $eingabe]));
        }

        $ckey = hash_hmac("sha256", $request->ip() . now()->format("Y-m-d"), config("metager.taketiles.secret"));
        Cache::put($ckey, "1", now()->addSeconds(TilesController::CACHE_DURATION_SECONDS));
        $tiles = TilesController::TILES($ckey);
        $tiles_update_url = route('tiles', ["ckey" => $ckey]);

        return view('index')
            ->with('title', trans('titles.index'))
            ->with('focus', $request->input('focus', 'web'))
            ->with('request', $request->input('request', 'GET'))
            ->with('tiles_update_url', $tiles_update_url)
            ->with('tiles', $tiles)
            ->with('css', [mix('css/themes/startpage/light.css')])
            ->with('js', [mix('js/startpage/app.js')])
            ->with('darkcss', [mix('css/themes/startpage/dark.css')]);
    }

    public function login(Request $request)
    {
        $key = $request->post("key", "");
        if (Str::isUuid($key)) {
            return redirect(route("loadSettings", ["key" => $key]));
        } else {
            return redirect(route("startpage", ["key_error" => $key]));
        }
    }

    public function loadPage($subpage)
    {
        /* TODO CSS und Titel laden
        $css = array(
        'datenschutz' => 'privacy.css',
        );

        if (in_array($subpage, $css)) {
        return view($subpage, [ 'title' => 'Datenschutz Richtlinien', 'css' => $css[$subpage]]);
        } else {
        return view($subpage, [ 'title' => 'Datenschutz Richtlinien']);
        }*/
        return view($subpage, ['title' => 'Datenschutz Richtlinien']);
    }

    public function loadPlugin(Request $request, $locale = "de")
    {
        $link = action('MetaGerSearch@search', []);
        $link .= "?";
        $link .= "eingabe={searchTerms}";
        $key = $request->input('key', '');
        if (!empty($key)) {
            $link .= "&key=" . urlencode($key);
        }
        $response = Response::make(
            view('plugin')
                ->with('link', $link),
            "200"
        );
        $response->header('Content-Type', "application/opensearchdescription+xml");
        return $response;
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