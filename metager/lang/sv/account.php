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
        'signed_in' => 'Inloggad',
        // The webextension: the key never reaches us, so there is nothing to name.
        'anonymous' => 'inloggad anonymt',
        // The mark is decorative and the code is styled shorthand, so the whole
        // answer has to be in the label for anyone not reading the pixels.
        //
        // "ending in", not a bare "key :fingerprint": these are the last six
        // characters of the key (KeyUser::getKeyFingerprint()), and calling them
        // the key invited people to type them into the sign-in form — where,
        // until the guard in the keymanager's POST /key/enter, they resolved to
        // an empty phantom account.
        'aria' => 'Mitt konto – nyckel som slutar på :fingerprint, :charge Token',
        'aria_nocharge' => 'Mitt konto – nyckel som slutar på :fingerprint',
        'aria_nofingerprint' => 'Mitt konto – :charge Token',
        'aria_anonymous' => 'Mitt konto – inloggad anonymt via webbtillägget',
    ],
    'sidebar' => [
        'balance' => ':charge Token · annonsfritt',
        // Not "0 Token · …": at zero the searches are not ad-free, they do
        // not happen at all.
        'balance_empty' => 'Inga Token kvar',
        'manage' => 'Hantera kontot',
        'topup' => 'Fyll på',
        'logout' => 'Logga ut',
        'login' => 'Logga in',
        'create' => 'Kom igång',
        'logged_out' => 'Inte inloggad. Med en nyckel söker du annonsfritt och anonymt.',
        'anonymous_hint' => 'Annonsfritt · hanteras av webbtillägget',
        // The webextension holds the key, so the account lives in its popup.
        // The button this labels is hidden until the extension reveals it.
        'extension_settings' => 'Hantera i tillägget',
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
        'heading' => 'Mitt konto',

        // Not "your key: 123456". These are the last six characters, and calling
        // them the key led people to type them into the sign-in form.
        'fingerprint' => 'Nyckel som slutar på :fingerprint',
        'fingerprint_unknown' => 'Inloggad',

        'balance' => [
            'unit' => 'tokens',
            'one_token' => 'En token är en sökning.',
            'valid_until' => 'Saldo giltigt till :date',
            'empty' => 'Inget saldo kvar. Utan tokens kan du inte söka — fyll på för att fortsätta.',
            'low' => 'Saldot håller på att ta slut.',
            'unknown' => 'Vi kan inte läsa av ditt saldo just nu. Det beror på oss och inte på dig — försök igen om några minuter.',
            'orders_summary' => 'Från :count påfyllningar, som löper ut en efter en',
            'orders_heading' => 'Utgångsdatum',
            'order' => ':amount tokens till :date',
        ],

        'actions' => [
            'topup' => 'Fyll på saldo',
            'search' => 'Till sökningen',
        ],

        'charge' => [
            'heading' => 'Fyll på saldo',
            'lede' => 'En token är en sökning och kostar en cent. Alla priser är inklusive moms.',
            'tokens' => ':amount tokens',
            'price' => ':price €',
            'more' => 'Alla priser och betalsätt',

            /**
             * Why no package is on offer right now. Three sentences for three
             * states, all three of which the old page had too — except that it
             * said "your key is already fully charged", which is not true: what
             * is full is not the balance but the number of open top-ups.
             */
            'blocked' => [
                'proxy' => 'Du surfar just nu via en av våra proxysessioner. Under tiden är påfyllning avstängd för din säkerhet — en betalning leder till en betaltjänstleverantör, och den ska inte se den här sessionen. Öppna den här sidan utan proxysession för att fylla på.',
                'full' => 'Den här nyckeln har redan tre påfyllningar. Så snart den äldsta är förbrukad eller har löpt ut kan du fylla på igen.',
                'member' => 'Du är medlem i SUMA-EV och söker utan ytterligare kostnad. Du behöver inget tokenpaket.',
            ],
        ],

        /**
         * The section the old page did not have: QR code, settings URL and the
         * transfer button sat there in one row, with not a sentence about what
         * they are for.
         */
        'save' => [
            'heading' => 'Säkra din åtkomst',
            'text' => 'Så länge den här webbläsaren behåller kakan förblir du inloggad. Om den förlorar den — en ny enhet, rensade webbläsardata — är din nyckel den enda vägen tillbaka till ditt saldo. Här är den, och här är tre sätt att ta den med dig.',

            /**
             * The key itself.
             *
             * It has to be here — the sign-in form asks for it first of all —
             * and it is collapsed, because this page gets photographed for
             * support tickets. The old page showed it large and always.
             */
            'key' => [
                'summary' => 'Visa och kopiera nyckel',
                'label' => 'Din nyckel',
                'action' => 'Kopiera nyckel',
                'hint' => '36 tecken. Med dem loggar du in på vilken annan enhet som helst. Hopfälld eftersom den här sidan ofta fotograferas — den som ser din nyckel söker på din bekostnad.',
            ],

            'qr' => [
                'label' => 'QR-kod',
                'alt' => 'QR-kod som leder till din nyckel',
                'action' => 'Spara som bild',
                'hint' => 'Bilden som inloggningsformuläret frågar efter. Du kan ladda upp den där eller fotografera den med kameran.',
            ],

            'url' => [
                'label' => 'Bokmärke',
                'action' => 'Kopiera URL',
                'hint' => 'Att öppna den här URL:en återställer nyckeln tillsammans med den här webbläsarens sökinställningar.',
            ],

            /**
             * The transfer dialog. The keymanager called it "generate login
             * code" — a label naming the means rather than the end, which is why
             * it never answered "how do I get MetaGer onto my phone?", even
             * though that is exactly what the button does.
             */
            'transfer' => [
                'label' => 'Ytterligare en enhet',
                'action' => 'Logga in en enhet',
                'hint' => 'Visar en kort kod som du skriver in i inloggningsformuläret på den andra enheten — i stället för att skriva av hela nyckeln.',

                'title' => 'Logga in ytterligare en enhet',
                'description' => 'Ange den här koden på den andra enheten i inloggningsformuläret, där nyckeln annars står.',
                'waiting' => 'Hämtar kod …',
                'note' => 'Koden gäller för en enda inloggning och bara så länge den visas här. Stäng det här fönstret så snart du har angett den.',
                'failed' => 'Koden kunde inte hämtas. Stäng fönstret och försök igen om en stund.',
                'close' => 'Stäng',
            ],
        ],

        /**
         * What still lives in the keymanager. A list at the foot rather than the
         * three equal tabs of before: hardly anybody has campaigns, and a third
         * tab claimed otherwise.
         */
        'more' => [
            'heading' => 'Övrigt',
            'orders' => 'Beställningar och fakturor',
            'campaigns' => 'Presentkortskampanjer',
            'help' => 'Hjälp med nyckeln',
            'logout' => 'Logga ut',
            // Signing out only clears the cookie. Someone who does not know
            // that will not click it — and someone who reads it as "delete
            // account" certainly will not.
            'logout_hint' => 'Tar bort nyckeln från den här webbläsaren. Saldot ligger kvar på nyckeln.',
        ],
    ],

    'empty' => [
        'message' => 'Dina Token är slut.',
        'action' => 'Fyll på nu',
    ],
];
