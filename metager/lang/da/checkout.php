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
        'change' => 'Skift beløb',
        'cancel' => 'Tilbage til kontoen',
        'steps' => [
            'aria_two' => 'Opladning i to trin',
            'amount' => 'Beløb',
            'pay' => 'Betal',
        ],
        'summary' => [
            'buying' => 'Du køber',
            'after' => 'Saldo bagefter',
            'price' => 'Pris',
        ],
    ],
    'consent' => [
        'agb' => 'Ved at fortsætte dit køb accepterer du vores <a href=":agblink" target="_blank">Vilkår og betingelser</a>.',
        'label' => 'Jeg accepterer udtrykkeligt udførelsen af kontrakten inden udløbet af tilbagekaldelsesperioden. Jeg forstår, at <a href=":revocation_link" target="_blank">fortrydelsesret</a> udløber ved påbegyndelse af udførelsen af kontrakten. I stedet giver vi dig en frivillig <a href=":refundlink" target="_blank">30-dages returret</a>.',
        'error' => 'Dette felt er påkrævet',
    ],
    'suma' => [
        'label' => 'Bankoverførsel, direkte debitering og mere',
        'heading' => 'Betal',
        'lede' => 'På næste side vælger du, hvordan du vil betale: :methods. Du kan stadig være anonym – navn og e-mail er frivillige, undtagen ved direkte debitering.',
        'submit' => 'Videre til betaling',
        'privacy' => 'Betalingen foregår via SUMA-EV’s betalingsområde (payments.suma-ev.de) og, afhængigt af betalingsmåden, via VR Payment eller PayPal.',
        'methods' => [
            'banktransfer' => 'Bankoverførsel',
            'sepa_directdebit' => 'SEPA direkte debitering',
            'card' => 'Kredit-/betalingskort',
            'wero' => 'Wero',
            'paypal' => 'PayPal',
            'cash' => 'Kontanter med posten',
        ],
        'error' => [
            'unreachable' => 'Noget gik galt under oprettelsen af din ordre. Prøv venligst igen senere.',
        ],
    ],
    'returned' => [
        'heading' => 'Opladning gennemført',
        'pending_heading' => 'Betalingen behandles',
        'paid' => 'Tak! Din nøgle er blevet opladet med :amount tokens.',
        'next' => 'Din saldo er klar med det samme – du kan søge videre nu.',
        'details' => 'Se ordredetaljer',
        'pending' => 'Din betaling behandles stadig. Så snart den når frem til os, bliver din nøgle automatisk opladet.',
    ],
];
