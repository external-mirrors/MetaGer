<?php

namespace App\Models\Verification;

use Carbon;
use Cookie;
use Exception;

class CookieVerification extends Verification
{
    const COOKIE_NAME = "hv_key";
    const EXPIRATION_FORMAT = "Y-m-d H:i:s";

    public function __construct($hv_key = null)
    {
        $this->cache_prefix = "humanverification.cookie";

        if (empty($hv_key)) {
            $hv_key = self::getKeyFromCookie();
            if (empty($hv_key)) {
                throw new Exception("Cannot find Cookie");
            }
        }

        parent::__construct($hv_key, $hv_key);
    }

    public static function impersonate($id, $uid)
    {
        return new CookieVerification($id, $uid);
    }

    public static function getKeyFromCookie()
    {
        if (!Cookie::has(self::COOKIE_NAME)) {
            return null;
        }
        $hv_data = Cookie::get(self::COOKIE_NAME);
        $hv_data = \base64_decode($hv_data);
        if (!$hv_data) {
            return null;
        }
        $hv_data = \json_decode($hv_data);
        if ($hv_data === null) {
            return null;
        }

        if (empty($hv_data->expiration) || empty($hv_data->key) || empty($hv_data->verification)) {
            return null;
        }

        // Check authenticity of Cookie
        $required_hash = \hash_hmac("sha256", $hv_data->expiration . $hv_data->key, config("metager.metager.proxy.password"));
        if (!\hash_equals($required_hash, $hv_data->verification)) {
            return null;
        }

        // Check if Cookie is expired
        $expiration = Carbon::createFromFormat(self::EXPIRATION_FORMAT, $hv_data->expiration);
        if ($expiration < now()) {
            return null;
        }

        return $hv_data->key;
    }

    public static function createCookie()
    {
        $expiration = now()->addMonths(1);
        $key = \md5(\microtime(true));
        $hv_data = [
            "expiration" => $expiration->format(self::EXPIRATION_FORMAT),
            "key" => $key,
            "verification" => \hash_hmac("sha256", $expiration->format(self::EXPIRATION_FORMAT) . $key, config("metager.metager.proxy.password")),
        ];
        Cookie::queue("hv_key", base64_encode(json_encode($hv_data)), $expiration->diffInMinutes(now()), "/", null, true, true);
    }
}
