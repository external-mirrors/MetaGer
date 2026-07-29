<?php

return [
    "empty" => "Ask anything to get started.",

    "unavailable" => [
        "title" => "Chat is currently unavailable",
        "body" => "The chat service cannot be reached right now. Please try again in a few moments.",
    ],

    "model" => [
        "label" => "Model",
        "cost_per_message" => "about :cost per message",
    ],

    "composer" => [
        "label" => "Your message",
        "placeholder" => "Type your message…",
        "send" => "Send",
        "stop" => "Stop",
    ],

    // Only ever shown with JavaScript enabled (resources/js/chat/affordances.js).
    "action" => [
        "copy" => "Copy",
        "copy_done" => "Copied",
        "regenerate" => "Regenerate",
        "download" => "Download",
    ],

    "error" => [
        "no_key" => "A MetaGer key is required to use chat.",
        "unavailable" => "Chat is currently unavailable. Please try again in a moment.",
        "generic" => "The message could not be completed.",
    ],
];
