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
     * Appends a new text content to the message at the specified index.
     * This method is used to add additional text content to an existing message.
     *
     * @param int $index The index at which to append the text content (not used in this implementation).
     * @param string $text The text content to append.
     */
    public function appendTextContent(int $index, string $text): void
    {
        // Append a new text content to the message
        if ($this->contents[$index] instanceof MessageContentText) {
            // If the last content is already text, append to it
            $this->contents[$index] = new MessageContentText(
                $this->contents[$index]->message . $text
            );
            return;
        }
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

    /**
     * Creates a unique ID for a message.
     * @param string|null $seed An optional seed for the ID.
     * @return string The generated message ID.
     */
    public static function CREATE_ID(string $seed = null): string
    {
        if ($seed === null) {
            $seed = uniqid("msg_", true);
        }
        // Use the application key to create a unique hash for the message ID
        return hash_hmac("sha256", $seed, config("app.key"));
    }
}