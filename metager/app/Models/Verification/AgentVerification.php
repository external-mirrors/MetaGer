<?php

namespace App\Models\Verification;

class AgentVerification extends Verification
{


    public function __construct($id = null, $uid = null)
    {
        $this->cache_prefix = "humanverification.agent";

        if (empty($id) || empty($uid)) {
            $request = \request();
            $ip = $request->ip();
            $id = hash("sha1", $_SERVER["AGENT"]);
            $uid = hash("sha1", $_SERVER["AGENT"] . $ip . "uid");
        }

        parent::__construct($id, $uid);
    }

    public static function impersonate($id, $uid)
    {
        return new AgentVerification($id, $uid);
    }
}
