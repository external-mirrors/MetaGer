<?php

namespace App;

class DynamicEngineParameters {

    // Returns a string notating the Date Range of the last year
    // The value is used as Parameter for the Bing search engine
    // freshness Parameter
    public static function FreshnessYearBing() {
        $now = \Carbon::now()->format("Y-m-d");
        $lastYear = \Carbon::now()->subYear()->format("Y-m-d");
        return $lastYear . ".." . $now;
    }
}