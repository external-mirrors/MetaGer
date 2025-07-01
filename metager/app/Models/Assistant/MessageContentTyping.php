<?php

namespace App\Models\Assistant;

class MessageContentTyping extends MessageContent
{
    public function render(): string
    {
        // Render a typing indicator
        return '<div class="typing">
                    <div class="typing__dot"></div>
                    <div class="typing__dot"></div>
                    <div class="typing__dot"></div>
                </div>';
    }

    public function serialize(): string|null
    {
        return null; // Typing content does not need to be serialized
    }

    public function unserialize(string $data): void
    {
        // No data to unserialize for typing content
    }
}