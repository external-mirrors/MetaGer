<?php

namespace App;

use Carbon;

class DynamicEngineParameters
{

    // Returns a string notating the Date Range of the last year
    // The value is used as Parameter for the Bing search engine
    // freshness Parameter
    public static function FreshnessYearBing()
    {
        $now = \Carbon::now()->format("Y-m-d");
        $lastYear = \Carbon::now()->subYear()->format("Y-m-d");
        return $lastYear . ".." . $now;
    }

    public static function FreshnessCustomBing()
    {
        // Bings custom date filter uses YYYY-mm-dd..YYYY-mm-dd
        // From and two dates are supplied by parameters ff and ft
        // They already have been checked if they match above pattern in parseFormData function
        $from = \Request::input("ff");
        $to = \Request::input("ft");

        return $from . ".." . $to;
    }

    public static function FreshnessCustomBrave()
    {
        // Bings custom date filter uses YYYY-mm-dd..YYYY-mm-dd
        // From and two dates are supplied by parameters ff and ft
        // They already have been checked if they match above pattern in parseFormData function
        $from = \Request::input("ff");
        $to = \Request::input("ft");

        return $from . "to" . $to;
    }
}