<?php

return [
    'login' => [
        'hint' => 'Bitte melde dich an, um Zugriff auf dein Konto zu erhalten.',
        'email' => 'Email Addresse',
        'code' => 'Login Code',
        'email_sent' => 'Falls dieser Account bereits registriert ist, haben wir dir einen Login Code per Email gesendet. Bitte trage diesen ein um dich anzumelden.',
        'submit' => 'Abschicken',
        'restart' => 'Neu Anmelden'
    ],
    'overview' => [
        "hint" => 'Hier findest du eine Übersicht über deine Bestellungen und Informationen zur Verwendung der API. Bitte stelle sicher, dass nachfolgende Rechnungsdaten aktuell und korrekt sind',
        'invoice-data' => [
            "heading" => "Rechnungsdaten",
            "email" => "Email Addresse",
            "company" => "Firma",
            "full_name" => "Name",
            "first_name" => "Vorname",
            "last_name" => "Nachname",
            "street" => "Straße u. Hausnummer",
            "postal_code" => "Postleitzahl",
            "city" => "Stadt",
            "save" => "Speichern",
            "update" => "Rechnungsdaten aktualisieren"
        ],
        "abo" => [
            "heading" => "Zugriff auf aktuelle Daten",
            "hint" => "Hier kannst du dir einen Zugang für die MetaGer Suchanfrage Logs für die kommenden Monate einrichten. Der Zugang wird im gewählten Zahlungsintervall automatisch verlängert und jeweils nach Zahlungseingang freigeschaltet.",
            "interval" => [
                "label" => "Zahlungsintervall",
                "setting_values" => [
                    "never" => "Niemals",
                    "monthly" => "monatlich",
                    "annual" => "jährlich",
                    "quarterly" => "vierteljährlich",
                    "six-monthly" => "halbjährlich"
                ]
            ],
            "last_invoice" => "Letzte Rechnung",
            "next_invoice" => "Nächste Rechnung",
            "never" => "Niemals",
            "create" => "Einrichten",
            "update" => "Aktualisieren"
        ]
    ],
    "create_abo" => [
        "heading" => "Abo einrichten",
        "interval" => "Zahlungsintervall",
        "conditions" => "Konditionen",
        "amount" => "Bei jeder Zahlung zu leisten",
        "conditions_hint" => "Wir stellen automatisch je Zahlungsintervall eine Rechnung aus. Dein Zugang beinhaltet Zugriff auf die MetaGer Logs für alle im Rechnungszeitraum enthaltenen Monate (inklusive des Aktuellen). Die Rechnung für den folgenden Zeitraum wird jeweils wenn möglich einen Monat vor Beginn ausgestellt, sodass eine nahtlose Nutzung möglich ist.",
        "nda" => "NDA (Verschwiegenheitserklärung)",
        "conditions_nda" => "Die zur Verfügung gestellten Daten können, wenn auch unsortiert, personenbeziehbare Daten enthalten. Aus diesem Grund dürfen die Daten in keiner Form von dir öffentlich zugänglich gemacht werden. Hierzu zählen insbesondere die Rohdaten selbst, aber auch hieraus angelernte Modelle aus dem Bereich des Machine Learnings. Ein öffentlicher Zugriff auf Antworten eines Modelles hingegen ist möglich. Lies dir bitte die nachfolgende NDA (Verschwiegenheitserklärung) genau durch und speicher sie für die eigenen Unetrlagen bevor du ihr durch Fortfahren zustimmst.",
        "accept" => "Ich stimme der NDA (Verschwiegenheitserklärung) und den Zahlungsbedingungen zu",
        "cancel" => "Aktuelles Abo kündigen"
    ],
    "orders" => [
        "heading" => "Bestellungen",
        "status" => [
            "4" => "Abgeschlossen",
            "5" => "Abgebrochen",
            "6" => "Zurückgezahlt",
            "3" => "teilweise bezahlt",
            "2" => "Zugestellt",
            "1" => "Entwurf",
            "-1" => "Überfällig",
            "-2" => "Zahlung Ausstehend",
            "-3" => "Angesehen"
        ],
        "thead" => [
            "from" => "Zugang von",
            "to" => "Zugang bis",
            "price" => "Rechnungsbetrag",
            "status" => "Rechnungsstatus",
            "invoice" => "Rechnung"
        ]
    ],
    "api_keys" => [
        "heading" => "API Schlüssel",
        "hint" => "Um die API abfragen zu können musst Du dich authentifizieren. Hier kannst du dir für deine Geräte API Schlüssel erstellen. <b>Hinweis</b>: Neu erstellte Schlüssel sind nur einmalig auslesbar. Bitte speichern Sie sich diese nach Erstellung ab.",
        "thead" => [
            "name" => "Gerät",
            "key" => "Schlüssel",
            "created_at" => "Erstellt",
            "accessed_at" => "Letzter Zugriff",
            "actions" => "Aktionen"
        ],
        "new" => [
            "heading" => "Neuen Schlüssel erstellen",
            "name" => "Gerätename",
            "placeholder_name" => "Laptop",
            "submit" => "Erstellen"
        ],
        "copy" => "Kopieren",
        "delete" => "Löschen"
    ],
    "api-docs" => [
        "hint" => "Nachfolgend findest du unsere API Dokumentation, welche du verwenden kannst um Logs von unserem Server abzurufen.",
        "link" => "API Dokumentation",
    ]
];