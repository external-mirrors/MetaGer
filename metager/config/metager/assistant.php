<?php

use Dotenv\Dotenv;

if (file_exists(base_path(".env.assistant"))) {
    $dotenv = Dotenv::createImmutable(base_path(), [".env.assistant"]);
    $dotenv->load();
}

return [
    "openai" => [
        "api_key" => env("OPENAI_API_KEY", "")
    ]
];