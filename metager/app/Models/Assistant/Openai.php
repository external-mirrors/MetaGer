<?php

namespace App\Models\Assistant;

use App\Models\Assistant\Assistant;

class Openai extends Assistant
{
    public function __construct()
    {
        $this->capabilities[] = AssistantCapability::CHAT;
        $this->capabilities[] = AssistantCapability::SEARCH;

    }
}