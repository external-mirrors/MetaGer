<?php

/**
 * Aufladen (/konto/aufladen) — App\Http\Controllers\ChargeController.
 *
 * Bezahlt wird bei suma-payments: hier stehen nur noch die Zustimmung
 * (`suma`, `consent`), die Schrittleiste und Kurzfassung (`page`) und die
 * Rückkehrseite (`returned`). Die Texte der eigenen Seiten für Bar, Wero,
 * micropayment und PayPal sind mit ihnen gegangen (suma-crm
 * docs/keymanager-migration-plan.md, Schritt 7).
 */
return [
    'page' => [
        'change' => 'Menge ändern',
        'cancel' => 'Zurück zum Konto',
        'steps' => [
            'aria_two' => 'Aufladen in zwei Schritten',
            'amount' => 'Menge',
            'pay' => 'Bezahlen',
        ],
        'summary' => [
            'buying' => 'Sie kaufen',
            'after' => 'Guthaben danach',
            'price' => 'Preis',
        ],
    ],
    'consent' => [
        'agb' => 'Durch das Fortsetzen Ihres Einkaufs erklären Sie sich mit unseren <a href=":agblink" target="_blank">AGB</a> einverstanden.',
        'label' => 'Ich stimme der Ausführung des Vertrages vor Ablauf der Widerrufsfrist ausdrücklich zu. Ich habe zur Kenntnis genommen, dass das <a href=":revocation_link" target="_blank">Widerrufsrecht</a> mit Beginn der Ausführung des Vertrages erlischt. Stattdessen gewähren wir Ihnen ein freiwilliges <a href=":refundlink" target="_blank">30-tägiges Rückgaberecht</a>.',
        'error' => 'Dieses Feld ist erforderlich',
    ],
    'suma' => [
        'label' => 'Überweisung, Lastschrift und mehr',
        'heading' => 'Bezahlen',
        'lede' => 'Auf der nächsten Seite wählen Sie, wie Sie bezahlen: :methods. Sie können anonym bezahlen – Name und E-Mail sind freiwillig, außer bei der Lastschrift.',
        'submit' => 'Weiter zur Zahlung',
        'privacy' => 'Die Zahlung läuft über den Zahlungsbereich des SUMA-EV (payments.suma-ev.de). Überweisung, SEPA-Lastschrift und Bargeld per Post wickeln wir selbst ab – ein externer Zahlungsanbieter ist nur bei Karte und Wero (VR Payment) sowie bei PayPal beteiligt.',
        'methods' => [
            'banktransfer' => 'Überweisung',
            'sepa_directdebit' => 'SEPA-Lastschrift',
            'card' => 'Kredit-/Debitkarte',
            'wero' => 'Wero',
            'paypal' => 'PayPal',
            'cash' => 'Bargeld per Post',
        ],
        'error' => [
            'unreachable' => 'Beim Erstellen Ihrer Bestellung ist etwas schief gegangen. Versuchen Sie es bitte später erneut.',
        ],
    ],
    'returned' => [
        'heading' => 'Aufladen abgeschlossen',
        'pending_heading' => 'Zahlung wird bearbeitet',
        'paid' => 'Vielen Dank! Ihr Schlüssel wurde um :amount Token aufgeladen.',
        'next' => 'Ihr Guthaben steht sofort bereit — Sie können direkt weitersuchen.',
        'details' => 'Bestelldetails ansehen',
        'pending' => 'Ihre Zahlung wird noch bearbeitet. Sobald sie bei uns eingegangen ist, wird Ihr Schlüssel automatisch aufgeladen.',
    ],
];
