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
        'manage' => 'Beheren',
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
        'heading' => 'Mijn account',

        // Not "your key: 123456". These are the last six characters, and calling
        // them the key led people to type them into the sign-in form.
        'fingerprint' => 'Sleutel eindigend op :fingerprint',
        'fingerprint_unknown' => 'Aangemeld',

        'balance' => [
            'unit' => 'tokens',
            'one_token' => 'Eén token is één zoekopdracht.',
            'valid_until' => 'Saldo geldig tot :date',
            'empty' => 'Geen saldo meer. Zonder tokens kunt u niet zoeken — laad op om verder te gaan.',
            'low' => 'Het saldo raakt op.',
            'unknown' => 'We kunnen uw saldo op dit moment niet opvragen. Dat ligt aan ons en niet aan u — probeer het over een paar minuten opnieuw.',
            'orders_summary' => 'Uit :count opladingen, die na elkaar verlopen',
            'orders_heading' => 'Vervaldata',
            'order' => ':amount tokens tot :date',
        ],

        'actions' => [
            'topup' => 'Saldo opladen',
            'search' => 'Naar de zoekfunctie',
        ],

        'charge' => [
            'heading' => 'Saldo opladen',
            'lede' => 'Eén token is één zoekopdracht en kost één cent. Alle prijzen zijn inclusief btw.',
            'tokens' => ':amount tokens',
            'price' => ':price €',
            'more' => 'Alle prijzen en betaalmethoden',

            /**
             * Why no package is on offer right now. Three sentences for three
             * states, all three of which the old page had too — except that it
             * said "your key is already fully charged", which is not true: what
             * is full is not the balance but the number of open top-ups.
             */
            'blocked' => [
                'proxy' => 'U surft momenteel via een van onze proxysessies. Zolang dat zo is, is opladen om veiligheidsredenen uitgeschakeld — een betaling leidt naar een betaaldienstverlener, en die hoort deze sessie niet te zien. Open deze pagina zonder proxysessie om op te laden.',
                'full' => 'Op deze sleutel staan al drie opladingen. Zodra de oudste is opgebruikt of verlopen, kunt u weer opladen.',
                'member' => 'U bent lid van SUMA-EV en zoekt zonder verdere kosten. U hebt geen tokenpakket nodig.',
            ],
        ],

        /**
         * The section the old page did not have: QR code, settings URL and the
         * transfer button sat there in one row, with not a sentence about what
         * they are for.
         */
        'save' => [
            'heading' => 'Stel uw toegang veilig',
            'text' => 'Zolang deze browser de cookie bewaart, blijft u aangemeld. Verliest hij die — een nieuw apparaat, gewiste browsergegevens —, dan is uw sleutel de enige weg terug naar uw saldo. Hier is hij, en hier zijn drie manieren om hem mee te nemen.',

            /**
             * The key itself.
             *
             * It has to be here — the sign-in form asks for it first of all —
             * and it is collapsed, because this page gets photographed for
             * support tickets. The old page showed it large and always.
             */
            'key' => [
                'summary' => 'Sleutel tonen en kopiëren',
                'label' => 'Uw sleutel',
                'action' => 'Sleutel kopiëren',
                'hint' => '36 tekens. Daarmee meldt u zich op elk ander apparaat aan. Ingeklapt, omdat er vaak foto\'s van deze pagina worden gemaakt — wie uw sleutel ziet, zoekt op uw kosten.',
            ],

            'qr' => [
                'label' => 'QR-code',
                'alt' => 'QR-code die naar uw sleutel leidt',
                'action' => 'Als afbeelding opslaan',
                'hint' => 'De afbeelding waar het aanmeldformulier om vraagt. U kunt die daar uploaden of met de camera fotograferen.',
            ],

            'url' => [
                'label' => 'Bladwijzer',
                'action' => 'URL kopiëren',
                'hint' => 'Het openen van deze URL herstelt de sleutel samen met de zoekinstellingen van deze browser.',
            ],

            /**
             * The transfer dialog. The keymanager called it "generate login
             * code" — a label naming the means rather than the end, which is why
             * it never answered "how do I get MetaGer onto my phone?", even
             * though that is exactly what the button does.
             */
            'transfer' => [
                'label' => 'Nog een apparaat',
                'action' => 'Apparaat aanmelden',
                'hint' => 'Toont een korte code die u op het andere apparaat in het aanmeldformulier typt — in plaats van de hele sleutel over te schrijven.',

                'title' => 'Nog een apparaat aanmelden',
                'description' => 'Voer deze code op het andere apparaat in het aanmeldformulier in, daar waar anders de sleutel staat.',
                'waiting' => 'Code ophalen …',
                'note' => 'De code geldt voor één enkele aanmelding en alleen zolang hij hier staat. Sluit dit venster zodra u hem hebt ingevoerd.',
                'failed' => 'De code kon niet worden opgehaald. Sluit het venster en probeer het zo meteen opnieuw.',
                'close' => 'Sluiten',
            ],
        ],

        /**
         * What still lives in the keymanager. A list at the foot rather than the
         * three equal tabs of before: hardly anybody has campaigns, and a third
         * tab claimed otherwise.
         */
        'more' => [
            'heading' => 'Overig',
            'orders' => 'Bestellingen en facturen',
            'campaigns' => 'Voucheracties',
            'help' => 'Hulp bij de sleutel',
            'logout' => 'Afmelden',
            // Signing out only clears the cookie. Someone who does not know
            // that will not click it — and someone who reads it as "delete
            // account" certainly will not.
            'logout_hint' => 'Verwijdert de sleutel uit deze browser. Het saldo blijft op de sleutel staan.',
        ],
    ],

    'empty' => [
        'message' => 'Je Token zijn op.',
        'action' => 'Nu opwaarderen',
    ],
];
