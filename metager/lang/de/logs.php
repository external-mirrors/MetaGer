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
        "conditions_hint" => "Wir stellen automatisch je Zahlungsintervall eine Rechnung aus. Dein Zugang wird nach Zahlungseingang freigeschaltet und beinhaltet Zugriff auf die MetaGer Logs für alle im Rechnungszeitraum enthaltenen Monate (inklusive des Aktuellen). Die Rechnung für den folgenden Zeitraum wird jeweils wenn möglich einen Monat vor Beginn ausgestellt, sodass eine nahtlose Nutzung möglich ist.",
        "nda" => "NDA (Verschwiegenheitserklärung)",
        "conditions_nda" => "Die zur Verfügung gestellten Daten können, wenn auch unsortiert, personenbeziehbare Daten enthalten. Aus diesem Grund dürfen die Daten in keiner Form von dir öffentlich zugänglich gemacht werden. Hierzu zählen insbesondere die Rohdaten selbst, aber auch hieraus angelernte Modelle aus dem Bereich des Machine Learnings. Ein öffentlicher Zugriff auf Antworten eines Modelles hingegen ist möglich. Lies dir bitte die nachfolgende NDA (Verschwiegenheitserklärung) genau durch und speicher sie für die eigenen Unetrlagen bevor du ihr durch Fortfahren zustimmst.",
        "accept" => "Ich stimme der NDA (Verschwiegenheitserklärung) und den Zahlungsbedingungen zu",
        "cancel" => "Aktuelles Abo kündigen"
    ]
];