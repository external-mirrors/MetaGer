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

    public function process(string $message)
    {
        parent::process($message);
        $this->messages[] = new Message("# Hier die Antwort\n\nAllerdings ist das nur ein **Test**!", MessageType::Agent);
    }
}