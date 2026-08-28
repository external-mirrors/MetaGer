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
        'signed_in' => 'Logget ind',
        // The webextension: the key never reaches us, so there is nothing to name.
        'anonymous' => 'logget ind anonymt',
        // The mark is decorative and the code is styled shorthand, so the whole
        // answer has to be in the label for anyone not reading the pixels.
        'aria' => 'Min konto – nøgle :fingerprint, :charge Token',
        'aria_nocharge' => 'Min konto – nøgle :fingerprint',
        'aria_nofingerprint' => 'Min konto – :charge Token',
        'aria_anonymous' => 'Min konto – logget ind anonymt via webudvidelsen',
    ],
    'sidebar' => [
        'balance' => ':charge Token · uden reklamer',
        // Not "0 Token · …": at zero the searches are not ad-free, they do
        // not happen at all.
        'balance_empty' => 'Ingen Token tilbage',
        'manage' => 'Administrer konto',
        'topup' => 'Fyld op',
        'logout' => 'Log ud',
        'login' => 'Log ind',
        'create' => 'Opret',
        'logged_out' => 'Ikke logget ind. Med en nøgle søger du uden reklamer og anonymt.',
        'anonymous_hint' => 'Uden reklamer · administreret af webudvidelsen',
        // The webextension holds the key, so the account lives in its popup.
        // The button this labels is hidden until the extension reveals it.
        'extension_settings' => 'Administrer i udvidelsen',
    ],
    'empty' => [
        'message' => 'Dine Token er brugt op.',
        'action' => 'Fyld op nu',
    ],
];
