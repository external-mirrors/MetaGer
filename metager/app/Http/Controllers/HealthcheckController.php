<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use Carbon\Carbon;

class HealthcheckController extends Controller
{

    /**
     * Check if the server is ready 
     */
    public function liveness()
    {
        return response('ok', 200);
    }

    public function livenessScheduler()
    {
        $lastSchedule = Redis::get(\App\Console\Commands\Heartbeat::REDIS_KEY);
        if (empty($lastSchedule)) {
            abort(500, "No heartbeat yet");
        }
        $lastSchedule = Carbon::createFromFormat('Y-m-d H:i:s', $lastSchedule);

        if (Carbon::now()->diffInMinutes($lastSchedule) > 1) {
            abort(500, "Last heartbeat too long ago");
        } else {
            return response('ok', 200);
        }
    }

    public function livenessWorker()
    {
        $lastSchedule = Redis::get(\App\Console\Commands\RequestFetcher::HEALTHCHECK_KEY);
        if (empty($lastSchedule)) {
            abort(500, "No heartbeat yet");
        }
        $lastSchedule = Carbon::createFromFormat(\App\Console\Commands\RequestFetcher::HEALTHCHECK_FORMAT, $lastSchedule);

        if (Carbon::now()->diffInMinutes($lastSchedule) > 1) {
            abort(500, "Last heartbeat too long ago");
        } else {
            return response('ok', 200);
        }
    }
}
