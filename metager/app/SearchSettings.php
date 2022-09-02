<?php

namespace App;

class SearchSettings
{

    public $bv_key = null; // Cache Key where data of BV is temporarily stored
    public $javascript_enabled = false;
    public $header_printed = false;

    public function __construct()
    {
    }
}
