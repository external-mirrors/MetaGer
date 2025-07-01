<?php

namespace App\Models\Assistant;

use League\CommonMark\Util\HtmlFilter;
use Str;

class MessageContentText extends MessageContent
{
    public readonly string $message;

    public function __construct(string $message)
    {
        $this->message = $message;
    }

    public function append(string $text): void
    {
        // Append text to the existing message
        $this->message .= $text;
    }

    public function render(): string
    {
        return $this->message;
    }

    public function serialize(): string|null
    {
        return serialize([
            $this->message
        ]);
    }

    public function unserialize(string $data): void
    {
        list($this->message) = unserialize($data);
    }
}