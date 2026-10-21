<?php

namespace Tests\Unit;

use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Contracts\Queue\Job;
use Illuminate\Queue\MaxAttemptsExceededException;
use Tests\TestCase;

/**
 * The broadcast worker runs --tries=1, so a KeyChanged job that resurfaces —
 * because a Valkey failover kept its delete from `:reserved` from going
 * through, or because the worker died holding it — is failed with
 * MaxAttemptsExceededException instead of being delivered late. That is the
 * policy, not a fault: on 2026-10-16 it surfaced as METAGER-19F, 90s
 * (retry_after) after the failed delete of METAGER-19D.
 *
 * Asserted against the handler's own shouldReport(), which is what both the
 * worker and GlitchTip's integration go through.
 */
class BroadcastMaxAttemptsNotReportedTest extends TestCase
{
    private function exhausted(string $connection, string $queue, string $name): MaxAttemptsExceededException
    {
        $job = $this->createMock(Job::class);
        $job->method("getConnectionName")->willReturn($connection);
        $job->method("getQueue")->willReturn($queue);
        $job->method("resolveName")->willReturn($name);

        return MaxAttemptsExceededException::forJob($job);
    }

    public function testAResurfacedBroadcastIsNotReported(): void
    {
        // phpunit.xml forces the broadcast connection to `sync`; production
        // names `redis`. Set it to what the deployed worker serves.
        config(["broadcasting.queue_connection" => "redis", "broadcasting.queue" => "broadcasts"]);

        $this->assertFalse(
            app(ExceptionHandler::class)->shouldReport(
                $this->exhausted("redis", "broadcasts", "App\\Events\\KeyChanged")
            ),
            "--tries=1 dropping a broadcast is the policy working, not an error"
        );
    }

    /** Donation and mail jobs run out of tries for real reasons; keep those. */
    public function testAnyOtherExhaustedJobIsStillReported(): void
    {
        config(["broadcasting.queue_connection" => "redis", "broadcasting.queue" => "broadcasts"]);

        $this->assertTrue(
            app(ExceptionHandler::class)->shouldReport(
                $this->exhausted("database", "donations", "App\\Jobs\\SomeDonationJob")
            )
        );
        $this->assertTrue(
            app(ExceptionHandler::class)->shouldReport(
                $this->exhausted("redis", "default", "App\\Jobs\\SomethingElse")
            ),
            "only the broadcast queue on that connection runs --tries=1"
        );
    }
}
