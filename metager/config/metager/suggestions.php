<?php

$suggestions = [];
if (file_exists(config_path("suggestions.json")))
    $suggestions = json_decode(file_get_contents(config_path("suggestions.json")), true);

return [
    "serper" => [
        "api_key" => array_key_exists("serper", $suggestions) && array_key_exists("api_key", $suggestions["serper"]) ? $suggestions["serper"]["api_key"] : null
    ],
    "brave" => [
        "api_key" => array_key_exists("brave", $suggestions) && array_key_exists("api_key", $suggestions["brave"]) ? $suggestions["brave"]["api_key"] : null
    ],
    "bing" => [
        "api_key" => array_key_exists("bing", $suggestions) && array_key_exists("api_key", $suggestions["bing"]) ? $suggestions["bing"]["api_key"] : null
    ],
    "dev" => [
        "api_base" => array_key_exists("dev", $suggestions) && array_key_exists("api_base", $suggestions["dev"]) ? $suggestions["dev"]["api_base"] : "",
        "api_get_parameters" => array_key_exists("dev", $suggestions) && array_key_exists("api_get_parameters", $suggestions["dev"]) ? $suggestions["dev"]["api_get_parameters"] : [],
        "api_lang_parameter_name" => array_key_exists("dev", $suggestions) && array_key_exists("api_lang_parameter_name", $suggestions["dev"]) ? $suggestions["dev"]["api_lang_parameter_name"] : "lang",
    ]
];