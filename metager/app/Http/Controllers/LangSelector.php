<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use LaravelLocalization;
use URL;

class LangSelector extends Controller
{
    public function index(Request $request)
    {
        // Check if a previous URL is given that we can offer a back button for
        $previous = request()->input("previous_url", URL::previous());

        $allowed_hosts = [
            "metager.de",
            "metager.org"
        ];

        $components = parse_url($previous);
        $previous_url = null; // URL for the back button
        if (is_array($components) && array_key_exists("host", $components)) {
            $host = $components["host"];
            $current_host = request()->getHost();

            $path = "/";
            if (array_key_exists("path", $components)) {
                $path = $components["path"];
            }
            if (array_key_exists("query", $components)) {
                $path .= "?" . $components["query"];
            }
            if (($host === $current_host || in_array($current_host, $allowed_hosts)) && preg_match("/^http(s)?:\/\//", $previous)) { // only if the host of that URL matches the current host
                $previous_url = LaravelLocalization::getLocalizedUrl(null, $path);
            }
        }

        return view('lang-selector')
            ->with("previous_url", $previous_url)
            ->with("title", trans("titles.lang-selector"))
            ->with('css', [mix('css/lang-selector.css')]);
    }
}