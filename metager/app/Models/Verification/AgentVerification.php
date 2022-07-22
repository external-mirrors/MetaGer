<?php

namespace App\Models\Verification;

class AgentVerification extends Verification
{


    public function __construct($id = null, $uid = null)
    {
        $this->cache_prefix = "humanverification.ip";

        $request = \request();
        $ip = $request->ip();

        if (empty($id) || empty($uid)) {
            $id = hash("sha1", $_SERVER["AGENT"]);
            $uid = hash("sha1", $_SERVER["AGENT"] . $ip . "uid");
        }

        parent::__construct($id, $uid);
    }

    public static function impersonate($id, $uid)
    {
        return new IPVerification($id, $uid);
    }
}
