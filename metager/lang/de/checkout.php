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
 *
 * `paypal.heading`/`submit`/`loading`/`cancel`/`error.generic`/`card.*` sind
 * wortgleich aus dem Keymanager übernommen (dessen checkout.json unter
 * `paypal`); `paypal.privacy` und `paypal.error.not_available` ebenso
 * (dessen `charge.paypal-privacy`/`charge.not-available`). `paypal.funding.*`
 * und `paypal.noscript` sind neu.
 *
 * checkout/index.blade.php zeigt alle Zahlweisen einzeln und flach — nicht
 * erst den Anbieter (PayPal, Micropayment), dann die Zahlweise: wer bezahlen
 * will, sucht eine Zahlweise, die er kennt, keinen Anbieter, den er nicht
 * kennt. `vrpayment.label` heißt deshalb "Wero", nicht "VR Payment" — die
 * einzige Zahlweise dahinter. `micropayment.label`/`paypal.label` gibt es
 * darum nicht mehr; `checkout.paypal.funding.*` beschriftet die sieben
 * PayPal-Kacheln direkt in der flachen Liste. `page.methods.cash_note` ist
 * die einzige Notiz, die geblieben ist — die anderen Zahlweisen sagen mit
 * ihrem eigenen Namen schon, was sie sind. Reihenfolge der Kacheln:
 * Datenschutzfreundlichkeit vor Einführungsreihenfolge — Bargeld (anonym),
 * Wero, die drei Micropayment-Zahlweisen, dann die sieben PayPal-Zahlweisen.
 */
return [
    'page' => [
        'change' => 'Menge ändern',
        'methods' => [
            'heading' => 'Zahlungsart wählen',
            'more' => 'Weitere Zahlungsarten',
            'back' => 'Andere Zahlungsart wählen',
            'cash_note' => 'Anonym',
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
        'label' => 'Wero',
        'submit' => 'Zahlung durchführen',
        'privacy' => 'Wenn Sie auf "Zahlung durchführen" klicken, werden Sie zu unserem Zahlungsdienstleister <a href="https://www.vr-payment.de" target="_blank">VR Payment</a> weitergeleitet, um den Kauf abzuschließen. Mehr über <a href=":link" target="_blank">Datenschutz bei VR Payment</a>.',
        'error' => [
            'failed' => 'VR Payment hat diese Zahlung abgelehnt. Bitte versuchen Sie es erneut oder wählen Sie eine andere Zahlungsart.',
            'onion' => 'Wero ist über unsere Onion-Adresse nicht verfügbar — der Zahlungsdienstleister kann Sie danach nicht hierher zurückschicken. Bitte wählen Sie eine andere Zahlungsart.',
        ],
    ],

    'paypal' => [
        'heading' => 'Zahlung durchführen',
        'submit' => 'Zahlung durchführen',
        'loading' => 'Zahlungsmethode wird geladen',
        'cancel' => 'Der Zahlungsvorgang wurde storniert. Wenn Ihre Zahlung vor der Stornierung durchgeführt wurde, wird Ihre Bestellung bearbeitet, sobald die Zahlung vom Zahlungsdienstleister bestätigt wurde. Andernfalls versuchen Sie es bitte erneut.',
        'privacy' => 'Zahlungsmethoden in dieser Gruppe erfordern zwar meist keinen PayPal Account, werden aber dort verarbeitet. Mehr zum <a href=":link" target="_blank">Datenschutz bei PayPal</a>.',
        // Nicht angeboten, ohne Javascript aufgerufen (Lesezeichen, direkt
        // eingegeben) — /konto/aufladen/<menge>/paypal bietet die Kachel
        // ohne Javascript gar nicht erst an.
        'noscript' => 'Diese Zahlungsart benötigt Javascript. Bitte wählen Sie eine andere Zahlungsart oder aktivieren Sie Javascript.',
        'funding' => [
            'paypal' => 'PayPal',
            'card' => 'Kredit-/Debitkarte',
            'p24' => 'Przelewy24',
            'bancontact' => 'Bancontact',
            'blik' => 'BLIK',
            'eps' => 'EPS',
            'mybank' => 'MyBank',
        ],
        'error' => [
            'not_available' => 'Die gewählte Zahlungsart ist in Ihrer Region leider nicht verfügbar.',
            'generic' => 'Der Zahlungsvorgang wurde aufgrund eines Fehlers abgebrochen. Wenn Ihre Zahlung vor der Stornierung durchgeführt wurde, wird Ihre Bestellung bearbeitet, sobald die Zahlung vom Zahlungsdienstleister bestätigt wurde. Andernfalls versuchen Sie es bitte erneut.',
        ],
        'card' => [
            'label' => 'Kredit- / Debitkarte',
            'name' => 'Name des Karteninhabers (optional)',
            'number' => 'Kartennummer',
            'expiration' => 'Gültig bis',
            'cvv' => 'CVV',
            'error' => [
                'generic' => 'Kreditkarte wurde vom Kreditinstitut abgelehnt',
                '9500' => 'Kreditkarte als betrügerisch abgelehnt',
                '5100' => 'Die Kreditkarte wurde vom Kreditinstitut abgelehnt',
                '00N7' => 'Falsche CVV. Bitte Eingabe überprüfen',
                '5400' => 'Kreditkarte abgelaufen',
                '5180' => 'Luhn Überprüfung fehlgeschlagen',
                '5120' => 'Kreditkarte wurde wegen nicht ausreichender Deckung abgelehnt.',
                '9520' => 'Kreditkarte als verloren/gestohlen abgelehnt',
                '0500' => 'Kreditkarte wurde vom Kreditinstitut abgelehnt',
                '1330' => 'Kreditkarte ungültig. Bitte überprüfen Sie Ihre Eingabe',
                '3ds' => '3D Authentifizierung fehlgeschlagen',
            ],
        ],
    ],

    'returned' => [
        'heading' => 'Aufladen abgeschlossen',
        'paid' => 'Vielen Dank! Ihr Schlüssel wurde um :amount Token aufgeladen.',
        'next' => 'Ihr Guthaben steht sofort bereit — Sie können direkt weitersuchen.',
        'details' => 'Bestelldetails ansehen',
        'pending' => 'Ihre Zahlung wird noch bearbeitet. Sobald sie bei uns eingegangen ist, wird Ihr Schlüssel automatisch aufgeladen.',
    ],
];
