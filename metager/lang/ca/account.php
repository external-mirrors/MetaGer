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
        'charge' => ':charge fitxes',
        // Shown instead of the key code when the key cannot be named — a legacy
        // non-UUID key whose canonical form we could not resolve.
        'signed_in' => 'Sessió iniciada',
        // The webextension: the key never reaches us, so there is nothing to name.
        'anonymous' => 'sessió iniciada anònimament',
        // The mark is decorative and the code is styled shorthand, so the whole
        // answer has to be in the label for anyone not reading the pixels.
        'aria' => 'El meu compte – clau :fingerprint, :charge fitxes',
        'aria_nocharge' => 'El meu compte – clau :fingerprint',
        'aria_nofingerprint' => 'El meu compte – :charge fitxes',
        'aria_anonymous' => "El meu compte – sessió iniciada anònimament amb l'extensió web",
    ],
    'sidebar' => [
        'balance' => ':charge fitxes · sense publicitat',
        // Not "0 fitxes · sense publicitat": at zero the searches are not
        // ad-free, they do not happen at all.
        'balance_empty' => 'No queden fitxes',
        'manage' => 'Gestiona el compte',
        'topup' => 'Recarrega',
        'logout' => 'Tanca la sessió',
        'login' => 'Inicia la sessió',
        'create' => 'Configura',
        'logged_out' => 'No heu iniciat la sessió. Amb una clau cerqueu sense publicitat i de manera anònima.',
        'anonymous_hint' => "Sense publicitat · gestionat per l'extensió web",
        // The webextension holds the key, so the account lives in its popup.
        // The button this labels is hidden until the extension reveals it.
        'extension_settings' => "Gestiona a l'extensió",
    ],
    'empty' => [
        'message' => 'Heu exhaurit les fitxes.',
        'action' => 'Recarrega ara',
    ],
];
