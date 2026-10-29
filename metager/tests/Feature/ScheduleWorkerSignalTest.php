<?php

namespace Tests\Feature;

use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * Regression test for ScheduleWorker::handle() (app/Console/Commands/ScheduleWorker.php)
 * only ever registering a handler for SIGQUIT.
 *
 * Docker's default stop signal for this image is SIGQUIT (inherited STOPSIGNAL
 * from the php-fpm base image), but Kubernetes always sends SIGTERM. Before the
 * fix, SIGTERM had no handler at all, so it hit PHP's default (immediate,
 * ungraceful) disposition — fast, but never running onExit(), never printing
 * "Stopping Scheduler", and never honouring the "finish the run in progress"
 * contract the chart's terminationGracePeriodSeconds comment promises.
 *
 * Checking only "the process is no longer running" does not catch that: an
 * ungraceful kill satisfies it just as well as a graceful exit (this test
 * caught that mistake on itself — with only the SIGQUIT handler in place, both
 * signals still made the process go away, one gracefully and one by the OS
 * default). What has to be asserted is that onExit() actually ran (its log
 * line) and that the process exited cleanly rather than being killed by the
 * signal (WIFSIGNALED).
 *
 * This has to spawn a real PHP process: pcntl_async_signals() is global
 * process state, and what is under test is genuine OS signal delivery, which
 * an in-process PHPUnit call cannot exercise.
 *
 * A residual, narrower race is not covered here and is not fixed by
 * ScheduleWorker's re-registration: a signal arriving while schedule:run() is
 * still *between* tasks lands fine (handlers are reinstalled right after
 * schedule:run() returns), but one arriving *during* one of the individual
 * tasks it runs still hits Illuminate\Console\Scheduling\Event's own
 * mutex-release handler (exit(1)), because that is reinstalled per task, for
 * the task's own duration, by Laravel itself. How much of a window that is
 * depends entirely on which tasks are due that minute (see routes/console.php)
 * and how long each one takes — trivial most minutes, real at :00 once an
 * hourly job is running. This test sidesteps it by waiting for schedule:run to
 * go quiet before signalling, rather than fixing that deeper race.
 */
class ScheduleWorkerSignalTest extends TestCase
{
    #[DataProvider('signals')]
    public function testTheWorkerExitsGracefullyOnSignal(int $signal): void
    {
        $process = proc_open(
            [PHP_BINARY, base_path('artisan'), 'schedule:work-mg', '-v'],
            [1 => ['pipe', 'w'], 2 => ['pipe', 'w']],
            $pipes,
            base_path()
        );
        $this->assertIsResource($process, 'Could not spawn the scheduler process.');
        stream_set_blocking($pipes[1], false);

        try {
            // How long the first schedule:run() takes varies with which tasks
            // are due this minute (routes/console.php) and with how loaded the
            // machine is. Waiting for its output to go quiet for half a second
            // still flaked in CI: a task that is slow says nothing while it
            // runs, the signal landed mid-task and hit Event's own handler
            // (exit 1). Wait for the worker to say it is asleep instead — see
            // the residual race noted in the class docblock.
            $bootDeadline = microtime(true) + 30;
            $output = '';
            while (!str_contains($output, 'Sleeping until the next run')) {
                if (microtime(true) > $bootDeadline) {
                    $this->fail("The scheduler never reached its sleep within 30 seconds. Output so far: {$output}");
                }
                $output .= stream_get_contents($pipes[1]);
                usleep(50_000);
            }

            proc_terminate($process, $signal);

            $deadline = microtime(true) + 5;
            do {
                $output .= stream_get_contents($pipes[1]);
                $status = proc_get_status($process);
                if (!$status['running']) {
                    break;
                }
                usleep(50_000);
            } while (microtime(true) < $deadline);
            $output .= stream_get_contents($pipes[1]);

            $this->assertFalse(
                $status['running'],
                'The scheduler did not exit within 5 seconds of signal '.$signal.
                    ' — it would have been SIGKILLed instead of shutting down gracefully.'
            );
            $this->assertFalse(
                $status['signaled'] ?? false,
                "The scheduler was killed by signal {$signal} rather than exiting on its own — onExit() did not run."
            );
            $this->assertSame(
                0,
                $status['exitcode'],
                "The scheduler exited with code {$status['exitcode']} instead of a clean 0."
            );
            $this->assertStringContainsString(
                'Stopping Scheduler',
                $output,
                "onExit() never ran — its log line is missing from: {$output}"
            );
        } finally {
            if (proc_get_status($process)['running'] ?? false) {
                proc_terminate($process, SIGKILL);
            }
            proc_close($process);
        }
    }

    public static function signals(): array
    {
        return [
            'SIGQUIT — docker compose stop\'s default here' => [SIGQUIT],
            'SIGTERM — what Kubernetes always sends' => [SIGTERM],
        ];
    }
}
