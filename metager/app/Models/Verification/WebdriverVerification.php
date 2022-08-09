<?php

namespace App\Models\Verification;

class WebdriverVerification extends Verification
{


    public function __construct($id = null, $uid = null)
    {
        $this->cache_prefix = "webdriver.ip";
        $this->cache_duration_minutes = 60;

        if (empty($id) || empty($uid)) {
            $request = \request();
            $id = hash("sha1", "webdriver");
            $uid = hash("sha1", "webdriver" . $request->ip() . "uid");
        }

        parent::__construct($id, $uid);
    }

    public static function impersonate($id, $uid)
    {
        return new WebdriverVerification($id, $uid);
    }
}
