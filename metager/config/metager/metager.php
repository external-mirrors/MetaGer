<?php

return [
    "browserverification_enabled" => true,
    "browserverification_whitelist" => [
    ],
    "admin" => [
        "user" => env("ADMIN_USER", "admin"),
        "password" => env("ADMIN_PASSWORD", "admin"),
    ],
    "affiliate_preference" => "adgoal",
    "botprotection" => [
        "enabled" => env("BOT_PROTECTION", false),
    ],
    "proxy" => [
        "password" => env("PROXY_PASSWORD", "secure_password"),
    ],
    "keys" => [
        "uni_mainz" => env("mainz_key"),
        "assoziator" => env("ASSO_KEY"),
        "berlin" => env("berlin"),
    ],
    "tts" => [
        "base_url" => env("TTS_BASE_URL", ""),
    ],
    "fetcher" => [
        "proxy" => [
            "host" => env("PROXY_HOST", ""),
            "port" => env("PROXY_PORT", ""),
            "user" => env("PROXY_USER", ""),
            "password" => env("PROXY_PASSWORD", ""),
        ],
    ],
    "fail2ban" => [
        "enabled" => true,
        "url" => env("fail2banurl", false),
        "user" => env("fail2banuser", false),
        "password" => env("fail2banpassword", false),
    ],
    "ticketsystem" => [
        "url" => env("TICKET_URL", "https://metager.de"),
        "apikey" => env("TICKET_APIKEY", ""),
    ],
    "civicrm" => [
        "url" => env("CIVICRM_URL", "https://metager.de"),
        "apikey" => env("CIVICRM_API_KEY", ""),
        "sitekey" => env("CIVICRM_SITE_KEY", ""),
    ],
    "admitad" => [
        "germany_public_key" => env("admitad_germany_public", ""),
        "international_public_key" => env("admitad_international_public", "")
    ],
    "keyserver" => [
        "user" => env("KEY_USER", "test"),
        "password" => env("KEY_PASSWORD", "test"),
    ],
    "webdriver" => [
        "user" => env("WEBDRIVER_USER", ""),
        "key" => env("WEBDRIVER_KEY", ""),
    ],
    "paypal" => [
        'pdt_token' => env("PAYPAL_PDT_TOKEN", ""),
    ],
    "maps" => [
        "version" => env("maps_version"),
    ],
    "git" => [
        "project_name" => "MetaGer",
        "branch_name" => env("BRANCH_NAME", "Not Set"),
        "commit_name" => env("COMMIT_NAME", "Not Set"),
    ],
    "selenium" => [
        "host" => env("SELENIUM_HOST", "localhost"),
    ],
];