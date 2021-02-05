<?php

namespace App\Http\Controllers;

use Cookie;
use Illuminate\Http\Request;
use LaravelLocalization;
use \App\Models\Key;

class KeyController extends Controller
{
    public function index(Request $request)
    {
        $redirUrl = $request->input('redirUrl', "");
        $cookie = Cookie::get('key');
        $key = $request->input('keyToSet', '');

        if (empty($key) && empty($cookie)) {
            $key = 'enter_key_here';
        } elseif (empty($key) && !empty($cookie)) {
            $key = $cookie;
        } elseif (!empty($key)) {
            $key = $request->input('key');
        }

        $cookieLink = LaravelLocalization::getLocalizedURL(LaravelLocalization::getCurrentLocale(), route('loadSettings', Cookie::get()));
        return view('key')
            ->with('title', trans('titles.key'))
            ->with('cookie', $key)
            ->with('cookieLink', $cookieLink);
    }

    public function setKey(Request $request)
    {
        $redirUrl = $request->input('redirUrl', "");
        $keyToSet = $request->input('keyToSet');
        $key = new Key($request->input('keyToSet', ''));

        if ($key->getStatus()) {
            # Valid Key
            $host = $request->header("X_Forwarded_Host", "");
            if (empty($host)) {
                $host = $request->header("Host", "");
            }
            Cookie::queue('key', $keyToSet, 525600, '/', null, false, false);
            $settings = Cookie::get();
            $settings['key'] = $keyToSet;
            $cookieLink = LaravelLocalization::getLocalizedURL(LaravelLocalization::getCurrentLocale(), route('loadSettings', $settings));
            return view('key')
                ->with('title', trans('titles.key'))
                ->with('cookie', $keyToSet)
                ->with('cookieLink', $cookieLink);
        } else {
            $cookieLink = LaravelLocalization::getLocalizedURL(LaravelLocalization::getCurrentLocale(), route('loadSettings', Cookie::get()));
            return view('key')
                ->with('title', trans('titles.key'))
                ->with('keyValid', false)
                ->with('cookie', 'enter_key_here')
                ->with('cookieLink', $cookieLink);
        }
    }

    public function removeKey(Request $request)
    {
        $instantRedir = $request->input("ir", "");
        $redirUrl = $request->input('redirUrl', "");
        Cookie::queue("key", "", 0, '/', null, false, false);
        $url = LaravelLocalization::getLocalizedURL(LaravelLocalization::getCurrentLocale(), action('KeyController@index', ['redirUrl' => $redirUrl]));

        $host = $request->getHttpHost();
        if(!empty($instantRedir) && in_array($host, ["metager.de", "metager.es", "metager.org", "metager3.de", "localhost:8080"])){
            return redirect($instantRedir);
        }else{
            return redirect($url);
        }
    }
}
