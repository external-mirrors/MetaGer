<?php

namespace App\Models\Assistant;

use Serializable;

class Message implements Serializable
{
    public readonly string $id;
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
    public function __construct(string $id = null, array $contents, MessageRole $role)
    {
        if ($id === null) {
            $id = uniqid("msg_", true);
        }
        $this->id = $id;
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
            $this->id,
            $this->contents,
            $this->role
        ]);
    }

    public function unserialize(string $data): void
    {
        list($this->id, $this->contents, $this->role) = unserialize($data);
    }
}