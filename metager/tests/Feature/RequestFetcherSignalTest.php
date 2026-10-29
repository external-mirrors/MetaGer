<?php

namespace Tests\Feature;

use App\Console\Commands\RequestFetcher;
use Illuminate\Support\Facades\Redis;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * Regression test for a real hang in RequestFetcher::handle()
 * (app/Console/Commands/RequestFetcher.php), confirmed against the running
 * container: `docker compose stop worker` hung for the whole grace period and
 * needed a SIGKILL (exit 137).
 *
 * The actual cause was not missing signal dispatch — that already worked
 * under `artisan` (Symfony Console's Application constructor turns on
 * pcntl_async_signals() unconditionally for every command). It was that the
 * loop's exit condition also required
 * `Redis::get(FPMGracefulStop::REDIS_FPM_STOPPED_KEY) !== NULL`: a handshake
 * from when this command ran as an fpm sidecar in the same pod ("don't stop
 * before fpm, there could be a last job flying in"). Now that the worker is
 * its own Deployment with no 1:1 relationship to any fpm pod
 * (chart/templates/deployment-worker.yaml), that global, un-expiring Redis key
 * only happens to be set at all once *some* fpm pod, anywhere, has ever run
 * its graceful-stop trap — otherwise this command can never exit no matter
 * what signal arrives. The fix removed that condition; this test flushes the
 * key first so it cannot pass by the same accident that hid the bug during
 * manual testing.
 *
 * Also confirms both SIGQUIT (Docker's default stop signal for this image —
 * STOPSIGNAL is inherited from the php-fpm base image) and SIGTERM (what
 * Kubernetes always sends) are handled: before this change SIGTERM had no
 * handler at all.
 *
 * This has to spawn a real PHP process and touch real Redis: what is under
 * test is genuine OS signal delivery and the Redis-backed gate, neither of
 * which an in-process PHPUnit call can exercise.
 */
class RequestFetcherSignalTest extends TestCase
{
    #[DataProvider('signals')]
    public function testTheFetcherExitsGracefullyOnSignal(int $signal): void
    {
        // The bug this pins only reproduces when this key is unset — see the
        // class docblock. A prior manual `fpm:graceful-stop` run (or a prior,
        // unfixed run of this very test) can leave it set forever, which would
        // make this test pass whether or not the real fix is in place.
        Redis::del('fpm_stopped');
        // Cleared so a stamp left by an earlier fetcher cannot pass for this
        // one being ready — see the wait below.
        $fetcherRedis = Redis::connection(RequestFetcher::REDIS_CONNECTION);
        $fetcherRedis->del(RequestFetcher::HEALTHCHECK_KEY);

        $process = proc_open(
            [PHP_BINARY, base_path('artisan'), 'requests:fetcher'],
            [1 => ['pipe', 'w'], 2 => ['pipe', 'w']],
            $pipes,
            base_path()
        );
        $this->assertIsResource($process, 'Could not spawn the fetcher process.');
        stream_set_blocking($pipes[1], false);

        try {
            // The healthcheck key is first written right after the handlers
            // are installed, so its appearance is the earliest moment a signal
            // can be handled. A fixed 500ms used to stand in for this and
            // flaked in CI: on a loaded runner artisan had not finished booting,
            // and SIGTERM hit PHP's default disposition instead.
            stream_set_blocking($pipes[2], false);
            $bootDeadline = microtime(true) + 20;
            $output = '';
            while ($fetcherRedis->get(RequestFetcher::HEALTHCHECK_KEY) === null) {
                $output .= stream_get_contents($pipes[1]) . stream_get_contents($pipes[2]);
                if (microtime(true) > $bootDeadline) {
                    $this->fail(
                        'The fetcher never wrote its healthcheck within 20 seconds. Running: '
                            . var_export(proc_get_status($process)['running'], true) . ". Output: {$output}"
                    );
                }
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
                'The fetcher did not exit within 5 seconds of signal '.$signal.
                    ' — it would have been SIGKILLed instead of shutting down gracefully.'
            );
            $this->assertFalse(
                $status['signaled'] ?? false,
                "The fetcher was killed by signal {$signal} rather than exiting on its own — sig_handler() did not run, or the loop's exit condition never became true."
            );
            $this->assertSame(
                0,
                $status['exitcode'],
                "The fetcher exited with code {$status['exitcode']} instead of a clean 0."
            );
            $this->assertStringContainsString(
                'Terminating Process',
                $output,
                "sig_handler() never ran — its log line is missing from: {$output}"
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
