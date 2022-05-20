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

    public function index(\App\Models\Key $key, Request $request)
    {
        $cookieLink = LaravelLocalization::getLocalizedURL(LaravelLocalization::getCurrentLocale(), route('loadSettings', Cookie::get()));
        $key->canChange();
        $changedAt = null;
        if (!empty($key) && !empty($key->keyinfo) && !empty($key->keyinfo->KeyChangedAt)) {
            $changedAt = $key->keyinfo->KeyChangedAt;
            $changedAt = Carbon::createFromFormat('Y-m-d\TH:i:s.u\Z', $changedAt, "Europe/London");
        }
        return view('key')
            ->with('title', trans('titles.key'))
            ->with('keystatus', $key->getStatus())
            ->with('cookie', $key->key)
            ->with('changedAt', $changedAt)
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
            Cookie::queue('key', $keyToSet, 525600, '/', null, false, false);
            $settings = Cookie::get();
            $settings['key'] = $keyToSet;
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
        Cookie::queue("key", "", 0, '/', null, false, false);
        $url = LaravelLocalization::getLocalizedURL(LaravelLocalization::getCurrentLocale(), action('KeyController@index', ['redirUrl' => $redirUrl]));

        $host = $request->getHttpHost();
        if (!empty($instantRedir) && in_array($host, ["metager.de", "metager.es", "metager.org", "metager3.de", "localhost:8080"])) {
            return redirect($instantRedir);
        } else {
            return redirect($url);
        }
    }

    public function changeKeyIndex(\App\Models\Key $key, Request $request)
    {
        if (!$key->canChange()) {
            return redirect(LaravelLocalization::getLocalizedURL(LaravelLocalization::getCurrentLocale(), route('keyindex')));
        }
        return view('keychange', [
            "title" => trans('titles.keychange'),
            "key" => $key->key,
            "css" => [mix('css/keychange/index.css')]
        ]);
    }

    public function removeCurrent(\App\Models\Key $key, Request $request)
    {
        if (!$key->canChange()) {
            return redirect(LaravelLocalization::getLocalizedURL(LaravelLocalization::getCurrentLocale(), route('keyindex')));
        }
        // Reduce Current Key
        $res = $key->reduce(self::KEYCHANGE_ADFREE_SEARCHES);
        if (empty($res) || empty($res->status) || $res->status !== "success") {
            return redirect(LaravelLocalization::getLocalizedURL(LaravelLocalization::getCurrentLocale(), route('keyindex')));
        }
        // Redirect to Cookie Remove URL with redirect to step two
        $validUntil = Carbon::now("Europe/London")->addDays(2);
        $format = "Y-m-d H:i:s";
        $data = [
            "validUntil" => $validUntil->format($format),
            "password" => hash_hmac("sha256", $validUntil->format($format), config("app.key")),
        ];
        $targetUrl = LaravelLocalization::getLocalizedURL(LaravelLocalization::getCurrentLocale(), route('changeKeyTwo', $data));
        $redirUrl = LaravelLocalization::getLocalizedURL(LaravelLocalization::getCurrentLocale(), route('removeCookie', [
            "ir" => $targetUrl
        ]));
        return redirect($redirUrl);
    }

    public function generateNew(\App\Models\Key $key, Request $request)
    {
        // Validate Request Data
        $validUntil = $request->input('validUntil', '');
        $password = $request->input('password', '');
        $format = "Y-m-d H:i:s";

        // Check if Validuntil
        $valid = true;
        if (empty($validUntil)) {
            $valid = false;
        } else {
            $validUntil = Carbon::createFromFormat($format, $validUntil, "Europe/London");
            if (!$validUntil) {
                $valid = false;
            }
        }

        if ($valid && Carbon::now()->diffInSeconds($validUntil) <= 0) {
            $valid = false;
        }
        if ($valid) {
            // Check if hash matches
            $expectedHash = hash_hmac("sha256", $validUntil->format($format), config("app.key"));
            if (!hash_equals($expectedHash, $password)) {
                $valid = false;
            }
        }
        if (!$valid) {
            return redirect(LaravelLocalization::getLocalizedURL(LaravelLocalization::getCurrentLocale(), route('keyindex')));
        }

        // Check if the key already was generated
        if (!$key->checkForChange($password, "")) {
            return redirect(LaravelLocalization::getLocalizedURL(LaravelLocalization::getCurrentLocale(), route('keyindex')));
        }


        return view('keychangetwo', [
            "title" => trans('titles.keychange'),
            "validUntil" => $validUntil,
            "css" => [mix('css/keychange/index.css')]
        ]);
    }

    public function generateNewPost(\App\Models\Key $key, Request $request)
    {
        // Validate Request Data
        $validUntil = $request->input('validUntil', '');
        $password = $request->input('password', '');
        $format = "Y-m-d H:i:s";

        // Check if Validuntil
        $valid = true;
        if (empty($validUntil)) {
            $valid = false;
        } else {
            $validUntil = Carbon::createFromFormat($format, $validUntil, "Europe/London");
            if (!$validUntil) {
                $valid = false;
            }
        }

        if ($valid && Carbon::now()->diffInSeconds($validUntil) <= 0) {
            $valid = false;
        }
        if ($valid) {
            // Check if hash matches
            $expectedHash = hash_hmac("sha256", $validUntil->format($format), config("app.key"));
            if (!hash_equals($expectedHash, $password)) {
                $valid = false;
            }
        }
        if (!$valid) {
            return redirect(LaravelLocalization::getLocalizedURL(LaravelLocalization::getCurrentLocale(), route('keyindex')));
        }

        $validator = Validator::make($request->all(), [
            'newkey' => 'required|min:4|max:20',
        ]);
        if ($validator->fails()) {
            $data = [
                "validUntil" => $validUntil->format($format),
                "password" => hash_hmac("sha256", $validUntil->format($format), config("app.key")),
                "newkey" => $request->input('newkey', ''),
            ];
            $targetUrl = LaravelLocalization::getLocalizedURL(LaravelLocalization::getCurrentLocale(), route('changeKeyTwo', $data));
            return redirect($targetUrl);
        }

        $newkey = $request->input('newkey', '');

        $characters = '0123456789abcdefghijklmnopqrstuvwxyz';
        $randomSuffix = "";
        $suffixCount = 3;
        for ($i = 0; $i < $suffixCount; $i++) {
            $randomSuffix .= $characters[rand(0, strlen($characters) - 1)];
        }
        $newkey = $newkey . $randomSuffix;

        if ($key->checkForChange($password, $newkey)) {
            $result = $key->generateKey(null, self::KEYCHANGE_ADFREE_SEARCHES, $newkey, "Schlüssel gewechselt. Hash $password");
            if (!empty($result)) {
                Cookie::queue('key', $result, 525600, '/', null, false, false);
                return redirect(LaravelLocalization::getLocalizedURL(LaravelLocalization::getCurrentLocale(), route('changeKeyThree', ["newkey" => $result])));
            }
        }
        $data = [
            "validUntil" => $validUntil->format($format),
            "password" => hash_hmac("sha256", $validUntil->format($format), config("app.key")),
        ];
        $targetUrl = LaravelLocalization::getLocalizedURL(LaravelLocalization::getCurrentLocale(), route('changeKeyTwo', $data));
        return redirect($targetUrl);
    }
}
