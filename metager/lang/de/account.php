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
        'signed_in' => 'Angemeldet',
        // The webextension: the key never reaches us, so there is nothing to name.
        'anonymous' => 'anonym angemeldet',
        // The mark is decorative and the code is styled shorthand, so the whole
        // answer has to be in the label for anyone not reading the pixels.
        //
        // "ending in", not a bare "key :fingerprint": these are the last six
        // characters of the key (KeyUser::getKeyFingerprint()), and calling them
        // the key invited people to type them into the sign-in form — where,
        // until the guard in the keymanager's POST /key/enter, they resolved to
        // an empty phantom account.
        'aria' => 'Mein Konto – Schlüssel endet auf :fingerprint, :charge Token',
        'aria_nocharge' => 'Mein Konto – Schlüssel endet auf :fingerprint',
        'aria_nofingerprint' => 'Mein Konto – :charge Token',
        'aria_anonymous' => 'Mein Konto – anonym über die Web-Erweiterung angemeldet',
    ],
    'sidebar' => [
        'balance' => ':charge Token · werbefrei',
        // Not "0 Token · werbefrei": at zero the searches are not ad-free,
        // they do not happen at all.
        'balance_empty' => 'Keine Token mehr',
        // Ein einzelnes Verb wie die Nachbarn 'topup'/'logout' — nicht "Konto
        // verwalten": das Konto ist hier schon der Kontext (Kachel direkt
        // darüber zeigt Guthaben und Schlüssel), und der zweite lange
        // deutsche Zusammensatz war im Sidebar-Knopf einzeilig zu breit, um
        // noch mittig zu stehen.
        'manage' => 'Verwalten',
        'topup' => 'Aufladen',
        'logout' => 'Abmelden',
        'login' => 'Anmelden',
        'create' => 'Einrichten',
        'logged_out' => 'Nicht angemeldet. Mit einem Schlüssel suchen Sie werbefrei und anonym.',
        'anonymous_hint' => 'Werbefrei · verwaltet von der Web-Erweiterung',
        // The webextension holds the key, so the account lives in its popup.
        // The button this labels is hidden until the extension reveals it.
        'extension_settings' => 'In der Erweiterung verwalten',
    ],
    /**
     * Die Kontoseite selbst — /konto, aus /keys/key/<uuid> hierher gezogen.
     *
     * Aus pass/lang/<locale>/key.json des Keymanagers übernommen, aber zum
     * größeren Teil neu. Die alte Seite bestand fast nur aus Beschriftungen für
     * Knöpfe: „URL kopieren“, „Teilen“, „In Datei sichern“, „Login Code
     * erzeugen“. Was sie nirgends sagte, ist, wofür man das eine oder das
     * andere tut — und genau danach fragt der Support.
     *
     * Nicht mitgezogen: `key.share.*`. Der Teilen-Knopf gab den
     * Einstellungs-URL an `navigator.share` weiter, also an das Teilen-Menü des
     * Betriebssystems, mitsamt Schlüssel. Ein Konto weiterzureichen ist keine
     * Handlung, für die ein Knopf werben sollte; wer es doch will, kopiert den
     * URL. Der Kopierknopf ist geblieben.
     */
    'page' => [
        'heading' => 'Mein Konto',

        // Nicht „Ihr Schlüssel: 123456“. Es sind die letzten sechs Zeichen,
        // und sie als den Schlüssel zu bezeichnen hat Leute dazu gebracht, sie
        // ins Anmeldeformular zu tippen.
        'fingerprint' => 'Schlüssel endet auf :fingerprint',
        'fingerprint_unknown' => 'Angemeldet',

        'balance' => [
            'unit' => 'Token',
            'one_token' => 'Ein Token ist eine Suche.',
            'valid_until' => 'Guthaben gültig bis :date',
            'empty' => 'Kein Guthaben mehr. Ohne Token können Sie nicht suchen — laden Sie auf, um weiterzusuchen.',
            'low' => 'Das Guthaben geht zur Neige.',
            'unknown' => 'Das Guthaben lässt sich gerade nicht abfragen. Das liegt an uns und nicht an Ihnen — versuchen Sie es in ein paar Minuten noch einmal.',
            'orders_summary' => 'Aus :count Aufladungen, die nacheinander verfallen',
            'orders_heading' => 'Verfallsdaten',
            'order' => ':amount Token bis :date',
        ],

        'actions' => [
            'topup' => 'Guthaben aufladen',
            'search' => 'Zur Suche',
        ],

        'charge' => [
            'heading' => 'Guthaben aufladen',
            // Der Preis steht auf /preise ausführlich; hier reicht der Satz,
            // der die Zahlen in den Kacheln erklärt.
            'lede' => 'Ein Token ist eine Suche und kostet einen Cent. Die Preise verstehen sich inklusive Mehrwertsteuer.',
            'tokens' => ':amount Token',
            'price' => ':price €',
            'more' => 'Alle Preise und Zahlungswege',

            /**
             * Warum gerade kein Paket angeboten wird. Drei Sätze für drei
             * Zustände, die alle drei schon auf der alten Seite standen — nur
             * dass sie dort „Ihr Schlüssel ist bereits voll aufgeladen“ sagte,
             * was nicht stimmt: voll ist nicht das Guthaben, sondern die Zahl
             * der offenen Aufladungen.
             */
            'blocked' => [
                'proxy' => 'Sie surfen gerade über eine unserer Proxy-Sitzungen. Zu Ihrer Sicherheit ist das Aufladen dabei abgeschaltet — ein Bezahlvorgang führt zu einem Zahlungsdienstleister, und der soll diese Sitzung nicht sehen. Rufen Sie diese Seite ohne Proxy-Sitzung auf, um aufzuladen.',
                'full' => 'Auf diesem Schlüssel liegen bereits drei Aufladungen. Sobald die älteste verbraucht oder verfallen ist, können Sie wieder aufladen.',
                'member' => 'Sie sind Mitglied im SUMA-EV und suchen ohne weitere Kosten. Ein Token-Paket brauchen Sie nicht.',
            ],
        ],

        /**
         * Der Abschnitt, den es auf der alten Seite so nicht gab: dort lagen
         * QR-Code, Einstellungs-URL und der Übertragen-Knopf nebeneinander in
         * einer Reihe, ohne einen Satz dazu, wozu sie da sind.
         */
        'save' => [
            'heading' => 'Zugang sichern',
            'text' => 'Solange dieser Browser das Cookie behält, bleiben Sie angemeldet. Verliert er es — neues Gerät, gelöschte Browserdaten —, ist Ihr Schlüssel der einzige Weg zurück zu Ihrem Guthaben. Hier ist er, und hier sind drei Arten, ihn mitzunehmen.',

            /**
             * Der Schlüssel selbst.
             *
             * Er muss hier stehen — das Anmeldeformular fragt in erster Linie
             * nach ihm —, und er steht zugeklappt, weil diese Seite für
             * Supportanfragen fotografiert wird. Die alte Seite zeigte ihn
             * groß und immer.
             */
            'key' => [
                'summary' => 'Schlüssel anzeigen und kopieren',
                'label' => 'Ihr Schlüssel',
                'action' => 'Schlüssel kopieren',
                'hint' => '36 Zeichen. Damit melden Sie sich auf jedem weiteren Gerät an. Zugeklappt, weil diese Seite oft fotografiert wird — wer Ihren Schlüssel sieht, sucht auf Ihre Kosten.',
            ],

            'qr' => [
                'label' => 'QR-Code',
                'alt' => 'QR-Code, der zu Ihrem Schlüssel führt',
                'action' => 'Als Bild speichern',
                'hint' => 'Das Bild, nach dem das Anmeldeformular fragt. Sie können es dort hochladen oder mit der Kamera abfotografieren.',
            ],

            'url' => [
                'label' => 'Lesezeichen',
                'action' => 'URL kopieren',
                'hint' => 'Ein Aufruf dieses URLs richtet den Schlüssel samt der Sucheinstellungen dieses Browsers wieder ein.',
            ],

            /**
             * Der Übertragen-Dialog. Hieß im Keymanager „Login Code erzeugen“ —
             * eine Beschriftung, die das Mittel nennt und nicht den Zweck, und
             * damit die Frage „wie bekomme ich MetaGer auf mein Telefon?“ nicht
             * beantwortet, obwohl der Knopf genau das tut.
             */
            'transfer' => [
                'label' => 'Weiteres Gerät',
                'action' => 'Gerät anmelden',
                'hint' => 'Zeigt einen kurzen Code, den Sie auf dem anderen Gerät ins Anmeldeformular eintippen — statt den ganzen Schlüssel abzuschreiben.',

                'title' => 'Weiteres Gerät anmelden',
                'description' => 'Geben Sie diesen Code auf dem anderen Gerät im Anmeldeformular ein, dort, wo sonst der Schlüssel steht.',
                'waiting' => 'Code wird geholt …',
                'note' => 'Der Code gilt für eine einzige Anmeldung und nur, solange er hier steht. Schließen Sie dieses Fenster, sobald Sie ihn eingegeben haben.',
                'failed' => 'Der Code ließ sich nicht holen. Schließen Sie das Fenster und versuchen Sie es gleich noch einmal.',
                'close' => 'Schließen',
            ],
        ],

        /**
         * Was noch im Keymanager liegt. Als Liste am Fuß und nicht als
         * gleichrangige Reiter wie vorher: Aktionen hat fast niemand, und ein
         * dritter Reiter behauptete das Gegenteil.
         */
        'more' => [
            'heading' => 'Weiteres',
            'orders' => 'Bestellungen und Rechnungen',
            'campaigns' => 'Gutscheinaktionen',
            'help' => 'Hilfe zum Schlüssel',
            'logout' => 'Abmelden',
            // Abmelden löscht nur das Cookie. Wer das nicht weiß, klickt es
            // nicht an — und wer es für „Konto löschen“ hält, erst recht nicht.
            'logout_hint' => 'Entfernt den Schlüssel aus diesem Browser. Das Guthaben bleibt auf dem Schlüssel.',
        ],
    ],

    'empty' => [
        'message' => 'Ihre Token sind aufgebraucht.',
        'action' => 'Jetzt aufladen',
        'message_anonymous' => 'Ihre anonymen Token sind aufgebraucht.',
    ],
];
