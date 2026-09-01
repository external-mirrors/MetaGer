<?php

/**
 * Bestellungen und ihre Auftragsbestätigungen (/konto/bestellungen) —
 * App\Http\Controllers\OrderController.
 *
 * Aus dem `/key/<uuid>/orders`-Bereich des Keymanagers übernommen: `lookup.*`
 * und die Zeilenbeschriftungen sind der Wortlaut von dessen `order.json`
 * (`orders.*`, `details.*`, `summary.*`), dieselbe Seite, jetzt hier
 * gerendert. `show.heading` und `show.lookup_hint` sind neu.
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
    ],
];
