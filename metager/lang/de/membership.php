<?php
return [
    'title' => 'Ihre Mitgliedschaft im SUMA-EV',
    'non-de' => 'Derzeit können wir Aufnahmeanträge leider nur für den deutschsprachigen Raum annehmen. Sie können uns aber sehr gerne mit einer <a href=":donationlink">Spende</a> unterstützen.',
    'success' => 'Herzlichen Dank für die Übermittlung Ihres Aufnahmeantrags. Wir werden diesen möglichst schnell bearbeiten. Anschließend erhalten Sie eine Mail mit weiteren Informationen von uns an die angegebene Addresse.',
    'application' => [
        'description' => 'Vielen Dank, dass Sie eine <a href="https://suma-ev.de/mitglieder/" target="_blank">Mitgliedschaft</a> in unserem gemeinnützigen Trägerverein erwägen. Um Ihren Antrag bearbeiten zu können benötigen wir lediglich ein paar Informationen, die Sie hier ausfüllen können.',
        'update' => 'Nachfolgend sehen Sie die bei uns für Ihre Mitgliedschaft hinterlegten Informationen. Mit einem Klick auf "Bearbeiten" können Sie diese ändern. Eine Änderung der Kontaktdaten ist hier nicht möglich. Sollten diese sich geändert haben, senden Sie uns bitte eine <a href=":contact_link" target="_blank">Mail</a> mit Ihren neuen Informationen.',
    ],
    'data' => [
        'description' => 'Folgende Daten haben wir für Ihren Antrag erfasst:',
        'name' => 'Name',
        'email' => 'Email-Adresse',
        "company" => "Firmenname",
        "amount" => "Mitgliedsbeitrag",
        "payment_method" => "Zahlungsmethode",
        "payment_methods" => [
            "banktransfer" => "Banküberweisung",
            "directdebit" => "Lastschrift",
            "paypal" => "PayPal",
            "card" => "Kreditkarte"
        ],
        "payment" => [
            "interval" => [
                "monthly" => "monatlich",
                "quarterly" => "vierteljährlich",
                "six-monthly" => "halbjährlich",
                "annual" => "jährlich"
            ]
        ]
    ],
    'key' => [
        'description' => 'Für die Nutzung von MetaGer wird der folgende Schlüssel verwendet und von uns aufgeladen. Falls Sie bereits angemeldet waren wurde Ihr bestehender Schlüssel benutzt.',
        'later' => 'Die erste Aufladung erfolgt nach Bearbeitung Ihres Antrags',
        'now' => 'Er ist bereits aufgeladen und kann sofort verwendet werden.',
    ],
    'back' => 'Zurück zur Startseite',

];