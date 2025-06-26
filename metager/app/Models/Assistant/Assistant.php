<?php

namespace App\Models\Assistant;

use Metager\App\Models\Assistant\AssistantCapability;

/**
 * Base class for all AI Assistant implementations MetaGer supports
 * used to provide a central equal interface for all of them
 */
abstract class Assistant
{
    /**
     * Capabilities this Assistant can perform
     * @var \Metager\App\Models\Assistant\AssistantCapability[]
     */
    protected array $capabilities = [];
    /**
     * Message History with Assistant
     * @var \Metager\App\Models\Assistant\Message[]
     */
    protected array $messages;

    /**
     * Returns this instances message history
     * @return \Metager\App\Models\Assistant\Message[]
     */
    public function getMessages(): array
    {
        return $this->messages;
    }

    /**
     * Checks if this Assistant can perform certain tasks
     * @param \Metager\App\Models\Assistant\AssistantCapability $capability
     * @return bool
     */
    public function can(AssistantCapability $capability): bool
    {
        return in_array($capability, $this->capabilities);
    }
}
