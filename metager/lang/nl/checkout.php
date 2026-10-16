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
        'change' => 'Aantal wijzigen',
        'cancel' => 'Terug naar account',
        'steps' => [
            'aria_two' => 'Opwaarderen in twee stappen',
            'amount' => 'Aantal',
            'pay' => 'Betalen',
        ],
        'summary' => [
            'buying' => 'U koopt',
            'after' => 'Tegoed daarna',
            'price' => 'Prijs',
        ],
    ],
    'consent' => [
        'agb' => 'Door verder te gaan met je aankoop, ga je akkoord met onze <a href=":agblink" target="_blank">Algemene voorwaarden</a>.',
        'label' => 'Ik ga uitdrukkelijk akkoord met de uitvoering van het contract voordat de herroepingstermijn is verstreken. Ik begrijp dat het <a href=":revocation_link" target="_blank">herroepingsrecht</a> vervalt bij aanvang van de uitvoering van het contract. In plaats daarvan verlenen wij u een vrijwillig <a href=":refundlink" target="_blank">herroepingsrecht van 30 dagen</a>.',
        'error' => 'Dit veld is verplicht',
    ],
    'suma' => [
        'label' => 'Overschrijving, incasso en meer',
        'heading' => 'Betalen',
        'lede' => 'Op de volgende pagina kiest u hoe u betaalt: :methods. U kunt anoniem betalen – naam en e-mail zijn vrijwillig, behalve bij incasso.',
        'submit' => 'Verder naar betaling',
        'privacy' => 'De betaling loopt via het betaalgedeelte van SUMA-EV (payments.suma-ev.de). Overschrijving, SEPA-incasso en contant geld per post handelen wij zelf af – een externe betaaldienst is alleen betrokken bij kaart en Wero (VR Payment) en bij PayPal.',
        'methods' => [
            'banktransfer' => 'Bankoverschrijving',
            'sepa_directdebit' => 'SEPA-incasso',
            'card' => 'Credit-/debitcard',
            'wero' => 'Wero',
            'paypal' => 'PayPal',
            'cash' => 'Contant per post',
        ],
        'error' => [
            'unreachable' => 'Er is iets misgegaan bij het aanmaken van uw bestelling. Probeer het later nog eens.',
        ],
    ],
    'returned' => [
        'heading' => 'Opwaarderen voltooid',
        'pending_heading' => 'Betaling wordt verwerkt',
        'paid' => 'Bedankt! Uw sleutel is opgewaardeerd met :amount tokens.',
        'next' => 'Uw tegoed is meteen beschikbaar — u kunt nu verder zoeken.',
        'details' => 'Bestelgegevens bekijken',
        'pending' => 'Uw betaling wordt nog verwerkt. Zodra deze bij ons binnenkomt, wordt uw sleutel automatisch opgewaardeerd.',
    ],
];
