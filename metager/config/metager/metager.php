<?php

return [
    "browserverification_enabled" => true,
    "browserverification_whitelist" => [
    ],
    "affiliate_preference" => "adgoal",
    "botprotection" => [
        "enabled" => env("BOT_PROTECTION", false),
    ],
    "proxy" => [
        "password" => env("PROXY_PASSWORD", "secure_password"),
    ],
    "keymanager" => [
        "server" => env("KEY_SERVER", null),
        "access_token" => env("KEY_ACCESS_TOKEN")
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
        "germanmail" => env("TICKET_GERMAN_MAIL", ""),
        "englishmail" => env("TICKET_ENGLISH_MAIL", "")
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
    "webdriver" => [
        "user" => env("WEBDRIVER_USER", ""),
        "key" => env("WEBDRIVER_KEY", ""),
    ],
    "paypal" => [
        'pdt_token' => env("PAYPAL_PDT_TOKEN", ""),
        'base_url' => env("APP_ENV") !== "production" ? "https://api-m.sandbox.paypal.com" : "https://api-m.paypal.com",
        'client_id' => env("APP_ENV") !== "production" ? env("PAYPAL_SANDBOX_CLIENT_ID") : env("PAYPAL_CLIENT_ID"),
        'secret' => env("APP_ENV") !== "production" ? env("PAYPAL_SANDBOX_SECRET") : env("PAYPAL_SECRET"),
        'subscription_plans' => [
            'de' => [
                'monthly' => env("APP_ENV") !== "production" ? env("PAYPAL_SANDBOX_SUBSCRIPTION_PLAN_DE_MONTHLY") : env("PAYPAL_SUBSCRIPTION_PLAN_DE_MONTHLY"),
                'quaterly' => env("APP_ENV") !== "production" ? env("PAYPAL_SANDBOX_SUBSCRIPTION_PLAN_DE_QUATERLY") : env("PAYPAL_SUBSCRIPTION_PLAN_DE_QUATERLY"),
                'six-monthly' => env("APP_ENV") !== "production" ? env("PAYPAL_SANDBOX_SUBSCRIPTION_PLAN_DE_SIXMONTHLY") : env("PAYPAL_SUBSCRIPTION_PLAN_DE_SIXMONTHLY"),
                'yearly' => env("APP_ENV") !== "production" ? env("PAYPAL_SANDBOX_SUBSCRIPTION_PLAN_DE_YEARLY") : env("PAYPAL_SUBSCRIPTION_PLAN_DE_YEARLY"),
            ]
        ]
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