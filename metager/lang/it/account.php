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
        'signed_in' => 'Connesso',
        // The webextension: the key never reaches us, so there is nothing to name.
        'anonymous' => 'connesso in modo anonimo',
        // The mark is decorative and the code is styled shorthand, so the whole
        // answer has to be in the label for anyone not reading the pixels.
        'aria' => 'Il mio account – chiave :fingerprint, :charge Token',
        'aria_nocharge' => 'Il mio account – chiave :fingerprint',
        'aria_nofingerprint' => 'Il mio account – :charge Token',
        'aria_anonymous' => "Il mio account – connesso in modo anonimo tramite l'estensione web",
    ],
    'sidebar' => [
        'balance' => ':charge Token · senza pubblicità',
        // Not "0 Token · …": at zero the searches are not ad-free, they do
        // not happen at all.
        'balance_empty' => 'Token esauriti',
        'manage' => "Gestisci l'account",
        'topup' => 'Ricarica',
        'logout' => 'Esci',
        'login' => 'Accedi',
        'create' => 'Configura',
        'logged_out' => "Non hai effettuato l'accesso. Con una chiave cerchi senza pubblicità e in modo anonimo.",
        'anonymous_hint' => "Senza pubblicità · gestito dall'estensione web",
        // The webextension holds the key, so the account lives in its popup.
        // The button this labels is hidden until the extension reveals it.
        'extension_settings' => "Gestisci nell'estensione",
    ],
    'empty' => [
        'message' => 'I tuoi Token sono esauriti.',
        'action' => 'Ricarica ora',
    ],
];
