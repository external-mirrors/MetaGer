<?php

namespace App;

use \Exception;

class QueryTimer
{
    private $start_time;

    private $timings = [];

    public function __construct()
    {
        $this->start_time = microtime(true);
    }

    /**
     * Observes a start for a given name (Typically a function)
     * It will store the name together with the current time
     */
    public function observeStart(String $name)
    {
        if (!empty($this->timings[$name])) {
            throw new Exception("Start Time for the event $name already registered");
        }

        $this->timings[$name]["start"] = microtime(true);
    }

    /**
     * Observes a end for a given name (Typically a function)
     * It will store the name together with the current time
     */
    public function observeEnd(String $name)
    {
        if (empty($this->timings[$name]["start"])) {
            throw new Exception("Start Time for the event $name has not been registered yet");
        }

        $this->timings[$name]["end"] = microtime(true);

        PrometheusExporter::Duration($this->timings[$name]["end"] - $this->timings[$name]["start"], $name);
    }

    /**
     * Observes the total request time from start to finish
     */
    public function observeTotal()
    {
        $total_time = \microtime(true) - $this->start_time;
        PrometheusExporter::Duration($total_time, "Search_Total");
    }
}
