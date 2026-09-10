<?php

namespace App\Console\Commands;

use App\Support\FetcherHeartbeat;
use Illuminate\Console\Command;

/**
 * Exit 0 when the fetch worker is still looping, 1 when it is not.
 *
 * This exists to be a Kubernetes exec probe, replacing
 * `pgrep -f requests:fetcher`. The worker can hold a half-open Redis socket and
 * stop consuming without the process ever dying, which pgrep cannot see and
 * which took search down for fifteen minutes on 2026-09-10 —
 * App\Support\FetcherHeartbeat carries the detail.
 *
 * The worker Deployment runs no HTTP server and no Service selects it, so this
 * is the only channel; there is no /health-check twin to keep in step with, as
 * there is for the scheduler.
 */
class FetcherHealthcheck extends Command
{
    protected $signature = "fetcher:healthcheck";

    protected $description = "Exit non-zero if the fetch worker has stopped looping.";

    public function handle(): int
    {
        [$healthy, $reason] = FetcherHeartbeat::check();

        if (!$healthy) {
            $this->error($reason);

            return 1;
        }

        $this->info($reason);

        return 0;
    }
}
