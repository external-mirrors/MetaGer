<?php

namespace App\Http\Controllers;

use Cookie;
use Illuminate\Http\Request;
use LaravelLocalization;
use \App\Models\Key;
use \Carbon\Carbon;
use Validator;

class KeyController extends Controller
{
    // How many Ad Free searches should a user get max when he creates a new key
    const KEYCHANGE_ADFREE_SEARCHES = 150;

    public function index(Key $key, Request $request)
    {
        $cookieLink = LaravelLocalization::getLocalizedURL(LaravelLocalization::getCurrentLocale(), route('loadSettings', Cookie::get()));

        return view('key')
            ->with('title', trans('titles.key'))
            ->with('keystatus', $key->getStatus())
            ->with('cookie', $key->key)
            ->with('cookieLink', $cookieLink);
    }

    public function setKey(Request $request)
    {
        $keyToSet = $request->input('keyToSet');
        $key = new Key($request->input('keyToSet', ''));

        $status = $key->getStatus();
        if ($status !== null) {
            # Valid Key
            $host = $request->header("X_Forwarded_Host", "");
            if (empty($host)) {
                $host = $request->header("Host", "");
            }
            Cookie::queue('key', $key->key, 525600, '/', null, true, true);
            $settings = Cookie::get();
            $settings['key'] = $key->key;
            $cookieLink = LaravelLocalization::getLocalizedURL(LaravelLocalization::getCurrentLocale(), route('loadSettings', $settings));
            return redirect(LaravelLocalization::getLocalizedURL(LaravelLocalization::getCurrentLocale(), route('keyindex')));
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
        Cookie::queue("key", "", 0, '/', null, true, true);
        $url = LaravelLocalization::getLocalizedURL(LaravelLocalization::getCurrentLocale(), action('KeyController@index', ['redirUrl' => $redirUrl]));

        $host = $request->getHttpHost();
        if (!empty($instantRedir) && in_array($host, ["metager.de", "metager.es", "metager.org", "metager3.de", "localhost:8080"])) {
            return redirect($instantRedir);
        } else {
            return redirect($url);
        }
    }
}