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
        'signed_in' => 'Kirjautunut sisään',
        // The webextension: the key never reaches us, so there is nothing to name.
        'anonymous' => 'kirjautunut sisään nimettömästi',
        // The mark is decorative and the code is styled shorthand, so the whole
        // answer has to be in the label for anyone not reading the pixels.
        'aria' => 'Oma tili – avain :fingerprint, :charge Token',
        'aria_nocharge' => 'Oma tili – avain :fingerprint',
        'aria_nofingerprint' => 'Oma tili – :charge Token',
        'aria_anonymous' => 'Oma tili – kirjautunut sisään nimettömästi selainlaajennuksen kautta',
    ],
    'sidebar' => [
        'balance' => ':charge Token · mainokseton',
        // Not "0 Token · …": at zero the searches are not ad-free, they do
        // not happen at all.
        'balance_empty' => 'Token loppu',
        'manage' => 'Hallitse tiliä',
        'topup' => 'Lataa',
        'logout' => 'Kirjaudu ulos',
        'login' => 'Kirjaudu sisään',
        'create' => 'Ota käyttöön',
        'logged_out' => 'Et ole kirjautunut sisään. Avaimella haet ilman mainoksia ja nimettömästi.',
        'anonymous_hint' => 'Mainokseton · selainlaajennuksen hallinnassa',
        // The webextension holds the key, so the account lives in its popup.
        // The button this labels is hidden until the extension reveals it.
        'extension_settings' => 'Hallitse laajennuksessa',
    ],
    'empty' => [
        'message' => 'Token ovat loppuneet.',
        'action' => 'Lataa nyt',
    ],
];
