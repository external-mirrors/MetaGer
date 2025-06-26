<?php

namespace Metager\App\Models\Assistant;

enum AssistantCapability
{
    /**
     * Provides basic AI Chat capabilities
     */
    case CHAT;

    /**
     * Can include current search results into message processing
     */
    case SEARCH;
}