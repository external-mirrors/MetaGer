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
        //
        // "ending in", not a bare "key :fingerprint": these are the last six
        // characters of the key (KeyUser::getKeyFingerprint()), and calling them
        // the key invited people to type them into the sign-in form — where,
        // until the guard in the keymanager's POST /key/enter, they resolved to
        // an empty phantom account.
        'aria' => 'El meu compte – clau acabada en :fingerprint, :charge fitxes',
        'aria_nocharge' => 'El meu compte – clau acabada en :fingerprint',
        'aria_nofingerprint' => 'El meu compte – :charge fitxes',
        'aria_anonymous' => "El meu compte – sessió iniciada anònimament amb l'extensió web",
    ],
    'sidebar' => [
        'balance' => ':charge fitxes · sense publicitat',
        // Not "0 fitxes · sense publicitat": at zero the searches are not
        // ad-free, they do not happen at all.
        'balance_empty' => 'No queden fitxes',
        'manage' => 'Gestiona',
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
    /**
     * The account page itself — /konto, moved here from /keys/key/<uuid>.
     *
     * Taken from the keymanager's pass/lang/<locale>/key.json, but mostly new.
     * The old page was almost nothing but button labels; what it never said is
     * what any of them are *for* — which is exactly what support gets asked.
     *
     * Not carried over: `key.share.*`. The share button handed the settings URL,
     * key included, to `navigator.share` and therefore to the operating system's
     * share sheet. Passing an account on is not something a button should
     * advertise; whoever wants to can copy the URL. The copy button stayed.
     */
    'page' => [
        'heading' => 'El meu compte',

        // Not "your key: 123456". These are the last six characters, and calling
        // them the key led people to type them into the sign-in form.
        'fingerprint' => 'Clau acabada en :fingerprint',
        'fingerprint_unknown' => 'Sessió iniciada',

        'balance' => [
            'unit' => 'tokens',
            'one_token' => 'Un token és una cerca.',
            'valid_until' => 'Saldo vàlid fins al :date',
            'empty' => 'No queda saldo. Sense tokens no podeu cercar: recarregueu per continuar.',
            'low' => 'El saldo s\'està acabant.',
            'unknown' => 'Ara mateix no podem consultar el vostre saldo. És cosa nostra, no vostra: torneu-ho a provar d\'aquí a uns minuts.',
            'orders_summary' => 'De :count recàrregues, que caduquen una darrere l\'altra',
            'orders_heading' => 'Dates de caducitat',
            'order' => ':amount tokens fins al :date',
        ],

        'actions' => [
            'topup' => 'Recarregar saldo',
            'search' => 'Anar a la cerca',
        ],

        'charge' => [
            'heading' => 'Recarregar saldo',
            'lede' => 'Un token és una cerca i costa un cèntim. Tots els preus inclouen l\'IVA.',
            'tokens' => ':amount tokens',
            'price' => ':price €',
            'more' => 'Tots els preus i mètodes de pagament',

            /**
             * Rendered on the German interface only
             * ({@see \App\Support\MembershipOffer}) — the SUMA-EV
             * application form exists in no other language. Translated
             * all the same, so the catalogues stay in step.
             */
            'membership' => [
                'heading' => 'O potser fer-se soci?',
                'text' => 'Els socis de la nostra associació sense ànim de lucre <a href="https://suma-ev.de" target="_blank" rel="noopener">SUMA-EV</a> cerquen sense cap cost addicional: la clau es recarrega cada mes amb la quota de soci, i vostè sosté el cercador en lloc de pagar-lo.',
                'action' => 'Fer-se soci',
            ],

            /**
             * Why no package is on offer right now. Three sentences for three
             * states, all three of which the old page had too — except that it
             * said "your key is already fully charged", which is not true: what
             * is full is not the balance but the number of open top-ups.
             */
            'blocked' => [
                'proxy' => 'Esteu navegant a través d\'una de les nostres sessions de proxy. Mentre ho feu, la recàrrega està desactivada per la vostra seguretat: un pagament porta a un proveïdor de pagaments, i no ha de veure aquesta sessió. Obriu aquesta pàgina sense sessió de proxy per recarregar.',
                'full' => 'Aquesta clau ja té tres recàrregues. Tan bon punt la més antiga s\'hagi esgotat o hagi caducat, podreu tornar a recarregar.',
                'member' => 'Sou membre de SUMA-EV i cerqueu sense cap cost addicional. No us cal cap paquet de tokens.',
            ],
        ],

        /**
         * The section the old page did not have: QR code, settings URL and the
         * transfer button sat there in one row, with not a sentence about what
         * they are for.
         */
        'save' => [
            'heading' => 'Assegureu el vostre accés',
            'text' => 'Mentre aquest navegador conservi la galeta, la sessió seguirà iniciada. Si la perd — un dispositiu nou, dades de navegació esborrades —, la vostra clau és l\'únic camí de tornada al vostre saldo. Aquí la teniu, i aquí teniu les maneres d\'endur-vos-la.',

            /**
             * The key itself.
             *
             * It has to be here — the sign-in form asks for it first of all.
             * No longer collapsed: the QR code below it carries the same key
             * and is never collapsed, so hiding it here bought nothing.
             */
            'key' => [
                'label' => 'La vostra clau',
                'action' => 'Copiar la clau',
                'hint' => '36 caràcters. És el que us permet iniciar la sessió en qualsevol altre dispositiu.',
            ],

            'qr' => [
                'label' => 'Codi QR',
                'alt' => 'Codi QR que porta a la vostra clau',
                'action' => 'Desar com a imatge',
                'hint' => 'La imatge que demana el formulari d\'inici de sessió. Hi podeu pujar-la o fotografiar-la amb la càmera.',
            ],

            'url' => [
                'label' => 'Adreça d\'interès',
                'action' => 'Copiar l\'URL',
                'hint' => 'Obrir aquest URL restableix la clau juntament amb la configuració de cerca d\'aquest navegador.',
            ],

            /**
             * The transfer dialog. The keymanager called it "generate login
             * code" — a label naming the means rather than the end, which is why
             * it never answered "how do I get MetaGer onto my phone?", even
             * though that is exactly what the button does.
             */
            'transfer' => [
                'label' => 'Un altre dispositiu',
                'action' => 'Iniciar sessió en un dispositiu',
                'hint' => 'Mostra un codi curt que escriviu al formulari d\'inici de sessió de l\'altre dispositiu, en lloc de copiar tota la clau.',

                'title' => 'Iniciar sessió en un altre dispositiu',
                'description' => 'Introduïu aquest codi a l\'altre dispositiu al formulari d\'inici de sessió, allà on normalment va la clau.',
                'waiting' => 'Obtenint el codi …',
                'note' => 'El codi val per a un únic inici de sessió i només mentre es mostri aquí. Tanqueu aquesta finestra quan l\'hagueu introduït.',
                'failed' => 'No s\'ha pogut obtenir el codi. Tanqueu la finestra i torneu-ho a provar de seguida.',
                'close' => 'Tancar',
            ],
        ],

        /**
         * What still lives in the keymanager. A list at the foot rather than the
         * three equal tabs of before: hardly anybody has campaigns, and a third
         * tab claimed otherwise.
         */
        'more' => [
            'heading' => 'Més',
            'orders' => 'Comandes i factures',
            'campaigns' => 'Campanyes de val',
            'help' => 'Ajuda sobre la clau',
            'logout' => 'Tancar la sessió',
            // Signing out only clears the cookie. Someone who does not know
            // that will not click it — and someone who reads it as "delete
            // account" certainly will not.
            'logout_hint' => 'Elimina la clau d\'aquest navegador. El saldo es queda a la clau.',
        ],
    ],

    'empty' => [
        'message' => 'Heu exhaurit les fitxes.',
        'action' => 'Recarrega ara',
        'message_anonymous' => 'Heu exhaurit les fitxes anònimes.',
    ],
];
