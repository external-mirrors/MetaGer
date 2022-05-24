<?php

namespace App;

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
        if (!empty($timings[$name])) {
            throw new Exception("Start Time for the event $name already registered");
        }

        $this->timings[$name]["start"] = microtime(true);
        dd($this->timings);
    }
}
