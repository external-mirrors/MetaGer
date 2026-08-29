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
        'signed_in' => 'Connecté',
        // The webextension: the key never reaches us, so there is nothing to name.
        'anonymous' => 'connecté anonymement',
        // The mark is decorative and the code is styled shorthand, so the whole
        // answer has to be in the label for anyone not reading the pixels.
        //
        // "ending in", not a bare "key :fingerprint": these are the last six
        // characters of the key (KeyUser::getKeyFingerprint()), and calling them
        // the key invited people to type them into the sign-in form — where,
        // until the guard in the keymanager's POST /key/enter, they resolved to
        // an empty phantom account.
        'aria' => 'Mon compte – clé se terminant par :fingerprint, :charge Token',
        'aria_nocharge' => 'Mon compte – clé se terminant par :fingerprint',
        'aria_nofingerprint' => 'Mon compte – :charge Token',
        'aria_anonymous' => "Mon compte – connecté anonymement via l'extension web",
    ],
    'sidebar' => [
        'balance' => ':charge Token · sans publicité',
        // Not "0 Token · …": at zero the searches are not ad-free, they do
        // not happen at all.
        'balance_empty' => 'Plus de Token',
        'manage' => 'Gérer le compte',
        'topup' => 'Recharger',
        'logout' => 'Se déconnecter',
        'login' => 'Se connecter',
        'create' => 'Configurer',
        'logged_out' => 'Non connecté. Avec une clé, vous cherchez sans publicité et anonymement.',
        'anonymous_hint' => "Sans publicité · géré par l'extension web",
        // The webextension holds the key, so the account lives in its popup.
        // The button this labels is hidden until the extension reveals it.
        'extension_settings' => "Gérer dans l'extension",
    ],
    'empty' => [
        'message' => 'Vos Token sont épuisés.',
        'action' => 'Recharger maintenant',
    ],
];
