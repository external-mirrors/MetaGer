<?php

/**
 * Aufladen (/konto/aufladen) — App\Http\Controllers\ChargeController.
 *
 * `cash`, `consent` und micropayment.prepay sind wortgleich aus dem Keymanager
 * übernommen (dessen checkout.json unter payments.cash/payments.prepay/
 * micropayment, dessen order.json unter agb und revocation, je Sprache) —
 * dieselbe Kasse, jetzt hier gerendert. `page`, `manual`,
 * micropayment.lastschrift/directbanking (dort nur Logos, keine Texte),
 * `returned` und vrpayment.label/submit/error.failed sind neu; vrpayment.privacy
 * ist wortgleich aus dem Keymanager übernommen wie cash/consent/micropayment.
 */
return [
    'page' => [
        'change' => 'Menge ändern',
        'methods' => [
            'heading' => 'Zahlungsart wählen',
            'more' => 'Weitere Zahlungsarten',
            'back' => 'Andere Zahlungsart wählen',
        ],
        'cancel' => 'Zurück zum Konto',
    ],

    'cash' => [
        'label' => 'Bargeld',
        'description' => 'Sie können Ihren Schlüssel auch gegen Bargeld aufladen. Lassen Sie uns hierfür einfach folgende Zahlungs-ID postalisch zusammen mit der gewünschten Geldsumme zukommen. Achten Sie bitte darauf, dass die Zahlungs-ID lesbar sein muss, um von uns verarbeitet werden zu können.',
        'note' => 'Folgendes gilt es zu beachten für die Barzahlung:',
        'no_large_values' => 'Senden Sie uns zu Ihrer eigenen Sicherheit nicht mehr als 100€ mit der Post. Wir übernehmen keine Haftung für den Transportweg. Sie sind selbst dafür verantwortlich, dass der Brief bei uns ankommt.',
        'no_coins' => 'Wir akzeptieren ausschließlich Geldscheine. Versenden Sie keine Münzen!',
        'accepted_currencies' => 'Wir akzeptieren ausschließlich folgende Währungen: EUR, USD, CAD, GBP.',
        'currency_translation' => 'Verrechnet werden Beträge von uns stets in EUR. Wenn Sie uns eine andere Währung zusenden, wird der gesendete Betrag zum tagesaktuellen Wechselkurs umgerechnet.',
        'no_refund' => 'Wegen geltender Geldwäschegesetze ist eine Erstattung oder Rücksendung leider nicht möglich. Sobald die Aufladung von uns verbucht wurde, können Sie aber unter "Bestellungen" die versendete Zahlungs-ID eingeben um eine Auftragsübersicht zu erhalten und/oder eine Rechnung anzufordern.',
        'generate' => 'Zahlungs-ID generieren',
        'error' => [
            'unreachable' => 'Beim Erstellen Ihrer Bestellung ist etwas schief gegangen. Versuchen Sie es bitte später erneut.',
        ],
        'order' => [
            'heading' => 'Ihre Zahlungs-ID',
            'copy' => 'Zahlungs-ID kopieren',
            'address_heading' => 'Schicken Sie den Brief an folgende Adresse und notieren Sie sich die Zahlungs-ID für Ihre eigenen Unterlagen',
            'address' => 'SUMA-EV
Postfach 51 01 43
30631 Hannover',
            'expiration' => 'Die Zahlungs-ID ist gültig bis zum :date. Ab diesem Datum kann sie nicht mehr für eine Aufladung verwendet werden.',
            'unique' => 'Verwenden Sie die Zahlungs-ID nur für eine einzige Aufladung. Sie erhalten mit jedem Aufruf dieser Seite eine neue!',
        ],
    ],

    'consent' => [
        'agb' => 'Durch das Fortsetzen Ihres Einkaufs erklären Sie sich mit unseren <a href=":agblink" target="_blank">AGB</a> einverstanden.',
        'label' => 'Ich stimme der Ausführung des Vertrages vor Ablauf der Widerrufsfrist ausdrücklich zu. Ich habe zur Kenntnis genommen, dass das <a href=":revocation_link" target="_blank">Widerrufsrecht</a> mit Beginn der Ausführung des Vertrages erlischt. Stattdessen gewähren wir Ihnen ein freiwilliges <a href=":refundlink" target="_blank">30-tägiges Rückgaberecht</a>.',
        'error' => 'Dieses Feld ist erforderlich',
    ],

    'manual' => [
        'label' => 'Manuell (Dev)',
        'description' => 'Überspringen Sie eine tatsächliche Zahlung. Nur in einer Entwicklungsumgebung verfügbar.',
        'submit' => 'Zahlung abschließen',
    ],

    'micropayment' => [
        'label' => 'Micropayment',
        'prepay' => [
            'label' => 'Überweisung',
            'email' => [
                'label' => 'E-Mail Addresse',
                'description' => 'An diese Addresse werden Ihnen einmalig Informationen zu unserer Bankverbindung und eine Benachrichtigung bei Abschluss der Zahlung zugesendet.',
            ],
        ],
        'lastschrift' => ['label' => 'Lastschrift'],
        'directbanking' => ['label' => 'Sofortüberweisung'],
        'submit' => 'Zahlung durchführen',
        'privacy' => 'Mit dem Klick auf "Zahlung durchführen" werden Sie zu unserem Zahlungsdienstleister <a href="https://micropayment.de" target="_blank">MicroPayment</a> weitergeleitet, um den Kauf abzuschließen. Mehr zum <a href=":link" target="_blank">Datenschutz bei :link_text</a>.',
    ],

    'vrpayment' => [
        'label' => 'VR Payment',
        'submit' => 'Zahlung durchführen',
        'privacy' => 'Wenn Sie auf "Zahlung durchführen" klicken, werden Sie zu unserem Zahlungsdienstleister <a href="https://www.vr-payment.de" target="_blank">VR Payment</a> weitergeleitet, um den Kauf abzuschließen. Mehr über <a href=":link" target="_blank">Datenschutz bei VR Payment</a>.',
        'error' => [
            'failed' => 'VR Payment hat diese Zahlung abgelehnt. Bitte versuchen Sie es erneut oder wählen Sie eine andere Zahlungsart.',
        ],
    ],

    'returned' => [
        'heading' => 'Aufladen abgeschlossen',
        'paid' => 'Vielen Dank! Ihr Schlüssel wurde um :amount Token aufgeladen.',
        'pending' => 'Ihre Zahlung wird noch bearbeitet. Sobald sie bei uns eingegangen ist, wird Ihr Schlüssel automatisch aufgeladen.',
    ],
];
