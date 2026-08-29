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
        'charge' => ':charge fichas',
        // Shown instead of the key code when the key cannot be named — a legacy
        // non-UUID key whose canonical form we could not resolve.
        'signed_in' => 'Sesión iniciada',
        // The webextension: the key never reaches us, so there is nothing to name.
        'anonymous' => 'sesión iniciada de forma anónima',
        // The mark is decorative and the code is styled shorthand, so the whole
        // answer has to be in the label for anyone not reading the pixels.
        //
        // "ending in", not a bare "key :fingerprint": these are the last six
        // characters of the key (KeyUser::getKeyFingerprint()), and calling them
        // the key invited people to type them into the sign-in form — where,
        // until the guard in the keymanager's POST /key/enter, they resolved to
        // an empty phantom account.
        'aria' => 'Mi cuenta – clave terminada en :fingerprint, :charge fichas',
        'aria_nocharge' => 'Mi cuenta – clave terminada en :fingerprint',
        'aria_nofingerprint' => 'Mi cuenta – :charge fichas',
        'aria_anonymous' => 'Mi cuenta – sesión iniciada de forma anónima con la extensión web',
    ],
    'sidebar' => [
        'balance' => ':charge fichas · sin publicidad',
        // Not "0 fichas · …": at zero the searches are not ad-free, they do
        // not happen at all.
        'balance_empty' => 'No quedan fichas',
        'manage' => 'Gestionar la cuenta',
        'topup' => 'Recargar',
        'logout' => 'Cerrar sesión',
        'login' => 'Iniciar sesión',
        'create' => 'Configurar',
        'logged_out' => 'No ha iniciado sesión. Con una clave busca sin publicidad y de forma anónima.',
        'anonymous_hint' => 'Sin publicidad · gestionado por la extensión web',
        // The webextension holds the key, so the account lives in its popup.
        // The button this labels is hidden until the extension reveals it.
        'extension_settings' => 'Gestionar en la extensión',
    ],
    'empty' => [
        'message' => 'Ha agotado sus fichas.',
        'action' => 'Recargar ahora',
    ],
];
