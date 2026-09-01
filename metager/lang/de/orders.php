<?php

/**
 * Bestellungen und ihre Auftragsbestätigungen (/konto/bestellungen) —
 * App\Http\Controllers\OrderController.
 *
 * Aus dem `/key/<uuid>/orders`-Bereich des Keymanagers übernommen: `lookup.*`
 * und die Zeilenbeschriftungen sind der Wortlaut von dessen `order.json`
 * (`orders.*`, `details.*`, `summary.*`), dieselbe Seite, jetzt hier
 * gerendert. `show.heading`, `show.lookup_hint` und `show.request_invoice`
 * sind neu; `invoice.*` ist der Wortlaut von dessen `invoice.json` (`form.*`).
 */

return [
    'lookup' => [
        'heading' => 'Bestellung nachschlagen',
        'description' => 'Geben Sie die Zahlungs-ID einer Ihrer Bestellungen ein, um deren Details anzuzeigen.',
        'placeholder' => 'Zahlungs-ID',
        'submit' => 'Bestellung anzeigen',
        'error' => [
            'invalid' => 'Das ist keine gültige Zahlungs-ID.',
            'not_found' => 'Zu dieser Zahlungs-ID gibt es keine Bestellung auf Ihrem Schlüssel.',
        ],
    ],

    'show' => [
        'heading' => 'Bestellung :reference',
        'breadcrumb' => 'Bestellungen',
        'thanks' => 'Vielen Dank für Ihren Einkauf!',
        'pending' => 'Ihre Token werden gutgeschrieben, sobald Ihre Zahlung bei uns eingegangen ist. Sie erhalten dann eine Bestätigungsmail.',
        'lookup_hint' => 'Sie können diese Übersicht jederzeit erneut aufrufen, indem Sie Ihre Zahlungs-ID (:reference) eingeben.',
        'order_line' => 'Auftrag :id vom :date',
        'item' => 'MetaGer Schlüssel: Token',
        'count' => 'Anzahl',
        'price' => 'Preis',
        'vat' => 'MwSt. (:rate %)',
        'total' => 'Gesamtbetrag',
        'exchange_rate' => 'Wechselkurs',
        'download_confirmation' => 'Auftragsbestätigung herunterladen',
        'request_invoice' => 'Rechnung erstellen',
    ],

    'invoice' => [
        'heading' => 'Rechnung',
        'breadcrumb' => 'Auftrag :reference',
        'description' => 'Wenn Sie eine Rechnung benötigen, tragen Sie bitte Ihre Rechnungsdaten in das nachfolgende Formular ein.',
        'ready' => 'Für diese Bestellung liegt bereits eine Rechnung vor.',
        'download' => 'Rechnung herunterladen',
        'submit' => 'Rechnung erstellen',
        'storage' => 'Wir sind rechtlich dazu verpflichtet, einmal ausgestellte Rechnungen <span class="bold">10 Jahre</span> lang aufzubewahren. Da eine Rechnung auf Sie persönlich ausgestellt sein muss, enthält sie zwangsläufig personenbeziehbare Daten (Name, Anschrift).',
        'error' => [
            'invalid' => 'Bitte prüfen Sie Ihre Angaben — einige Pflichtfelder fehlen oder sind zu lang.',
        ],
        'field' => [
            'company' => 'Firmenname (optional)',
            'first_name' => 'Vorname',
            'last_name' => 'Nachname',
            'address1' => 'Adresse 1',
            'address2' => 'Adresse 2 (optional)',
            'zip' => 'Postleitzahl',
            'city' => 'Stadt',
            'state' => 'Staat (optional)',
        ],
    ],
];
