<?php

namespace App\Models\Assistant;

use App\Models\Assistant\AssistantCapability;
use Exception;
use Serializable;

/**
 * Base class for all AI Assistant implementations MetaGer supports
 * used to provide a central equal interface for all of them
 */
abstract class Assistant implements Serializable
{
    /**
     * Capabilities this Assistant can perform
     * @var \App\Models\Assistant\AssistantCapability[]
     */
    protected array $capabilities = [];
    /**
     * Message History with Assistant
     * @var \App\Models\Assistant\Message[]
     */
    protected array $messages = [];

    /**
     * Processes a new user message
     * @param string $message
     * @throws \Exception
     * @return void
     */
    public function process(string $message)
    {
        if (!$this->can(AssistantCapability::CHAT))
            throw new Exception("Agent is missing the CHAT capability");
        $this->messages[] = new Message($message, MessageType::User);
    }

    /**
     * Returns this instances message history
     * @return \App\Models\Assistant\Message[]
     */
    public function getMessages(): array
    {
        return $this->messages;
    }

    /**
     * Checks if this Assistant can perform certain tasks
     * @param \App\Models\Assistant\AssistantCapability $capability
     * @return bool
     */
    public function can(AssistantCapability $capability): bool
    {
        return in_array($capability, $this->capabilities);
    }

    public function serialize(): string|null
    {
        return serialize([
            $this->capabilities,
            $this->messages
        ]);
    }

    public function unserialize(string $data): void
    {
        list($this->capabilities, $this->messages) = unserialize($data);
    }
}
