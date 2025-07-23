<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UserLogin
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public string $login_token;
    public string $key;

    /**
     * Create a new event instance.
     */
    public function __construct(string $login_token, string $key)
    {
        $this->login_token = $login_token;
        $this->key = $key;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new Channel("App.Models.Authorization.Login.{$this->login_token}"),
        ];
    }
}
