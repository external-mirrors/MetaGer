<?php

namespace Metager\App\Models\Assistant;

class Message
{
    readonly string $message;
    readonly MessageType $type;

    public function __construct(string $message, MessageType $type)
    {
        $this->message = $message;
        $this->type = $type;
    }
}