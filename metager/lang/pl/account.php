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
        'signed_in' => 'Zalogowano',
        // The webextension: the key never reaches us, so there is nothing to name.
        'anonymous' => 'zalogowano anonimowo',
        // The mark is decorative and the code is styled shorthand, so the whole
        // answer has to be in the label for anyone not reading the pixels.
        'aria' => 'Moje konto – klucz :fingerprint, :charge Token',
        'aria_nocharge' => 'Moje konto – klucz :fingerprint',
        'aria_nofingerprint' => 'Moje konto – :charge Token',
        'aria_anonymous' => 'Moje konto – zalogowano anonimowo przez rozszerzenie przeglądarki',
    ],
    'sidebar' => [
        'balance' => ':charge Token · bez reklam',
        // Not "0 Token · …": at zero the searches are not ad-free, they do
        // not happen at all.
        'balance_empty' => 'Brak Token',
        'manage' => 'Zarządzaj kontem',
        'topup' => 'Doładuj',
        'logout' => 'Wyloguj się',
        'login' => 'Zaloguj się',
        'create' => 'Skonfiguruj',
        'logged_out' => 'Nie zalogowano. Z kluczem szukasz bez reklam i anonimowo.',
        'anonymous_hint' => 'Bez reklam · zarządzane przez rozszerzenie przeglądarki',
        // The webextension holds the key, so the account lives in its popup.
        // The button this labels is hidden until the extension reveals it.
        'extension_settings' => 'Zarządzaj w rozszerzeniu',
    ],
    'empty' => [
        'message' => 'Twoje Token zostały wyczerpane.',
        'action' => 'Doładuj teraz',
    ],
];
