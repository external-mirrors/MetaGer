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
 */
class KeyChanged implements ShouldBroadcast, ShouldRescue
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

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
