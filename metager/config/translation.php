<?php

return [
    'key' => env("TRANSLATION_KEY", ""),
    'source_locale' => 'de',
    'target_locales' => ['en', 'es'],

    /* Directories to scan for Gettext strings */
    'gettext_parse_paths' => ['app', 'resources'],

    /* Where the Gettext translations are stored */
    'gettext_locales_path' => 'resources/lang/gettext'
];
