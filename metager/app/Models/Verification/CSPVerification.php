<?php

namespace App\Models\Verification;

class CSPVerification extends Verification
{


    public function __construct($csp_errors = null, $id = null, $uid = null)
    {
        $this->cache_prefix = "csp.ip";
        $this->cache_duration_minutes = 60;

        if (empty($id) || empty($uid)) {
            $request = \request();
            $id = "";
            if (\array_key_exists("error_count", $csp_errors)) {
                $id .= $csp_errors["error_count"];
            }
            if (\array_key_exists("line-numbers", $csp_errors)) {
                $id .= json_encode($csp_errors["line-numbers"]);
            }
            if (\array_key_exists("column-numbers", $csp_errors)) {
                $id .= json_encode($csp_errors["column-numbers"]);
            }
            $id = hash("sha1", $id);
            $uid = hash("sha1", $id . $request->ip() . "uid");
        }

        parent::__construct($id, $uid);
    }

    public static function impersonate($id, $uid)
    {
        return new CSPVerification(null, $id, $uid);
    }
}
