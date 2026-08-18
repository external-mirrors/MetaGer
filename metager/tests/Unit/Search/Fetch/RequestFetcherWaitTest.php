<?php

namespace Tests\Unit\Search\Fetch;

use App\Console\Commands\RequestFetcher;
use Tests\TestCase;

/**
 * What the fetch loop does when a pass produced nothing.
 *
 * The loop runs `curl_multi_exec`, reads whatever finished, pulls new jobs off
 * the Redis queue, and — if none of that produced anything — waits. It used to
 * wait by sleeping ten milliseconds flat, whether or not there were transfers
 * in flight, so a response arriving just after the check went unnoticed for up
 * to that long. A result page waits for the slowest engine, so it came off the
 * top of every search.
 *
 * Timing assertions would make this flaky on a loaded CI runner, and asserting
 * "it was fast" proves less than asserting *what it waited on*. So the two
 * calls that can wait are seams on the command, and these tests are about which
 * one it picks.
 */
class RequestFetcherWaitTest extends TestCase
{
    private function fetcher(int $selectReturns = 1): object
    {
        return new class ($selectReturns) extends RequestFetcher {
            /** @var list<int> microsecond durations slept, in order */
            public array $slept = [];
            /** @var list<float> timeouts passed to curl_multi_select, in order */
            public array $selected = [];

            public function __construct(private int $selectReturns)
            {
                parent::__construct();
            }

            protected function sleepMicroseconds(int $microseconds): void
            {
                $this->slept[] = $microseconds;
            }

            protected function selectOnMultiHandle(float $timeoutSeconds): int
            {
                $this->selected[] = $timeoutSeconds;
                return $this->selectReturns;
            }

            public function wait(int $operationsRunning): void
            {
                $this->waitForActivity($operationsRunning);
            }
        };
    }

    /**
     * The point of the change: while an engine is still answering, the loop
     * waits on the connection rather than on the clock.
     */
    public function testWithTransfersInFlightItWaitsOnTheSockets(): void
    {
        $fetcher = $this->fetcher();
        $fetcher->wait(3);

        $this->assertSame([0.01], $fetcher->selected);
        $this->assertSame([], $fetcher->slept, "It slept a fixed interval while an engine was mid-answer — that delay lands on the result page.");
    }

    /**
     * The timeout is not arbitrary. curl_multi_select cannot see the Redis
     * queue, so it is the only thing that brings the loop back to look for new
     * missions; ten milliseconds is the delay a new search can meet.
     */
    public function testTheSelectTimeoutStillBoundsHowLongANewJobCanWait(): void
    {
        $fetcher = $this->fetcher();
        $fetcher->wait(1);

        $this->assertSame(0.01, $fetcher->selected[0]);
    }

    /**
     * libcurl answers -1 when it has no socket to wait on — it is on a timer of
     * its own. Returning straight to the top of the loop would spin a core flat;
     * the short sleep is what libcurl's own documentation prescribes.
     */
    public function testItSleepsBrieflyWhenThereIsNoSocketToWaitOn(): void
    {
        $fetcher = $this->fetcher(selectReturns: -1);
        $fetcher->wait(2);

        $this->assertSame([0.01], $fetcher->selected);
        $this->assertSame([100], $fetcher->slept, "A -1 from curl_multi_select has to be slept off, or the loop busy-waits.");
    }

    /**
     * Nothing in flight means nothing to select on, and checkNewJobs has just
     * spent up to a second blocked on Redis.
     */
    public function testWithNothingInFlightItSleepsInsteadOfSelecting(): void
    {
        $fetcher = $this->fetcher();
        $fetcher->wait(0);

        $this->assertSame([], $fetcher->selected);
        $this->assertSame([10000], $fetcher->slept);
    }
}
