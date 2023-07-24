<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Jenssegers\Agent\Agent;
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
            return redirect(route("resultpage", ["eingabe" => $eingabe]));
        }

        $optionParams = ['param_sprueche', 'param_newtab', 'param_maps', 'param_autocomplete', 'param_lang', 'param_key'];
        $option_values = [];

        foreach ($optionParams as $param) {
            $value = $request->input($param);
            if ($value) {
                $option_values[$param] = $value;
            }
        }

        $autocomplete = 'on';
        if (in_array('autocomplete', array_keys($option_values))) {
            $autocomplete = $option_values['autocomplete'];
        }

        return view('index')
            ->with('title', trans('titles.index'))
            ->with('homeIcon')
            ->with('agent', new Agent())
            ->with('navbarFocus', 'suche')
            ->with('focus', $request->input('focus', 'web'))
            ->with('time', $request->input('param_time', '1500'))
            ->with('request', $request->input('request', 'GET'))
            ->with('option_values', $option_values)
            ->with('autocomplete', $autocomplete)
            ->with('pluginmodal', $request->input('plugin-modal', 'off'))
            ->with('css', [mix('css/themes/startpage/light.css')])
            ->with('darkcss', [mix('css/themes/startpage/dark.css')]);
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