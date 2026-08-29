<?php
return [
    /**
     * The account, wherever it appears: the pill in the corner, the block at the
     * top of the site menu, and the one alert that interrupts.
     *
     * Its own file rather than more keys under index/sidebar, because the same
     * strings are now rendered from three different views on two different
     * layouts, and none of them is "the index page".
     */
    'pill' => [
        'charge' => ':charge Token',
        // Shown instead of the key code when the key cannot be named — a legacy
        // non-UUID key whose canonical form we could not resolve.
        'signed_in' => 'Inloggad',
        // The webextension: the key never reaches us, so there is nothing to name.
        'anonymous' => 'inloggad anonymt',
        // The mark is decorative and the code is styled shorthand, so the whole
        // answer has to be in the label for anyone not reading the pixels.
        //
        // "ending in", not a bare "key :fingerprint": these are the last six
        // characters of the key (KeyUser::getKeyFingerprint()), and calling them
        // the key invited people to type them into the sign-in form — where,
        // until the guard in the keymanager's POST /key/enter, they resolved to
        // an empty phantom account.
        'aria' => 'Mitt konto – nyckel som slutar på :fingerprint, :charge Token',
        'aria_nocharge' => 'Mitt konto – nyckel som slutar på :fingerprint',
        'aria_nofingerprint' => 'Mitt konto – :charge Token',
        'aria_anonymous' => 'Mitt konto – inloggad anonymt via webbtillägget',
    ],
    'sidebar' => [
        'balance' => ':charge Token · annonsfritt',
        // Not "0 Token · …": at zero the searches are not ad-free, they do
        // not happen at all.
        'balance_empty' => 'Inga Token kvar',
        'manage' => 'Hantera kontot',
        'topup' => 'Fyll på',
        'logout' => 'Logga ut',
        'login' => 'Logga in',
        'create' => 'Kom igång',
        'logged_out' => 'Inte inloggad. Med en nyckel söker du annonsfritt och anonymt.',
        'anonymous_hint' => 'Annonsfritt · hanteras av webbtillägget',
        // The webextension holds the key, so the account lives in its popup.
        // The button this labels is hidden until the extension reveals it.
        'extension_settings' => 'Hantera i tillägget',
    ],
    'empty' => [
        'message' => 'Dina Token är slut.',
        'action' => 'Fyll på nu',
    ],
];
