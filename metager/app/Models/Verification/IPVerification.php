<?php

namespace App\Models\Verification;

class IPVerification extends Verification
{


    public function __construct($id = null, $uid = null)
    {
        $this->cache_prefix = "humanverification.ip";

        if (empty($id) || empty($uid)) {
            $request = \request();
            $ip = $request->ip();
            $id = hash("sha1", $ip);
            $uid = hash("sha1", $ip . $_SERVER["AGENT"] . "uid");
        }

        parent::__construct($id, $uid);
    }

    public static function impersonate($id, $uid)
    {
        return new IPVerification($id, $uid);
    }
}
