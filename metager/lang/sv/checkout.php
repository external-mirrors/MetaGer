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
        'change' => 'Ändra mängd',
        'cancel' => 'Tillbaka till kontot',
        'steps' => [
            'aria_two' => 'Laddning i två steg',
            'amount' => 'Mängd',
            'pay' => 'Betala',
        ],
        'summary' => [
            'buying' => 'Du köper',
            'after' => 'Saldo efteråt',
            'price' => 'Pris',
        ],
    ],
    'consent' => [
        'agb' => 'Genom att fortsätta ditt köp godkänner du våra <a href=":agblink" target="_blank">Villkor och bestämmelser</a>.',
        'label' => 'Jag samtycker uttryckligen till att avtalet genomförs innan ångerfristen löpt ut. Jag förstår att <a href=":revocation_link" target="_blank">rätten till återkallelse</a> löper ut när genomförandet av avtalet påbörjas. Istället ger vi dig en frivillig <a href=":refundlink" target="_blank">30-dagars returrätt</a>.',
        'error' => 'Detta fält är obligatoriskt',
    ],
    'suma' => [
        'label' => 'Banköverföring, autogiro och mer',
        'heading' => 'Betala',
        'lede' => 'På nästa sida väljer du hur du betalar: :methods. Du kan fortfarande vara anonym – namn och e-post är frivilliga, utom vid autogiro.',
        'submit' => 'Vidare till betalning',
        'privacy' => 'Betalningen går via SUMA-EV:s betalningsområde (payments.suma-ev.de) och, beroende på betalsätt, via VR Payment eller PayPal.',
        'methods' => [
            'banktransfer' => 'Banköverföring',
            'sepa_directdebit' => 'SEPA-autogiro',
            'card' => 'Kredit-/betalkort',
            'wero' => 'Wero',
            'paypal' => 'PayPal',
            'cash' => 'Kontanter per post',
        ],
        'error' => [
            'unreachable' => 'Något gick fel när vi skapade din beställning. Vänligen försök igen senare.',
        ],
    ],
    'returned' => [
        'heading' => 'Uppladdning slutförd',
        'pending_heading' => 'Betalningen behandlas',
        'paid' => 'Tack! Din nyckel har laddats med :amount tokens.',
        'next' => 'Ditt saldo är tillgängligt direkt – du kan fortsätta söka nu.',
        'details' => 'Visa beställningsdetaljer',
        'pending' => 'Din betalning behandlas fortfarande. Så snart den når oss laddas din nyckel automatiskt.',
    ],
];
