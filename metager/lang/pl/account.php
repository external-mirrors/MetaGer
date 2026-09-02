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
        'signed_in' => 'Zalogowano',
        // The webextension: the key never reaches us, so there is nothing to name.
        'anonymous' => 'zalogowano anonimowo',
        // The mark is decorative and the code is styled shorthand, so the whole
        // answer has to be in the label for anyone not reading the pixels.
        //
        // "ending in", not a bare "key :fingerprint": these are the last six
        // characters of the key (KeyUser::getKeyFingerprint()), and calling them
        // the key invited people to type them into the sign-in form — where,
        // until the guard in the keymanager's POST /key/enter, they resolved to
        // an empty phantom account.
        'aria' => 'Moje konto – klucz kończący się na :fingerprint, :charge Token',
        'aria_nocharge' => 'Moje konto – klucz kończący się na :fingerprint',
        'aria_nofingerprint' => 'Moje konto – :charge Token',
        'aria_anonymous' => 'Moje konto – zalogowano anonimowo przez rozszerzenie przeglądarki',
    ],
    'sidebar' => [
        'balance' => ':charge Token · bez reklam',
        // Not "0 Token · …": at zero the searches are not ad-free, they do
        // not happen at all.
        'balance_empty' => 'Brak Token',
        'manage' => 'Zarządzaj',
        'topup' => 'Doładuj',
        'logout' => 'Wyloguj się',
        'login' => 'Zaloguj się',
        'create' => 'Skonfiguruj',
        'logged_out' => 'Nie zalogowano. Z kluczem szukasz bez reklam i anonimowo.',
        'anonymous_hint' => 'Bez reklam · zarządzane przez rozszerzenie przeglądarki',
        // The webextension holds the key, so the account lives in its popup.
        // The button this labels is hidden until the extension reveals it.
        'extension_settings' => 'Zarządzaj w rozszerzeniu',
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
        'heading' => 'Moje konto',

        // Not "your key: 123456". These are the last six characters, and calling
        // them the key led people to type them into the sign-in form.
        'fingerprint' => 'Klucz kończący się na :fingerprint',
        'fingerprint_unknown' => 'Zalogowano',

        'balance' => [
            'unit' => 'tokenów',
            'one_token' => 'Jeden token to jedno wyszukiwanie.',
            'valid_until' => 'Saldo ważne do :date',
            'empty' => 'Brak salda. Bez tokenów nie możesz wyszukiwać — doładuj, aby kontynuować.',
            'low' => 'Saldo się kończy.',
            'unknown' => 'W tej chwili nie możemy odczytać Twojego salda. To nasza wina, nie Twoja — spróbuj ponownie za kilka minut.',
            'orders_summary' => 'Z :count doładowań, które wygasają jedno po drugim',
            'orders_heading' => 'Daty wygaśnięcia',
            'order' => ':amount tokenów do :date',
        ],

        'actions' => [
            'topup' => 'Doładuj saldo',
            'search' => 'Do wyszukiwarki',
        ],

        'charge' => [
            'heading' => 'Doładuj saldo',
            'lede' => 'Jeden token to jedno wyszukiwanie i kosztuje jeden cent. Wszystkie ceny zawierają VAT.',
            'tokens' => ':amount tokenów',
            'price' => ':price €',
            'more' => 'Wszystkie ceny i sposoby płatności',

            /**
             * Rendered on the German interface only
             * ({@see \App\Support\MembershipOffer}) — the SUMA-EV
             * application form exists in no other language. Translated
             * all the same, so the catalogues stay in step.
             */
            'membership' => [
                'heading' => 'A może zostać członkiem?',
                'text' => 'Członkowie naszego stowarzyszenia non-profit <a href="https://suma-ev.de" target="_blank" rel="noopener">SUMA-EV</a> wyszukują bez dalszych kosztów: klucz jest co miesiąc doładowywany ze składki członkowskiej, a Ty utrzymujesz wyszukiwarkę, zamiast za nią płacić.',
                'action' => 'Zostań członkiem',
            ],

            /**
             * Why no package is on offer right now. Three sentences for three
             * states, all three of which the old page had too — except that it
             * said "your key is already fully charged", which is not true: what
             * is full is not the balance but the number of open top-ups.
             */
            'blocked' => [
                'proxy' => 'Przeglądasz obecnie przez jedną z naszych sesji proxy. W tym czasie doładowanie jest wyłączone dla Twojego bezpieczeństwa — płatność prowadzi do dostawcy płatności, a ten nie powinien widzieć tej sesji. Aby doładować, otwórz tę stronę bez sesji proxy.',
                'full' => 'Na tym kluczu są już trzy doładowania. Gdy tylko najstarsze zostanie zużyte lub wygaśnie, będzie można doładować ponownie.',
                'member' => 'Jesteś członkiem SUMA-EV i wyszukujesz bez dodatkowych kosztów. Pakiet tokenów nie jest Ci potrzebny.',
            ],
        ],

        /**
         * The section the old page did not have: QR code, settings URL and the
         * transfer button sat there in one row, with not a sentence about what
         * they are for.
         */
        'save' => [
            'heading' => 'Zabezpiecz swój dostęp',
            'text' => 'Dopóki ta przeglądarka zachowuje ciasteczko, pozostajesz zalogowany. Jeśli je utraci — nowe urządzenie, wyczyszczone dane przeglądania — Twój klucz jest jedyną drogą powrotu do Twojego salda. Oto on, a oto sposoby, by zabrać go ze sobą.',

            /**
             * The key itself.
             *
             * It has to be here — the sign-in form asks for it first of all.
             * No longer collapsed: the QR code below it carries the same key
             * and is never collapsed, so hiding it here bought nothing.
             */
            'key' => [
                'label' => 'Twój klucz',
                'action' => 'Kopiuj klucz',
                'hint' => '36 znaków. Nimi logujesz się na każdym innym urządzeniu.',
            ],

            'qr' => [
                'label' => 'Kod QR',
                'alt' => 'Kod QR prowadzący do Twojego klucza',
                'action' => 'Zapisz jako obraz',
                'hint' => 'Obraz, o który prosi formularz logowania. Możesz go tam przesłać lub sfotografować aparatem.',
            ],

            'url' => [
                'label' => 'Zakładka',
                'action' => 'Kopiuj adres URL',
                'hint' => 'Otwarcie tego adresu przywraca klucz wraz z ustawieniami wyszukiwania tej przeglądarki.',
            ],

            /**
             * The transfer dialog. The keymanager called it "generate login
             * code" — a label naming the means rather than the end, which is why
             * it never answered "how do I get MetaGer onto my phone?", even
             * though that is exactly what the button does.
             */
            'transfer' => [
                'label' => 'Kolejne urządzenie',
                'action' => 'Zaloguj urządzenie',
                'hint' => 'Pokazuje krótki kod, który wpisujesz w formularzu logowania na drugim urządzeniu — zamiast przepisywać cały klucz.',

                'title' => 'Zaloguj kolejne urządzenie',
                'description' => 'Wpisz ten kod na drugim urządzeniu w formularzu logowania, tam gdzie normalnie znajduje się klucz.',
                'waiting' => 'Pobieranie kodu …',
                'note' => 'Kod jest ważny dla jednego logowania i tylko dopóki jest tu wyświetlany. Zamknij to okno, gdy tylko go wpiszesz.',
                'failed' => 'Nie udało się pobrać kodu. Zamknij okno i spróbuj ponownie za chwilę.',
                'close' => 'Zamknij',
            ],
        ],

        /**
         * What still lives in the keymanager. A list at the foot rather than the
         * three equal tabs of before: hardly anybody has campaigns, and a third
         * tab claimed otherwise.
         */
        'more' => [
            'heading' => 'Więcej',
            'orders' => 'Zamówienia i faktury',
            'campaigns' => 'Akcje z bonami',
            'help' => 'Pomoc dotycząca klucza',
            'logout' => 'Wyloguj się',
            // Signing out only clears the cookie. Someone who does not know
            // that will not click it — and someone who reads it as "delete
            // account" certainly will not.
            'logout_hint' => 'Usuwa klucz z tej przeglądarki. Saldo pozostaje na kluczu.',
        ],
    ],

    'empty' => [
        'message' => 'Twoje Token zostały wyczerpane.',
        'action' => 'Doładuj teraz',
        'message_anonymous' => 'Twoje anonimowe Token zostały wyczerpane.',
    ],
];
