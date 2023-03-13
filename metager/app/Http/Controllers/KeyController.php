<?php

namespace App\Http\Controllers;

use App\Models\Authorization\Authorization;
use Cookie;
use Illuminate\Http\Request;
use LaravelLocalization;

class KeyController extends Controller
{
    public function index(Authorization $auth, Request $request)
    {
        $cookieLink = LaravelLocalization::getLocalizedURL(LaravelLocalization::getCurrentLocale(), route('loadSettings', Cookie::get()));

        return view('key')
            ->with('title', trans('titles.key'))
            ->with('authStatus', $auth->canDoAuthenticatedSearch())
            ->with('token', $auth->getToken())
            ->with('cookieLink', $cookieLink);
    }

    public function setKey(Request $request)
    {
        $key_to_set = $request->input('keyToSet', '');
        if (!empty($key_to_set)) {
            Cookie::queue(Cookie::forever('key', $key_to_set, '/', null, true, true));
            return redirect(LaravelLocalization::getLocalizedURL(LaravelLocalization::getCurrentLocale(), route('keyindex')));
        }
    }

    public function removeKey(Request $request)
    {
        $instantRedir = $request->input("ir", "");
        $redirUrl = $request->input('redirUrl', "");
        Cookie::queue(Cookie::forget("key", '/'));
        $url = LaravelLocalization::getLocalizedURL(LaravelLocalization::getCurrentLocale(), action('KeyController@index', ['redirUrl' => $redirUrl]));

        $host = $request->getHttpHost();
        if (!empty($instantRedir) && in_array($host, ["metager.de", "metager.es", "metager.org", "metager3.de", "localhost:8080"])) {
            return redirect($instantRedir);
        } else {
            return redirect($url);
        }
    }
}