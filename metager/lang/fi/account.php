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
        'signed_in' => 'Kirjautunut sisään',
        // The webextension: the key never reaches us, so there is nothing to name.
        'anonymous' => 'kirjautunut sisään nimettömästi',
        // The mark is decorative and the code is styled shorthand, so the whole
        // answer has to be in the label for anyone not reading the pixels.
        //
        // "ending in", not a bare "key :fingerprint": these are the last six
        // characters of the key (KeyUser::getKeyFingerprint()), and calling them
        // the key invited people to type them into the sign-in form — where,
        // until the guard in the keymanager's POST /key/enter, they resolved to
        // an empty phantom account.
        'aria' => 'Oma tili – avain, joka päättyy :fingerprint, :charge Token',
        'aria_nocharge' => 'Oma tili – avain, joka päättyy :fingerprint',
        'aria_nofingerprint' => 'Oma tili – :charge Token',
        'aria_anonymous' => 'Oma tili – kirjautunut sisään nimettömästi selainlaajennuksen kautta',
    ],
    'sidebar' => [
        'balance' => ':charge Token · mainokseton',
        // Not "0 Token · …": at zero the searches are not ad-free, they do
        // not happen at all.
        'balance_empty' => 'Token loppu',
        'manage' => 'Hallitse',
        'topup' => 'Lataa',
        'logout' => 'Kirjaudu ulos',
        'login' => 'Kirjaudu sisään',
        'create' => 'Ota käyttöön',
        'logged_out' => 'Et ole kirjautunut sisään. Avaimella haet ilman mainoksia ja nimettömästi.',
        'anonymous_hint' => 'Mainokseton · selainlaajennuksen hallinnassa',
        // The webextension holds the key, so the account lives in its popup.
        // The button this labels is hidden until the extension reveals it.
        'extension_settings' => 'Hallitse laajennuksessa',
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
        'heading' => 'Oma tili',

        // Not "your key: 123456". These are the last six characters, and calling
        // them the key led people to type them into the sign-in form.
        'fingerprint' => 'Avaimen loppu :fingerprint',
        'fingerprint_unknown' => 'Kirjautunut sisään',

        'balance' => [
            'unit' => 'tokenia',
            'one_token' => 'Yksi token on yksi haku.',
            'valid_until' => 'Saldo voimassa :date asti',
            'empty' => 'Saldoa ei ole jäljellä. Ilman tokeneita et voi hakea — lataa lisää jatkaaksesi.',
            'low' => 'Saldo on käymässä vähiin.',
            'unknown' => 'Saldoa ei juuri nyt saada haettua. Vika on meissä eikä sinussa — yritä uudelleen muutaman minuutin kuluttua.',
            'orders_summary' => ':count latauksesta, jotka vanhenevat yksi kerrallaan',
            'orders_heading' => 'Vanhenemispäivät',
            'order' => ':amount tokenia :date asti',
        ],

        'actions' => [
            'topup' => 'Lataa saldoa',
            'search' => 'Hakuun',
        ],

        'charge' => [
            'heading' => 'Lataa saldoa',
            'lede' => 'Yksi token on yksi haku ja maksaa sentin. Kaikki hinnat sisältävät arvonlisäveron.',
            'tokens' => ':amount tokenia',
            'price' => ':price €',
            'more' => 'Kaikki hinnat ja maksutavat',

            /**
             * Why no package is on offer right now. Three sentences for three
             * states, all three of which the old page had too — except that it
             * said "your key is already fully charged", which is not true: what
             * is full is not the balance but the number of open top-ups.
             */
            'blocked' => [
                'proxy' => 'Selaat parhaillaan yhden välityspalvelinistuntomme kautta. Sen aikana lataaminen on turvallisuutesi vuoksi poissa käytöstä — maksu johtaa maksupalveluntarjoajalle, eikä sen pidä nähdä tätä istuntoa. Avaa tämä sivu ilman välityspalvelinistuntoa ladataksesi.',
                'full' => 'Tällä avaimella on jo kolme latausta. Heti kun vanhin on käytetty tai vanhentunut, voit ladata uudelleen.',
                'member' => 'Olet SUMA-EV:n jäsen ja haet ilman lisäkuluja. Et tarvitse token-pakettia.',
            ],
        ],

        /**
         * The section the old page did not have: QR code, settings URL and the
         * transfer button sat there in one row, with not a sentence about what
         * they are for.
         */
        'save' => [
            'heading' => 'Varmista pääsysi',
            'text' => 'Niin kauan kuin tämä selain säilyttää evästeen, pysyt kirjautuneena. Jos se menettää sen — uusi laite, tyhjennetyt selaustiedot — avaimesi on ainoa tie takaisin saldosi luo. Tässä se on, ja tässä kolme tapaa ottaa se mukaan.',

            /**
             * The key itself.
             *
             * It has to be here — the sign-in form asks for it first of all —
             * and it is collapsed, because this page gets photographed for
             * support tickets. The old page showed it large and always.
             */
            'key' => [
                'summary' => 'Näytä ja kopioi avain',
                'label' => 'Avaimesi',
                'action' => 'Kopioi avain',
                'hint' => '36 merkkiä. Näillä kirjaudut sisään millä tahansa muulla laitteella. Suljettuna, koska tästä sivusta otetaan usein valokuvia — se, joka näkee avaimesi, hakee sinun kustannuksellasi.',
            ],

            'qr' => [
                'label' => 'QR-koodi',
                'alt' => 'QR-koodi, joka johtaa avaimeesi',
                'action' => 'Tallenna kuvana',
                'hint' => 'Kuva, jota kirjautumislomake pyytää. Voit ladata sen sinne tai valokuvata sen kameralla.',
            ],

            'url' => [
                'label' => 'Kirjanmerkki',
                'action' => 'Kopioi URL',
                'hint' => 'Tämän URL-osoitteen avaaminen palauttaa avaimen sekä tämän selaimen hakuasetukset.',
            ],

            /**
             * The transfer dialog. The keymanager called it "generate login
             * code" — a label naming the means rather than the end, which is why
             * it never answered "how do I get MetaGer onto my phone?", even
             * though that is exactly what the button does.
             */
            'transfer' => [
                'label' => 'Toinen laite',
                'action' => 'Kirjaa laite sisään',
                'hint' => 'Näyttää lyhyen koodin, jonka kirjoitat toisen laitteen kirjautumislomakkeeseen — koko avaimen kopioimisen sijaan.',

                'title' => 'Kirjaa toinen laite sisään',
                'description' => 'Syötä tämä koodi toisella laitteella kirjautumislomakkeeseen, siihen kohtaan, jossa avain muuten olisi.',
                'waiting' => 'Haetaan koodia …',
                'note' => 'Koodi kelpaa yhteen kirjautumiseen ja vain niin kauan kuin se näkyy tässä. Sulje tämä ikkuna heti kun olet syöttänyt sen.',
                'failed' => 'Koodia ei saatu haettua. Sulje ikkuna ja yritä hetken kuluttua uudelleen.',
                'close' => 'Sulje',
            ],
        ],

        /**
         * What still lives in the keymanager. A list at the foot rather than the
         * three equal tabs of before: hardly anybody has campaigns, and a third
         * tab claimed otherwise.
         */
        'more' => [
            'heading' => 'Muuta',
            'orders' => 'Tilaukset ja laskut',
            'campaigns' => 'Lahjakorttikampanjat',
            'help' => 'Ohjeita avaimeen',
            'logout' => 'Kirjaudu ulos',
            // Signing out only clears the cookie. Someone who does not know
            // that will not click it — and someone who reads it as "delete
            // account" certainly will not.
            'logout_hint' => 'Poistaa avaimen tästä selaimesta. Saldo jää avaimelle.',
        ],
    ],

    'empty' => [
        'message' => 'Token ovat loppuneet.',
        'action' => 'Lataa nyt',
    ],
];
