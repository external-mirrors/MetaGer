<?php

/**
 * Bestellungen und ihre Auftragsbestätigungen (/konto/bestellungen) —
 * App\Http\Controllers\OrderController.
 *
 * Aus dem `/key/<uuid>/orders`-Bereich des Keymanagers übernommen: `lookup.*`
 * und die Zeilenbeschriftungen sind der Wortlaut von dessen `order.json`
 * (`orders.*`, `details.*`, `summary.*`), dieselbe Seite, jetzt hier
 * gerendert. `show.heading`, `show.lookup_hint` und `show.request_refund`
 * sind neu; `refund.*` ist der Wortlaut von dessen `order.json` (`refund.*`).
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
        'documents' => 'Beleg und Rechnung',
        'request_refund' => 'Erstattung anfragen',
    ],


    'refund' => [
        'heading' => 'Erstattung',
        'breadcrumb' => 'Auftrag :reference',
        'unavailable' => 'Für diese Bestellung gibt es kein erstattungsfähiges Guthaben mehr — entweder wurde bereits eine Erstattung angefragt, oder die verwendete Zahlungsart unterstützt keine Erstattungsanfrage über dieses Formular.',
        'success' => 'Ihre Anfrage wurde uns erfolgreich zugestellt. Wir bearbeiten diese so schnell wie möglich. Je nach Zahlungsmethode kann es einige Tage dauern, bis eine Erstattung in Ihren Umsätzen sichtbar wird.',
        'description' => 'Sind Sie unzufrieden mit Ihrem Schlüssel? Das bedauern wir sehr! Selbstverständlich erstatten wir Ihnen in diesem Fall den Rechnungsbetrag. Eine Erstattung erfolgt stets auf das gleiche Konto, welches bei der ursprünglichen Zahlung verwendet wurde. Gerne nehmen wir auch Ihre Kritik entgegen.',
        'partial_note' => 'Ein Teil Ihres gekauften Guthabens wurde bereits verbraucht. Wir können Ihnen deshalb lediglich <span class="bold">:count</span> von <span class="bold">:total</span> Suchanfragen erstatten.',
        'message' => [
            'label' => 'Ihre Nachricht (optional)',
        ],
        'submit' => ':amount € Erstattung anfragen',
        'error' => [
            'not_allowed' => 'Für diese Bestellung ist keine Erstattung mehr möglich.',
            'unreachable' => 'Fehler beim Senden Ihrer Nachricht. Bitte versuchen Sie es später erneut.',
        ],
    ],
];
