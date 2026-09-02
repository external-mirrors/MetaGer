<?php
return [
    'plugin' => 'MetaGer Installieren',
    'plugin-title' => 'MetaGer-Plugin hinzufügen',
    'key' => [
        'placeholder' => 'Geben Sie Ihren MetaGer Schlüssel ein, um die Suche zu starten.',
        'tooltip' => [
            'nokey' => 'Werbefreie Suche neu einrichten',
            'empty' => 'Token aufgebraucht. Jetzt aufladen.',
            'low' => 'Token bald aufgebraucht. Jetzt aufladen.',
            'full' => 'Werbefreie Suche aktiviert.',
        ],
    ],
    'placeholder' => 'MetaGer – Mehr als eine Suchmaschine',
    'searchbutton' => 'MetaGer-Suche starten',
    'foki' => [
        'web' => 'Web',
        'bilder' => 'Bilder',
        'nachrichten' => 'Nachrichten',
        'science' => 'Wissenschaft',
        'produkte' => 'Produkte',
        'maps' => 'Maps',
    ],
    'adfree' => 'MetaGer werbefrei nutzen',
    'skip' => [
        'search' => 'Weiter zur Eingabe der Suchanfrage',
        'navigation' => 'Zur Navigation springen',
        'fokus' => 'Zur Auswahl des Suchfokus springen',
    ],
    'lang' => 'Hexensprache',
    'searchreset' => 'Eingabe der Suchanfrage löschen',
    'searchbar-replacement' => [
        'tagline' => 'Open-Source. Werbefrei. Anonym.',
        'message' => 'Ihr Schlüssel ist Ihr Zugang – kein Konto, keine E-Mail-Adresse. Nur Ihr Guthaben hängt daran.',
        'have_key' => 'Mit meinem Schlüssel anmelden',
        'first_time' => 'Zum ersten Mal hier?',
        'start' => 'Schlüssel einrichten',
        // Swapped in by resources/js/accountBreadcrumb.js when this browser has
        // rendered a signed-in page before. Three strings, replacing three
        // elements in place — nothing appears, nothing moves.
        'welcome_back' => 'Willkommen zurück.',
        'welcome_back_message' => 'Auf diesem Gerät waren Sie schon einmal angemeldet. Melden Sie sich mit demselben Schlüssel an – Ihr Guthaben ist noch da.',
        'welcome_back_button' => 'Wieder anmelden',
        'key_error' => "Der eingegebene Schlüssel war nicht korrekt. Bitte prüfen Sie die Eingabe.",
        'login' => "Anmelden",
        'login_code_error' => "Der eingegebene Login-Code war nicht gültig. Hinweis: Login-Codes sind nur gültig, solange sie auf einem anderen Gerät angezeigt werden!",
        'payment_id_error' => "Sie haben eine Zahlungskennung eingegeben, die kein korrekter Schlüssel ist. Ihr Schlüssel ist 36 Zeichen lang.",
        'new_key' => 'Noch kein Schlüssel?',
        'extension' => 'Bleiben Sie eingeloggt und anonym mit unserer Webextension',
    ],
    // The landing page shown to a visitor without a key: hero, "how it works",
    // and the five benefit cards. It came from the keymanager's own root page
    // (pass/views/index.ejs, pass/lang/*/index.json), which /keys used to serve
    // and which now redirects here.
    //
    // Placeholders are Laravel's :name, not i18next's {{name}}, and the links
    // are passed in from parts/landing/* so the locale prefix and the /keys
    // paths stay in one place.
    'landing' => [
        'title' => 'Suchen und surfen, ohne beobachtet zu werden',
        'description' => 'MetaGer respektiert Ihre Privatsphäre und lässt Sie auch anonym auf jeder Webseite surfen.',
        'advantages' => [
            'ads' => 'Keine Werbung',
            'tracking' => 'Kein Tracking',
            'logging' => 'Kein Logging',
            'compromise' => 'Keine Kompromisse',
        ],
        'calltoaction' => 'So funktioniert es',
        'benefits' => [
            'browsing' => [
                'heading' => 'Nicht nur anonym suchen – auch anonym surfen',
                'description' => 'Mit Ihrem MetaGer-Schlüssel können Sie beliebige Webseiten auch in einem privaten Browser öffnen, der sicher auf unseren Servern läuft – nicht auf Ihrem Gerät. Webseiten können nicht erkennen, wer Sie sind oder von wo Sie surfen, und nach Ende Ihrer Sitzung wird automatisch alles gelöscht. Keine Installation, keine Einrichtung – einfach öffnen und loslegen.',
                'fingerprinting' => 'Fingerprinting',
                'tracking' => 'Tracking',
            ],
            'ads' => [
                'heading' => 'Ohne Werbeanzeigen',
                'description' => 'Werbeanzeigen und Datenschutz vertragen sich in der Regel nicht gut. Bei MetaGer gibt es deshalb keinerlei Werbung, sodass wir Ihre Privatsphäre kompromisslos schützen können.',
                'ads' => 'Werbung',
                'tracking' => 'Tracking-Links',
            ],
            'logging' => [
                'heading' => 'Ohne Logging',
                'description' => 'Bei Ihren Internetsuchen fallen normalerweise viele Daten an. Wir müssen davon nichts speichern: Unsere Suchmaschine ist so gestaltet, dass wir zur Spambekämpfung keine Logs benötigen. Auch Captchas werden Ihnen bei uns nicht begegnen, selbst wenn Sie ein VPN verwenden.',
                'logging' => 'Logging',
            ],
            'compromise' => [
                'heading' => 'Ohne Kompromisse',
                'description' => 'Statt eines Kontos mit Ihren persönlichen Daten erhalten Sie einfach einen zufällig erzeugten Schlüssel, ganz ohne Namen oder E-Mail-Adresse. Wählen Sie aus mehreren <a href=":linkPaymentMethods">Zahlungsmethoden</a>, darunter die anonyme Zahlung per Bargeld. Unsere <a href=":linkApp">Android-App</a> und Browser-Erweiterung verwenden <a href=":linkToken">anonyme Token</a>: Damit lässt sich sogar beweisen, dass Ihre Suchen anonym bleiben.',
                'compromise' => 'Persönliche Daten',
            ],
            'efficiency' => [
                'heading' => 'Effizienter suchen',
                'description' => 'Finden Sie schneller, was Sie suchen. Wenn es hilfreich ist, blenden wir übersichtliche Deeplinks, relevante Nachrichten und Videos direkt in die Suchergebnisse ein. Auch unsere Bildersuche greift auf zusätzliche Quellen zurück.',
            ],
        ],
        'howitworks' => [
            'heading' => 'So funktioniert es',
            'steps' => [
                [
                    'heading' => 'Schlüssel automatisch erzeugen',
                    'description' => 'Ihr MetaGer-Schlüssel wird automatisch erzeugt. Keine Anmeldung, keine persönlichen Daten nötig. Er ist alles, was Sie für die Nutzung von MetaGer brauchen.',
                ],
                [
                    'heading' => 'Zugang aktivieren',
                    'description' => 'Mit einer einmaligen <a href=":linkCost">Zahlung</a> laden Sie Ihrem Schlüssel Guthaben auf, das wir Token nennen. Damit schalten Sie werbefreie, trackingfreie Suche und anonymes Surfen frei – inklusive aller aktuellen und zukünftigen MetaGer-Funktionen. Etwa 500 Token (5 €) reichen normalerweise für rund 2 Monate.',
                    'membership' => 'Hinweis: Mitglieder unseres gemeinnützigen Trägervereins <a href="https://suma-ev.de" target="_blank">SUMA-EV</a> können MetaGer ohne weitere Kosten verwenden. <a href=":linkMembership" target="_blank">Jetzt Mitglied werden</a>',
                ],
                [
                    'heading' => 'MetaGer überall nutzen',
                    'description' => 'Verwenden Sie denselben Schlüssel auf beliebig vielen Geräten oder teilen Sie ihn mit Freunden und Familie. Rufen Sie MetaGer einfach auf einem beliebigen Gerät auf, geben Sie Ihren Schlüssel ein und schon können Sie suchen – oder anonym surfen.',
                ],
            ],
            'start' => 'Jetzt loslegen',
            'login' => 'Ich habe bereits einen Schlüssel',
        ],
    ],
];
