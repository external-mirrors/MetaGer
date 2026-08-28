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
        'signed_in' => 'Sessão iniciada',
        // The webextension: the key never reaches us, so there is nothing to name.
        'anonymous' => 'sessão iniciada anonimamente',
        // The mark is decorative and the code is styled shorthand, so the whole
        // answer has to be in the label for anyone not reading the pixels.
        'aria' => 'A minha conta – chave :fingerprint, :charge Token',
        'aria_nocharge' => 'A minha conta – chave :fingerprint',
        'aria_nofingerprint' => 'A minha conta – :charge Token',
        'aria_anonymous' => 'A minha conta – sessão iniciada anonimamente através da extensão web',
    ],
    'sidebar' => [
        'balance' => ':charge Token · sem publicidade',
        // Not "0 Token · …": at zero the searches are not ad-free, they do
        // not happen at all.
        'balance_empty' => 'Sem Token',
        'manage' => 'Gerir a conta',
        'topup' => 'Carregar',
        'logout' => 'Terminar sessão',
        'login' => 'Iniciar sessão',
        'create' => 'Configurar',
        'logged_out' => 'Sessão não iniciada. Com uma chave pesquisa sem publicidade e de forma anónima.',
        'anonymous_hint' => 'Sem publicidade · gerido pela extensão web',
        // The webextension holds the key, so the account lives in its popup.
        // The button this labels is hidden until the extension reveals it.
        'extension_settings' => 'Gerir na extensão',
    ],
    'empty' => [
        'message' => 'Os seus Token acabaram.',
        'action' => 'Carregar agora',
    ],
];
