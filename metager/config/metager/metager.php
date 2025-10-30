<?php

return [
    "affiliate_preference" => "adgoal",
    "botprotection" => [
        "enabled" => env("BOT_PROTECTION", false),
        "whitelist" => explode(",", env("BOT_PROTECTION_WHITELIST", "")),
    ],
    "event_authorization" => env("EVENT_AUTHORIZATION", "EVENT_AUTHORIZATION"),
    "proxy" => [
        "password" => env("PROXY_PASSWORD", "secure_password"),
    ],
    "keymanager" => [
        "server" => env("KEY_SERVER", config("app.url") . "/keys"),
        "access_token" => env("KEY_ACCESS_TOKEN", "no-auth")
    ],
    "keys" => [
        "uni_mainz" => explode(separator: ",", string: env("mainz_keys")),
        "assoziator" => env("ASSO_KEY"),
    ],
    "tts" => [
        "base_url" => env("TTS_BASE_URL", ""),
    ],
    "fetcher" => [
        "proxy" => [
            "host" => env("FETCHER_PROXY_HOST", ""),
            "port" => env("FETCHER_PROXY_PORT", ""),
            "user" => env("FETCHER_PROXY_USER", ""),
            "password" => env("FETCHER_PROXY_PASSWORD", ""),
        ],
    ],
    "ticketsystem" => [
        "url" => env("TICKET_URL", "https://metager.de"),
        "apikey" => env("TICKET_APIKEY", ""),
        "germanmail" => env("TICKET_GERMAN_MAIL", ""),
        "englishmail" => env("TICKET_ENGLISH_MAIL", ""),
        "donation_ticket_id" => env("TICKET_DONATION_ID", "")
    ],
    "civicrm" => [
        "enabled" => env("CIVICRM_NOTIFICATIONS_ENABLED", true),
        "url" => env("CIVICRM_URL", "https://metager.de"),
        "apikey" => env("CIVICRM_API_KEY", ""),
        "sitekey" => env("CIVICRM_SITE_KEY", ""),
    ],
    "webdriver" => [
        "user" => env("WEBDRIVER_USER", ""),
        "key" => env("WEBDRIVER_KEY", ""),
    ],
    "membership" => [
        "membership_id_testing" => env("MEMBERSHIP_TESTING_ID", ""),
        "notification_address" => env("MEMBERSHIP_NOTIFICATION_EMAIL", "")
    ],
    "paypal" => [
        'pdt_token' => env("PAYPAL_PDT_TOKEN", ""),
        'base_url' => env("APP_ENV") === "local" ? "https://api-m.sandbox.paypal.com" : "https://api-m.paypal.com",
        'client_id' => env("APP_ENV") === "local" ? env("PAYPAL_SANDBOX_CLIENT_ID") : env("PAYPAL_CLIENT_ID"),
        'secret' => env("APP_ENV") === "local" ? env("PAYPAL_SANDBOX_SECRET") : env("PAYPAL_SECRET"),
        'membership' => [
            'client_id' => env("APP_ENV") === "local" ? env("PAYPAL_MEMBERSHIP_SANDBOX_CLIENT_ID") : env("PAYPAL_MEMBERSHIP_CLIENT_ID"),
            'secret' => env("APP_ENV") === "local" ? env("PAYPAL_MEMBERSHIP_SANDBOX_SECRET") : env("PAYPAL_MEMBERSHIP_SECRET"),
            "webhook_id" => env("APP_ENV") === "local" ? env("PAYPAL_MEMBERSHIP_SANDBOX_WEBHOOKID") : env("PAYPAL_MEMBERSHIP_WEBHOOKID"),
        ],
        'subscription_plans' => [
            'de' => [
                'monthly' => env("APP_ENV") === "local" ? "P-9K924561XA8341447MR4OWLY" : "P-5T792079318830948MR7RKNQ",
                'quarterly' => env("APP_ENV") === "local" ? "P-0FB89268F4436550RMR44A4Q" : "P-62V62940SW941451CMSADXYY",
                'six-monthly' => env("APP_ENV") === "local" ? "P-6HV135640N2728211MR44BEA" : "P-02012023JU117583VMSADYCA",
                'annual' => env("APP_ENV") === "local" ? "P-9PS701646J694893UMR44BKY" : "P-76933683ME4978749MSADYKA",
            ],
            'en' => [
                'monthly' => env("APP_ENV") === "local" ? "P-9K924561XA8341447MR4OWLY" : "P-4KU89480TX608823SMR7RL5Q",
                'quarterly' => env("APP_ENV") === "local" ? "P-0FB89268F4436550RMR44A4Q" : "P-84X85829590029937MSADY4I",
                'six-monthly' => env("APP_ENV") === "local" ? "P-6HV135640N2728211MR44BEA" : "P-4BN89043UV429704AMSADZDY",
                'annual' => env("APP_ENV") === "local" ? "P-9PS701646J694893UMR44BKY" : "P-5BK5465420316543SMSADZKY",
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