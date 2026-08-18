<?php

namespace App\Http\Controllers;

use App\Support\SchedulerHeartbeat;

class HealthcheckController extends Controller
{

    /**
     * Check if the server is ready
     */
    public function liveness()
    {
        return response('ok', 200);
    }

    /**
     * The same check the scheduler's own exec probe runs (`schedule:healthcheck`);
     * both go through App\Support\SchedulerHeartbeat so they cannot drift apart.
     */
    public function livenessScheduler()
    {
        [$healthy, $reason] = SchedulerHeartbeat::check();

        if (!$healthy) {
            abort(500, $reason);
        }

        return response('ok', 200);
    }
}
