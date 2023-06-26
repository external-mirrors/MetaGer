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
            $agent = $_SERVER["AGENT"];
            if ($request->hasHeader("Sec-Ch-Ua-Full-Version-List")) {
                $agent .= $request->header("Sec-Ch-Ua-Full-Version-List");
            }
            if ($request->hasHeader("Sec-CH-UA-Platform")) {
                $agent .= $request->header("Sec-CH-UA-Platform");
            }
            if ($request->hasHeader("Sec-CH-UA-Model")) {
                $agent .= $request->header("Sec-CH-UA-Model");
            }
            if ($request->hasHeader("Sec-CH-UA-Platform-Version")) {
                $agent .= $request->header("Sec-CH-UA-Platform-Version");
            }
            $id = hash("sha1", $agent);
            $uid = hash("sha1", $agent . $ip . "uid");
        }

        parent::__construct($id, $uid);
    }

    public static function impersonate($id, $uid)
    {
        return new AgentVerification($id, $uid);
    }
}