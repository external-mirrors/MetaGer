<?php

namespace App\Models\Assistant;

use Illuminate\Mail\Markdown;
use League\CommonMark\Util\HtmlFilter;
use Serializable;
use Str;

class Message implements Serializable
{
    public readonly string $message;
    public readonly MessageType $type;

    public function __construct(string $message, MessageType $type)
    {
        $this->message = $message;
        $this->type = $type;
    }

    public function render(): string
    {
        switch ($this->type) {
            case MessageType::Agent:
                return Str::of($this->message)->markdown([
                    "html_input" => HtmlFilter::ESCAPE,
                ]);
            case MessageType::User:
                return $this->message;
        }
    }

    public function serialize(): string|null
    {
        return serialize([
            $this->type,
            $this->message
        ]);
    }

    public function unserialize(string $data): void
    {
        list($this->type, $this->message) = unserialize($data);
    }
}