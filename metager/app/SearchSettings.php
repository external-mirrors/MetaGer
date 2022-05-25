<?php

namespace App;

class SearchSettings
{

    public $jskey = null;
    public $javascript_enabled = false;
    public $header_printed = false;

    // Captcha Related
    public $verification_id = null;
    public $verification_count = 0;

    public function __construct()
    {
    }
}
