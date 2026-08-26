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
        // pcntl_signal() alone only installs the handler at the OS level; it
        // needs async signals on (or an explicit pcntl_signal_dispatch() call)
        // to actually be invoked. Symfony Console's own Application constructor
        // already turns this on unconditionally for every artisan command (it
        // builds a SignalRegistry whether or not the command subscribes to
        // anything), so this call is a no-op in practice today — kept explicit
        // rather than depending on that framework internal.
        pcntl_async_signals(true);
        // The previous bug here was not signal dispatch (that part already
        // worked) — it was that this command only ever registered SIGQUIT.
        // Docker's default stop signal for this image is SIGQUIT (inherited
        // STOPSIGNAL from the php-fpm base image), but Kubernetes always sends
        // SIGTERM, which had no handler at all and so hit PHP's default
        // (immediate, ungraceful) disposition — the "finish the run in
        // progress" contract onExit() implements silently never applied there.
        // See tests/Feature/ScheduleWorkerSignalTest.php.
        $this->installSignalHandlers();
        $this->info("Starting Scheduler");
        $this->call('schedule:run');
        // schedule:run just replaced our handlers with its own: every
        // Illuminate\Console\Scheduling\Event installs a SIGTERM/SIGINT/SIGQUIT
        // handler of its own before running (ensureMutexIsReleasedOnSignal(),
        // gated on $releaseOnTerminationSignals, which defaults to true for
        // every event, not just ones using withoutOverlapping()) so a kill
        // mid-task still releases its mutex — and that handler just does
        // exit(1), permanently, since it is never restored afterward. Without
        // re-installing ours here, a signal arriving anywhere after the first
        // schedule:run — including the whole time this process spends asleep —
        // hits that handler instead of onExit(), and the worker dies with a
        // bare exit code 1 instead of the graceful shutdown this class exists
        // to provide.
        $this->installSignalHandlers();
        while (!$this->should_exit) {
            // Nothing here uses Redis for the next minute, and something in
            // front of it will hang up in less than that. See
            // releaseIdleConnections().
            $this->releaseIdleConnections();
            sleep(seconds: 60 - now()->second + 1);
            // sleep() being interrupted by a signal does not reliably run the
            // registered handler on its own — confirmed empirically: with
            // pcntl_async_signals(true) on, a long sleep() returns early on
            // signal but the process can reach the end of the script before
            // the handler ever fires, so should_exit is still false here
            // without this. A usleep()-based polling loop does not have this
            // problem; only a single long-blocking sleep() does.
            pcntl_signal_dispatch();
            // Check right after waking, before starting another run: a signal
            // arriving during the sleep above must stop the worker once it
            // wakes, not send it into one more full schedule:run first.
            if ($this->should_exit) {
                break;
            }
            $this->call('schedule:run');
            $this->installSignalHandlers();
        }
        return 0;
    }

    /**
     * (Re-)install this worker's own termination handlers.
     *
     * Has to be called again after every schedule:run() — see the comment at
     * the call sites in handle().
     */
    private function installSignalHandlers(): void
    {
        pcntl_async_signals(true);
        // Docker's default stop signal for this image is SIGQUIT (inherited
        // STOPSIGNAL from the php-fpm base image); Kubernetes always sends
        // SIGTERM. Both are handled so the graceful path in onExit() runs in
        // either place.
        pcntl_signal(SIGQUIT, [$this, "onExit"]);
        pcntl_signal(SIGTERM, [$this, "onExit"]);
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

        // connections() is null, not [], when the manager has never resolved
        // one yet — nothing on this path guarantees schedule:run touched Redis
        // (it may have found nothing due, or hit a non-Redis cache store) —
        // found via CACHE_STORE=array in the test environment, where it
        // crashed here on every run before the process ever reached the
        // signal-handling this method sits next to.
        foreach (array_keys($redis->connections() ?? []) as $name) {
            $redis->purge($name);
        }
    }

    public function onExit()
    {
        $this->info("Stopping Scheduler");
        $this->should_exit = true;
    }
}
