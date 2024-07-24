<?php

return [
    "enabled" => env("MATOMO_ENABLED", false),
    "url" => env("MATOMO_URL", null),
    "site_id" => env("MATOMO_SITE_ID", 0),
    "token_auth" => env("MATOMO_TOKEN_AUTH", "")
];