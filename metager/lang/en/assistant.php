<?php

return [
    'chat' => [
        'empty_chat' => [
            'description' => 'I am your MetaGer assistant and will be happy to help you with your research. On request, I can include current search results in my answers.',
            'help' => 'How can I help you?',
        ]
    ],
    'prompt' => [
        'placeholder' => 'How is the weather in Hanover?',
        'placeholder_followup' => 'Follow up on this topic...',
        'include_search' => [
            'label' => 'Include search results'
        ]
    ],
    'response' => [
        'error' => 'Sorry, I had trouble loading a response from the selected provider!',
        "content" => [
            "web_search" => [
                "label" => "Includes Web search results for:",
                "loading" => "Loading search results...",
            ]
        ]
    ]
];