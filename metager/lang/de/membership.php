<?php

return [
    "title" => "Ihre Mitgliedschaft im SUMA-EV",
    "description" => "Vielen Dank, dass Sie eine <a href='https://suma-ev.de/mitglieder/' target='_blank'>Mitgliedschaft</a> in unserem gemeinnützigen Trägerverein erwägen. Um Ihren Antrag bearbeiten zu können benötigen wir lediglich ein paar Informationen, die Sie hier ausfüllen können.",
    "submit" => "Abschicken",
    "success" => "Herzlichen Dank für die Übermittlung Ihres Aufnahmeantrags. Wir werden diesen möglichst schnell bearbeiten. Anschließend erhalten Sie eine Mail mit weiteren Informationen von uns an die angegebene Addresse.",
    "startpage" => "Zurück zur Startseite",
    "contact" => [
        "title" => "1. Ihre Kontaktdaten",
        "name" => [
            "label" => "Ihr Name",
            "placeholder" => "Max Mustermann / Muster GmbH"
        ],
        "email" => [
            "label" => "Ihre Email Addresse",
            "placeholder" => "max@mustermann.de"
        ]
    ],
    "fee" => [
        "title" => "2. Ihr Mitgliedsbeitrag",
        "description" => "Wählen Sie nachfolgend bitte Ihren gewünschten Mitgliedsbeitrag (mtl.) aus.",
        "amount" => [
            "custom" => [
                "label" => "Wunschbetrag",
                "placeholder" => "5,00€"
            ]
        ]
    ],
    "payment" => [
        "interval" => [
            "title" => "3. Ihr Zahlungsintervall",
            "annual" => "jährlich",
            "six-monthly" => "halbjährlich",
            "quarterly" => "vierteljährlich",
            "monthly" => "monatlich"
        ],
        "method" => [
            "title" => "4. Ihre Zahlungsmethode",
            "directdebit" => [
                "label" => "SEPA Lastschrift",
                "accountholder" => [
                    "label" => "Kontoinhaber (falls abweichend)",
                    "placeholder" => "Max Mustermann"
                ]
            ],
            "banktransfer" => "Banküberweisung"
        ]
    ]
];