<?php

namespace App\Http\Controllers;

use Cookie;
use Illuminate\Http\Request;
use LaravelLocalization;

class KeyController extends Controller
{
    public function index(Request $request)
    {
        $redirUrl = $request->input('redirUrl', "");

        return view('key')
            ->with('title', trans('titles.key'));

    }

    public function setKey(Request $request)
    {
        $redirUrl = $request->input('redirUrl', "");
        $key = $request->input('key', '');

        if (app('App\Models\Key')->getStatus()) {
            # Valid Key
            $host = $request->header("X_Forwarded_Host", "");
            if (empty($host)) {
                $host = $request->header("Host", "");
            }

            Cookie::queue('key', $key, 525600, '/', null, false, false);
            return redirect($redirUrl);
        } else {
            return view('key')
                ->with('title', trans('titles.key'))
                ->with('keyValid', false);
        }
    }

    public function removeKey(Request $request)
    {
        $redirUrl = $request->input('redirUrl', "");
        Cookie::queue('key', '', 0, '/', null, false, false);
        $url = LaravelLocalization::getLocalizedURL(LaravelLocalization::getCurrentLocale(), action('KeyController@index', ['redirUrl' => $redirUrl]));
        return redirect($url);
    }
}
