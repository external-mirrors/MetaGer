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
        'signed_in' => 'Logget ind',
        // The webextension: the key never reaches us, so there is nothing to name.
        'anonymous' => 'logget ind anonymt',
        // The mark is decorative and the code is styled shorthand, so the whole
        // answer has to be in the label for anyone not reading the pixels.
        //
        // "ending in", not a bare "key :fingerprint": these are the last six
        // characters of the key (KeyUser::getKeyFingerprint()), and calling them
        // the key invited people to type them into the sign-in form — where,
        // until the guard in the keymanager's POST /key/enter, they resolved to
        // an empty phantom account.
        'aria' => 'Min konto – nøgle der ender på :fingerprint, :charge Token',
        'aria_nocharge' => 'Min konto – nøgle der ender på :fingerprint',
        'aria_nofingerprint' => 'Min konto – :charge Token',
        'aria_anonymous' => 'Min konto – logget ind anonymt via webudvidelsen',
    ],
    'sidebar' => [
        'balance' => ':charge Token · uden reklamer',
        // Not "0 Token · …": at zero the searches are not ad-free, they do
        // not happen at all.
        'balance_empty' => 'Ingen Token tilbage',
        'manage' => 'Administrer konto',
        'topup' => 'Fyld op',
        'logout' => 'Log ud',
        'login' => 'Log ind',
        'create' => 'Opret',
        'logged_out' => 'Ikke logget ind. Med en nøgle søger du uden reklamer og anonymt.',
        'anonymous_hint' => 'Uden reklamer · administreret af webudvidelsen',
        // The webextension holds the key, so the account lives in its popup.
        // The button this labels is hidden until the extension reveals it.
        'extension_settings' => 'Administrer i udvidelsen',
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
        'heading' => 'Min konto',

        // Not "your key: 123456". These are the last six characters, and calling
        // them the key led people to type them into the sign-in form.
        'fingerprint' => 'Nøgle der ender på :fingerprint',
        'fingerprint_unknown' => 'Logget ind',

        'balance' => [
            'unit' => 'tokens',
            'one_token' => 'Et token er én søgning.',
            'valid_until' => 'Saldo gyldig til :date',
            'empty' => 'Ingen saldo tilbage. Uden tokens kan du ikke søge — fyld op for at fortsætte.',
            'low' => 'Saldoen er ved at slippe op.',
            'unknown' => 'Vi kan ikke aflæse din saldo lige nu. Det skyldes os og ikke dig — prøv igen om et par minutter.',
            'orders_summary' => 'Fra :count opfyldninger, som udløber én efter én',
            'orders_heading' => 'Udløbsdatoer',
            'order' => ':amount tokens indtil :date',
        ],

        'actions' => [
            'topup' => 'Fyld saldo op',
            'search' => 'Til søgningen',
        ],

        'charge' => [
            'heading' => 'Fyld saldo op',
            'lede' => 'Et token er én søgning og koster én cent. Alle priser er inklusive moms.',
            'tokens' => ':amount tokens',
            'price' => ':price €',
            'more' => 'Alle priser og betalingsmåder',

            /**
             * Why no package is on offer right now. Three sentences for three
             * states, all three of which the old page had too — except that it
             * said "your key is already fully charged", which is not true: what
             * is full is not the balance but the number of open top-ups.
             */
            'blocked' => [
                'proxy' => 'Du surfer gennem en af vores proxy-sessioner. Mens du gør det, er opfyldning slået fra af hensyn til din sikkerhed — en betaling fører til en betalingsudbyder, og den skal ikke se denne session. Åbn denne side uden proxy-session for at fylde op.',
                'full' => 'Denne nøgle bærer allerede tre opfyldninger. Så snart den ældste er brugt op eller udløbet, kan du fylde op igen.',
                'member' => 'Du er medlem af SUMA-EV og søger uden yderligere omkostninger. Du har ikke brug for en token-pakke.',
            ],
        ],

        /**
         * The section the old page did not have: QR code, settings URL and the
         * transfer button sat there in one row, with not a sentence about what
         * they are for.
         */
        'save' => [
            'heading' => 'Sikr din adgang',
            'text' => 'Så længe denne browser beholder cookien, forbliver du logget ind. Mister den den — ny enhed, ryddede browserdata — er din nøgle den eneste vej tilbage til din saldo. Her er den, og her er tre måder at tage den med på.',

            /**
             * The key itself.
             *
             * It has to be here — the sign-in form asks for it first of all —
             * and it is collapsed, because this page gets photographed for
             * support tickets. The old page showed it large and always.
             */
            'key' => [
                'summary' => 'Vis og kopiér nøgle',
                'label' => 'Din nøgle',
                'action' => 'Kopiér nøgle',
                'hint' => '36 tegn. Det er dem, du logger ind med på enhver anden enhed. Foldet sammen, fordi der ofte tages billeder af denne side — den, der ser din nøgle, søger for din regning.',
            ],

            'qr' => [
                'label' => 'QR-kode',
                'alt' => 'QR-kode der fører til din nøgle',
                'action' => 'Gem som billede',
                'hint' => 'Billedet, som login-formularen spørger efter. Du kan uploade det der eller fotografere det med kameraet.',
            ],

            'url' => [
                'label' => 'Bogmærke',
                'action' => 'Kopiér URL',
                'hint' => 'Et kald af denne URL genskaber nøglen sammen med denne browsers søgeindstillinger.',
            ],

            /**
             * The transfer dialog. The keymanager called it "generate login
             * code" — a label naming the means rather than the end, which is why
             * it never answered "how do I get MetaGer onto my phone?", even
             * though that is exactly what the button does.
             */
            'transfer' => [
                'label' => 'Endnu en enhed',
                'action' => 'Log en enhed ind',
                'hint' => 'Viser en kort kode, som du taster ind i login-formularen på den anden enhed — i stedet for at skrive hele nøglen af.',

                'title' => 'Log endnu en enhed ind',
                'description' => 'Indtast denne kode på den anden enhed i login-formularen, der hvor nøglen ellers står.',
                'waiting' => 'Henter kode …',
                'note' => 'Koden gælder for ét enkelt login og kun så længe den står her. Luk dette vindue, så snart du har indtastet den.',
                'failed' => 'Koden kunne ikke hentes. Luk vinduet og prøv igen om lidt.',
                'close' => 'Luk',
            ],
        ],

        /**
         * What still lives in the keymanager. A list at the foot rather than the
         * three equal tabs of before: hardly anybody has campaigns, and a third
         * tab claimed otherwise.
         */
        'more' => [
            'heading' => 'Mere',
            'orders' => 'Bestillinger og fakturaer',
            'campaigns' => 'Gavekortkampagner',
            'help' => 'Hjælp til nøglen',
            'logout' => 'Log ud',
            // Signing out only clears the cookie. Someone who does not know
            // that will not click it — and someone who reads it as "delete
            // account" certainly will not.
            'logout_hint' => 'Fjerner nøglen fra denne browser. Saldoen bliver på nøglen.',
        ],
    ],

    'empty' => [
        'message' => 'Dine Token er brugt op.',
        'action' => 'Fyld op nu',
    ],
];
