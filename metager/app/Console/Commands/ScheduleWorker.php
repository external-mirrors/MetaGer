<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ScheduleWorker extends Command
{
    private $should_exit = false;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'schedule:work-mg';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Starts the schedule worker with correct signal handling and graceful shutdown.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        pcntl_signal(SIGQUIT, array(&$this, "onExit"));
        $this->info("Starting Scheduler");
        $this->call('schedule:run');
        do {
            // Nothing here uses Redis for the next minute, and something in
            // front of it will hang up in less than that. See
            // releaseIdleConnections().
            $this->releaseIdleConnections();
            sleep(seconds: 60 - now()->second + 1);
            $this->call('schedule:run');
        } while (!$this->should_exit);
        return 0;
    }

    /**
     * Let go of every Redis connection before sleeping through the next minute.
     *
     * This process is the one part of MetaGer that holds a connection open
     * without using it. It wakes once a minute, and `schedule:run` reaches
     * Redis on its very first line — Laravel asks the cache whether the
     * schedule is paused — so the connection it needs is always one that has
     * been idle for the whole sleep.
     *
     * In front of Valkey sits the chart's HAProxy master proxy, configured
     * `timeout client 30s` / `timeout server 30s`. Sixty is more than thirty,
     * so the connection is *always* gone by the time the next run wants it, and
     * Predis reports the closed socket as "Stream is already at the end". That
     * is not an exception `schedule:run` catches: the command aborts, the
     * process exits, and Kubernetes restarts it — once a minute, for ever.
     *
     * It became visible when the scheduler moved into a Deployment of its own,
     * because `helm --wait` then waits for it and a deploy that used to succeed
     * now times out. As a sidecar the same crash was only a restart count on a
     * pod that was otherwise healthy.
     *
     * Purging is enough to close the sockets: the cache store re-resolves its
     * connection through the manager on every call, so nothing else is holding
     * one, and Predis disconnects in its destructor. The cost is one connection
     * per minute per configured connection, against a proxy already opening one
     * per request.
     *
     * Public so the regression test can call it —
     * tests/Feature/ScheduleWorkerTest. Calling it at any other time is
     * harmless: the next command reconnects.
     */
    public function releaseIdleConnections(): void
    {
        $redis = app("redis");

        foreach (array_keys($redis->connections()) as $name) {
            $redis->purge($name);
        }
    }

    public function onExit()
    {
        $this->info("Stopping Scheduler on SIGQUIT");
        $this->should_exit = true;
    }
}
