<?php

namespace App;

use \Exception;

/**
 * Wall-clock timings for the phases of one search, reported to Prometheus.
 *
 * Bound as a container singleton, which under FPM means one per request because
 * the process handles one request. It is not one per *search*, and the two are
 * only the same thing by accident of the deployment: anything that handles two
 * searches in one application — a test, a queue worker, Octane — used to get a
 * 500 on the second, because observeStart() refused a name it had already seen
 * and MetaGerSearch@search starts with "Search_CheckSpecialSearches" every
 * time.
 *
 * So a name that has already been *completed* starts a fresh measurement: that
 * is a second search, and the first one's duration has already gone to
 * Prometheus. A name that is still open is a different matter — observeStart()
 * twice with no observeEnd() between is a programming error, the timing it
 * would produce is meaningless, and that still throws.
 */
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
        if (isset($this->timings[$name]) && !isset($this->timings[$name]["end"])) {
            throw new Exception("Start Time for the event $name already registered");
        }

        // A completed name means a second search in this process. Its total
        // starts here too, so Search_Total measures the search rather than
        // everything since the process began.
        if (isset($this->timings[$name])) {
            $this->restart();
        }

        $this->timings[$name]["start"] = microtime(true);
    }

    /**
     * Forget the previous search and start timing a new one.
     */
    private function restart(): void
    {
        $this->timings = [];
        $this->start_time = microtime(true);
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
