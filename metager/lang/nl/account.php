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
        'signed_in' => 'Aangemeld',
        // The webextension: the key never reaches us, so there is nothing to name.
        'anonymous' => 'anoniem aangemeld',
        // The mark is decorative and the code is styled shorthand, so the whole
        // answer has to be in the label for anyone not reading the pixels.
        //
        // "ending in", not a bare "key :fingerprint": these are the last six
        // characters of the key (KeyUser::getKeyFingerprint()), and calling them
        // the key invited people to type them into the sign-in form — where,
        // until the guard in the keymanager's POST /key/enter, they resolved to
        // an empty phantom account.
        'aria' => 'Mijn account – sleutel eindigend op :fingerprint, :charge Token',
        'aria_nocharge' => 'Mijn account – sleutel eindigend op :fingerprint',
        'aria_nofingerprint' => 'Mijn account – :charge Token',
        'aria_anonymous' => 'Mijn account – anoniem aangemeld via de webextensie',
    ],
    'sidebar' => [
        'balance' => ':charge Token · advertentievrij',
        // Not "0 Token · …": at zero the searches are not ad-free, they do
        // not happen at all.
        'balance_empty' => 'Geen Token meer',
        'manage' => 'Account beheren',
        'topup' => 'Opwaarderen',
        'logout' => 'Afmelden',
        'login' => 'Aanmelden',
        'create' => 'Instellen',
        'logged_out' => 'Niet aangemeld. Met een sleutel zoek je advertentievrij en anoniem.',
        'anonymous_hint' => 'Advertentievrij · beheerd door de webextensie',
        // The webextension holds the key, so the account lives in its popup.
        // The button this labels is hidden until the extension reveals it.
        'extension_settings' => 'Beheren in de extensie',
    ],
    'empty' => [
        'message' => 'Je Token zijn op.',
        'action' => 'Nu opwaarderen',
    ],
];
