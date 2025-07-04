<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class KeyChanged implements ShouldBroadcast
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
            new PrivateChannel("App.Models.Authorization.Key.{$this->key}"),
        ];
    }
}
