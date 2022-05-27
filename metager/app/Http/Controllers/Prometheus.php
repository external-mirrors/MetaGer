<?php

namespace App\Http\Controllers;

use Prometheus\CollectorRegistry;
use Prometheus\RenderTextFormat;

class Prometheus extends Controller
{
    const PROMETHEUS_NAMESPACE = "metager";

    public function metrics()
    {
        // Export FPM Metrics before Scraping
        $this->exportFpmMetrics();

        $registry = CollectorRegistry::getDefault();

        $renderer = new RenderTextFormat();
        $result = $renderer->render($registry->getMetricFamilySamples());

        return response($result, 200)
            ->header('Content-Type', RenderTextFormat::MIME_TYPE);
    }

    private function exportFpmMetrics()
    {
        $registry = CollectorRegistry::getDefault();

        $fpm_status = \fpm_get_status();

        $prometheus_prefix = "fpm_";

        // Accepted Connections
        $counter = $registry->getOrRegisterGauge(self::PROMETHEUS_NAMESPACE, $prometheus_prefix . "accepted_conn", "Number of accepted FPM connections");
        $counter->set($fpm_status["accepted-conn"]);

        // Listen Queue
        $counter = $registry->getOrRegisterGauge(self::PROMETHEUS_NAMESPACE, $prometheus_prefix . "listen_queue", "Size of FPM Listen Queue");
        $counter->set($fpm_status["listen-queue"]);

        // Max Listen Queue
        $counter = $registry->getOrRegisterGauge(self::PROMETHEUS_NAMESPACE, $prometheus_prefix . "max_listen_queue", "Size of FPM Max Listen Queue");
        $counter->set($fpm_status["max-listen-queue"]);

        // Listen Queue Length
        $counter = $registry->getOrRegisterGauge(self::PROMETHEUS_NAMESPACE, $prometheus_prefix . "listen_queue_length", "Size of FPM Listen Queue Length");
        $counter->set($fpm_status["listen-queue-len"]);

        // Idle Processes
        $counter = $registry->getOrRegisterGauge(self::PROMETHEUS_NAMESPACE, $prometheus_prefix . "idle_processes", "Number of FPM Idle Processes");
        $counter->set($fpm_status["idle-processes"]);

        // Active Processes
        $counter = $registry->getOrRegisterGauge(self::PROMETHEUS_NAMESPACE, $prometheus_prefix . "active_processes", "Number of FPM Active Processes");
        $counter->set($fpm_status["active-processes"]);

        // Active Processes
        $counter = $registry->getOrRegisterGauge(self::PROMETHEUS_NAMESPACE, $prometheus_prefix . "total_processes", "Number of FPM Total Processes");
        $counter->set($fpm_status["total-processes"]);

        // Max Active Processes
        $counter = $registry->getOrRegisterGauge(self::PROMETHEUS_NAMESPACE, $prometheus_prefix . "max_active_processes", "Number of FPM Max Active Processes");
        $counter->set($fpm_status["max-active-processes"]);

        // Max Children Reached
        $counter = $registry->getOrRegisterGauge(self::PROMETHEUS_NAMESPACE, $prometheus_prefix . "max_children_reached", "FPM Max Children Reached");
        $counter->set($fpm_status["max-children-reached"]);

        // Slow Requests
        $counter = $registry->getOrRegisterGauge(self::PROMETHEUS_NAMESPACE, $prometheus_prefix . "slow_requests", "FPM Slow Requests");
        $counter->set($fpm_status["slow-requests"]);

        if (sizeof($fpm_status["procs"]) > 0) {
            $request_memory_sum = 0;
            $request_cpu_sum = 0;
            foreach ($fpm_status["procs"] as $proc) {
                $request_cpu_sum += $proc["last-request-cpu"];
                $request_memory_sum += $proc["last-request-memory"];
            }
            // CPU
            $counter = $registry->getOrRegisterGauge(self::PROMETHEUS_NAMESPACE, $prometheus_prefix . "cpu", "FPM Average of CPU Utilization");
            $counter->set($request_cpu_sum / sizeof($fpm_status["procs"]));
            // Memory
            $counter = $registry->getOrRegisterGauge(self::PROMETHEUS_NAMESPACE, $prometheus_prefix . "memory", "FPM Average of Memory Utilization");
            $counter->set($request_memory_sum / sizeof($fpm_status["procs"]));
        }
    }
}
