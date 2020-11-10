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

        $cookie = Cookie::get('key');
        $key = $request->input('key', '');

        if(empty($key) && empty($cookie)){
            $key = 'enter_key_here';
        }elseif(empty($key) && !empty($cookie)){
            $key = $cookie;
        }elseif(!empty($key)){
            $key = $request->input('key');
        }

        return view('key')
            ->with('title', trans('titles.key'))
            ->with('cookie', $key);
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

            $cookie = Cookie::get('key');

            if(empty($key) && empty($cookie)){
                $key = 'enter_key_here';
            }elseif(empty($key) && !empty($cookie)){
                $key = $cookie;
            }elseif(!empty($key)){
                $key = $request->input('key');
            }
            
            return view('key')
            ->with('title', trans('titles.key'))
            ->with('cookie', $key);
        } else {
            return view('key')
                ->with('title', trans('titles.key'))
                ->with('keyValid', false)
                ->with('cookie', 'enter_key_here');
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
