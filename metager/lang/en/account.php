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
        'charge' => ':charge tokens',
        // Shown instead of the key code when the key cannot be named — a legacy
        // non-UUID key whose canonical form we could not resolve.
        'signed_in' => 'Signed in',
        // The webextension: the key never reaches us, so there is nothing to name.
        'anonymous' => 'signed in anonymously',
        // The mark is decorative and the code is styled shorthand, so the whole
        // answer has to be in the label for anyone not reading the pixels.
        //
        // "ending in", not a bare "key :fingerprint": these are the last six
        // characters of the key (KeyUser::getKeyFingerprint()), and calling them
        // the key invited people to type them into the sign-in form — where,
        // until the guard in the keymanager's POST /key/enter, they resolved to
        // an empty phantom account.
        'aria' => 'My account – key ending in :fingerprint, :charge tokens',
        'aria_nocharge' => 'My account – key ending in :fingerprint',
        'aria_nofingerprint' => 'My account – :charge tokens',
        'aria_anonymous' => 'My account – signed in anonymously through the web extension',
    ],
    'sidebar' => [
        'balance' => ':charge tokens · ad-free',
        // Not "0 tokens · ad-free": at zero the searches are not ad-free,
        // they do not happen at all.
        'balance_empty' => 'No tokens left',
        'manage' => 'Manage account',
        'topup' => 'Top up',
        'logout' => 'Log out',
        'login' => 'Log in',
        'create' => 'Set up',
        'logged_out' => 'Not logged in. With a key you search ad-free and anonymously.',
        'anonymous_hint' => 'Ad-free · managed by the web extension',
        // The webextension holds the key, so the account lives in its popup.
        // The button this labels is hidden until the extension reveals it.
        'extension_settings' => 'Manage in the extension',
    ],
    'empty' => [
        'message' => 'Your tokens are used up.',
        'action' => 'Top up now',
    ],
];
