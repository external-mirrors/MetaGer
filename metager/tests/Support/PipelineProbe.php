<?php

namespace Tests\Support;

/**
 * Remembers which commands were queued inside a pipeline, and forwards them.
 *
 * A pipeline is one round trip carrying several commands, which is exactly what
 * makes it worth using and exactly what makes it opaque: RecordingRedis sees a
 * single `pipeline` call and nothing about what travelled in it. Two different
 * pipelines in one request — the search putting an answer back, the
 * authorization staking a claim — would be indistinguishable.
 *
 * So the commands become the pipeline's key: RecordingRedis records it as
 * `pipeline lpush,expire`, and a test can name the one it means.
 */
class PipelineProbe
{
    /** @var list<string> */
    public array $commands = [];

    public function __construct(private object $inner) {}

    /**
     * @param array<int, mixed> $arguments
     */
    public function __call(string $method, array $arguments): mixed
    {
        $this->commands[] = strtolower($method);

        return $this->inner->{$method}(...$arguments);
    }
}
