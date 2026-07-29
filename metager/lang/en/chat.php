<?php

return [
    "empty" => "Chat securely and privately with MetaGer.",
    "empty_note" => "Ask something to get started. Your key is needed, and history only saves if you turn it on.",

    "unavailable" => [
        "title" => "Chat is currently unavailable",
        "body" => "The chat service cannot be reached right now. Please try again in a few moments.",
    ],

    "skiplinks" => [
        "conversation" => "Skip to the conversation",
    ],

    "model" => [
        "label" => "Model",
        "cost_per_message" => "about :cost per message",

        "speed" => [
            "fast" => "Fast",
            "balanced" => "Balanced",
            "thorough" => "Thorough",
        ],

        // Plain language, one sentence each. Keyed by the model id from
        // metager-chat/config/models.json; a model with no entry simply renders without a
        // description.
        "descriptions" => [
            "openai/gpt-4o" => "Capable and dependable — a good default if you are not sure.",
            "openai/gpt-4o-mini" => "Answers very quickly and costs the least. Good for short questions.",
            "anthropic/claude-3-5-sonnet" => "Takes longer and gives the most careful answers on long texts.",
            "anthropic/claude-3-5-haiku" => "Quick and inexpensive, with a little more stamina on longer texts.",
        ],
    ],

    "attachment" => [
        "label" => "Attach a file",
        "choose" => "Attach a file…",
        "remove" => "Remove",
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
        "file_too_large" => "That file is too large. Up to :size is allowed.",
        "file_not_text" => "That file could not be read. Please attach a text file.",
        "file_failed" => "The file could not be uploaded. Please try again.",
        "attachment_gone" => "The attached file has expired. Please attach it again.",
    ],
];
