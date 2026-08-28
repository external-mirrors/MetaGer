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
        'signed_in' => 'Angemeldet',
        // The webextension: the key never reaches us, so there is nothing to name.
        'anonymous' => 'anonym angemeldet',
        // The mark is decorative and the code is styled shorthand, so the whole
        // answer has to be in the label for anyone not reading the pixels.
        'aria' => 'Mein Konto – Schlüssel :fingerprint, :charge Token',
        'aria_nocharge' => 'Mein Konto – Schlüssel :fingerprint',
        'aria_nofingerprint' => 'Mein Konto – :charge Token',
        'aria_anonymous' => 'Mein Konto – anonym über die Web-Erweiterung angemeldet',
    ],
    'sidebar' => [
        'balance' => ':charge Token · werbefrei',
        // Not "0 Token · werbefrei": at zero the searches are not ad-free,
        // they do not happen at all.
        'balance_empty' => 'Keine Token mehr',
        'manage' => 'Konto verwalten',
        'topup' => 'Aufladen',
        'logout' => 'Abmelden',
        'login' => 'Anmelden',
        'create' => 'Einrichten',
        'logged_out' => 'Nicht angemeldet. Mit einem Schlüssel suchen Sie werbefrei und anonym.',
        'anonymous_hint' => 'Werbefrei · verwaltet von der Web-Erweiterung',
    ],
    'empty' => [
        'message' => 'Ihre Token sind aufgebraucht.',
        'action' => 'Jetzt aufladen',
    ],
];
