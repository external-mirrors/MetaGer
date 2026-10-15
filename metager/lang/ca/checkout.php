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
        'change' => 'Canvia la quantitat',
        'cancel' => 'Tornar al compte',
        'steps' => [
            'aria_two' => 'Recàrrega en dos passos',
            'amount' => 'Quantitat',
            'pay' => 'Pagar',
        ],
        'summary' => [
            'buying' => 'Comprau',
            'after' => 'Saldo després',
            'price' => 'Preu',
        ],
    ],
    'consent' => [
        'agb' => 'En continuar amb la compra, accepteu les nostres <a href=":agblink" target="_blank">condicions generals</a>.',
        'label' => 'Accepto expressament l\'execució del contracte abans que expiri el termini de desistiment. Entenc que el <a href=":revocation_link" target="_blank">dret de desistiment</a> s\'extingeix quan comença l\'execució del contracte. En canvi, us concedim un <a href=":refundlink" target="_blank">dret de devolució voluntari de 30 dies</a>.',
        'error' => 'Aquest camp és obligatori',
    ],
    'suma' => [
        'label' => 'Transferència, domiciliació i més',
        'heading' => 'Pagar',
        'lede' => 'A la pàgina següent trieu com pagar: :methods. Seguir sent anònim continua funcionant: el nom i el correu electrònic són voluntaris, excepte per a la domiciliació.',
        'submit' => 'Continua cap al pagament',
        'privacy' => 'El pagament passa per l\'àrea de pagaments de SUMA-EV (payments.suma-ev.de) i, segons el mètode, per VR Payment o PayPal.',
        'methods' => [
            'banktransfer' => 'Transferència bancària',
            'sepa_directdebit' => 'Domiciliació SEPA',
            'card' => 'Targeta de crèdit/dèbit',
            'wero' => 'Wero',
            'paypal' => 'PayPal',
            'cash' => 'Efectiu per correu',
        ],
        'error' => [
            'unreachable' => 'Alguna cosa ha anat malament en crear la vostra comanda. Torneu-ho a provar més tard.',
        ],
    ],
    'returned' => [
        'heading' => 'Recàrrega completada',
        'pending_heading' => 'El pagament s\'està processant',
        'paid' => 'Gràcies! La vostra clau s\'ha recarregat amb :amount tokens.',
        'next' => 'El vostre saldo ja està disponible: podeu continuar cercant de seguida.',
        'details' => 'Veure els detalls de la comanda',
        'pending' => 'El vostre pagament encara s\'està processant. Tan bon punt ens arribi, la vostra clau es recarregarà automàticament.',
    ],
];
