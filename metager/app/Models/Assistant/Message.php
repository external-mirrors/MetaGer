<?php

namespace App\Models\Assistant;

use Serializable;

class Message implements Serializable
{
    /**
     * Contains all parts of this message (Text content, image content etc)
     * @var MessageContent[]
     */
    private array $contents = [];

    /**
     * The Role of this message (i.e. Assistant or User)
     * @var MessageRole
     */
    public readonly MessageRole $role;

    /**
     * Constructs a message with a given role
     * @param MessageContent[] $contents
     */
    public function __construct(array $contents, MessageRole $role)
    {
        $this->contents = $contents;
        $this->role = $role;
    }

    public function addContent(MessageContent $content)
    {
        $this->contents[] = $content;
    }

    /**
     * Returns current contents
     * @return MessageContent[]
     */
    public function getContents(): array
    {
        return $this->contents;
    }

    public function render(): string
    {
        return view("assistant.message", ["message" => $this])->render();
    }

    public function serialize(): string|null
    {
        return serialize([
            $this->contents,
            $this->role
        ]);
    }

    public function unserialize(string $data): void
    {
        list($this->contents, $this->role) = unserialize($data);
    }
}