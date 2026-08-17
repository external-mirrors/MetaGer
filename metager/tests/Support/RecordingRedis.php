<?php

namespace Tests\Support;

/**
 * Records every Redis command the application issues, and passes them all on.
 *
 * A search talks to Redis a lot: it looks for cached engine answers, pushes a
 * fetch mission per engine, blocks waiting for the first of them, puts each
 * answer back where it found it, and reads each engine's list again on the way
 * to the page. Every one of those is a round trip, they happen one after the
 * other, and in production they cross the network to a Valkey pod rather than a
 * socket on the same host.
 *
 * None of that shows up in a test that only looks at the rendered page, which
 * is why it went unnoticed. Wrapping the manager makes the cost assertable.
 *
 * Wrap this *outside* FakeFetcher, not inside it: the fetch-queue push is the
 * one command the fake intercepts, and it is one of the commands worth counting.
 * Wrapped the other way round, FPM's mission pushes are invisible and the
 * fake's own stand-in for the worker gets counted as if FPM had made it.
 */
class RecordingRedis
{
    /** @var list<array{command: string, key: string}> */
    private array $commands = [];

    public function __construct(private object $inner) {}

    /**
     * @param array<int, mixed> $arguments
     */
    public function __call(string $method, array $arguments): mixed
    {
        $this->record($method, $arguments);

        return $this->inner->{$method}(...$arguments);
    }

    /**
     * Connections taken out of the manager have to be recorded too — the
     * authorization path reaches Redis that way rather than through the facade.
     */
    public function connection(?string $name = null): mixed
    {
        return new class ($this->inner->connection($name), $this) {
            public function __construct(private object $inner, private RecordingRedis $recorder) {}

            /**
             * @param array<int, mixed> $arguments
             */
            public function __call(string $method, array $arguments): mixed
            {
                $this->recorder->recordFromConnection($method, $arguments);

                return $this->inner->{$method}(...$arguments);
            }
        };
    }

    /**
     * @param array<int, mixed> $arguments
     */
    public function recordFromConnection(string $method, array $arguments): void
    {
        $this->record($method, $arguments);
    }

    /**
     * @param array<int, mixed> $arguments
     */
    private function record(string $method, array $arguments): void
    {
        $key = is_string($arguments[0] ?? null) ? $arguments[0] : "";

        $this->commands[] = [
            "command" => strtolower($method),
            // Result keys are md5 hashes of an engine configuration, so they
            // differ per engine and per query and would make any assertion
            // about them a restatement of the hashing.
            "key" => preg_replace('/[0-9a-f]{16,}/', "<hash>", $key),
        ];
    }

    /**
     * How many times a command was issued, whatever the key.
     */
    public function countOf(string $command): int
    {
        return count(array_filter($this->commands, fn(array $c) => $c["command"] === strtolower($command)));
    }

    /**
     * How many times a command was issued against one key. Result-list keys are
     * md5 hashes, so pass "<hash>" for those.
     */
    public function countOfKey(string $command, string $key): int
    {
        return count(array_filter(
            $this->commands,
            fn(array $c) => $c["command"] === strtolower($command) && $c["key"] === $key
        ));
    }

    /**
     * Every command issued, in order, as "command key" strings. For the message
     * of a failing assertion, where the sequence is what explains it.
     *
     * @return list<string>
     */
    public function trace(): array
    {
        return array_map(fn(array $c) => trim($c["command"] . " " . $c["key"]), $this->commands);
    }

    public function total(): int
    {
        return count($this->commands);
    }

    public function forget(): void
    {
        $this->commands = [];
    }
}
