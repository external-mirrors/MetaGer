<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Broadcasting\ShouldRescue;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Broadcast when a key's charge changes.
 *
 * `ShouldRescue` is what keeps this off the critical path. Broadcasting queues
 * a job, and `QUEUE_CONNECTION` is unset in production so config/queue.php
 * falls back to `database` — which made every dispatch of this event a
 * synchronous Postgres INSERT. The start page and the result page are designed
 * not to need a database, and this one line of an unrelated feature was
 * quietly making them need one: on 2026-09-08 a key holder loading
 * metager.de/ got a 500 out of `Auth::guard("key")->user()->getKeyState()`
 * because `database-rw.postgresql` was briefly unreachable (GlitchTip issue
 * 1209).
 *
 * With the interface, `BroadcastManager::queue()` wraps the push in the
 * framework's `rescue()` helper: the failure is still reported to GlitchTip,
 * but it cannot reach the user. A missed charge broadcast costs a live-updating
 * balance one refresh; it must never cost the page.
 *
 * `ShouldRescue` stopped the failure reaching the user. It did not stop the
 * *attempt*: the push still opened a PDO connection to Postgres on the start
 * page and the result page, and during an outage still paid
 * `DB_CONNECT_TIMEOUT` (3s, config/database.php) for each one before being
 * swallowed — three seconds added to a search, silently, for a broadcast
 * nobody was waiting on. $connection below is what takes it off Postgres
 * altogether; see config/broadcasting.php for why this is scoped to the
 * broadcast rather than done by moving QUEUE_CONNECTION.
 */
class KeyChanged implements ShouldBroadcast, ShouldRescue
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * The queue connection this event's BroadcastEvent job is pushed to, and
     * the queue on it.
     *
     * Read by `BroadcastManager::queue()` — `$event->connection` and
     * `$event->queue`, in that method's own precedence order — not by anything
     * of ours. Public and set per instance because that is the shape the
     * framework looks for; they are serialized with the job, which is harmless.
     *
     * Assigned in the constructor rather than as property defaults so the
     * values stay configurable: the test suite runs QUEUE_CONNECTION=sync and
     * needs this broadcast to stay inline like every other, and a property
     * default would be baked in before config ever loaded.
     */
    public $connection;
    public $queue;

    /**
     * The key that has changed.
     *
     * @var string
     */
    public string $key;

    /**
     * The change in the key's value.
     *
     * @var float
     */
    public float $change;

    /**
     * The new charge for the key.
     *
     * @var float
     */
    public float $new_charge;

    /**
     * Create a new event instance.
     */
    public function __construct(string $key, float $change = 0, float $new_charge = 0)
    {
        $this->key = $key;
        $this->change = $change;
        $this->new_charge = $new_charge;
        $this->connection = config("broadcasting.queue_connection");
        $this->queue = config("broadcasting.queue");
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new Channel("App.Models.Authorization.Key.{$this->key}"),
        ];
    }
}
