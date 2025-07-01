<?php

namespace App\Models\Assistant;

class MessageContentWebsearch extends MessageContent
{
    protected string $query;

    public function __construct(string $query)
    {
        $this->query = $query;
    }

    public function render(): string
    {
        return $this->query;
    }

    public function serialize(): string|null
    {
        return serialize([
            $this->query
        ]);
    }

    public function unserialize(string $data): void
    {
        $unserialized = unserialize($data);
        if (!is_array($unserialized) || count($unserialized) !== 1) {
            throw new \Exception("Invalid serialized data for MessageContentWebsearch");
        }
        $this->query = $unserialized[0];
    }
}
