<?php

namespace App\Console\Commands;

use App\Support\SchedulerHeartbeat;
use Illuminate\Console\Command;

/**
 * Exit 0 when the scheduler is alive, 1 when it is not.
 *
 * This exists to be a Kubernetes exec probe. The scheduler runs in its own
 * Deployment with no HTTP server beside it, so /health-check/liveness-scheduler
 * — which is the same check over HTTP — is not reachable from its own pod.
 */
class SchedulerHealthcheck extends Command
{
    protected $signature = "schedule:healthcheck";

    protected $description = "Exit non-zero if the scheduler has not run recently.";

    public function handle(): int
    {
        [$healthy, $reason] = SchedulerHeartbeat::check();

        if (!$healthy) {
            $this->error($reason);

            return 1;
        }

        $this->info($reason);

        return 0;
    }
}
