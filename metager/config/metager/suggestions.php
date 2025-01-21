<?php

$suggestions = [];
$suggestions = json_decode(file_get_contents(config_path("suggestions.json")), true);

return [
    "serper" => [
        "api_key" => array_key_exists("serper", $suggestions) && array_key_exists("api_key", $suggestions["serper"]) ? $suggestions["serper"]["api_key"] : null
    ]
];