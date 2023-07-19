<?php
return [
    'payment' => [
        'method' => [
            'directdebit' => [
                'accountholder' => [
                    'label' => 'Titolare del conto (se diverso)',
                ],
                'label' => 'Addebito diretto SEPA',
            ],
            'banktransfer' => 'Bonifico bancario',
        ],
        'interval' => [
            'annual' => 'annuale',
            'quarterly' => 'trimestrale',
        ],
    ],
    'title' => 'La vostra adesione a SUMA-EV',
    'success' => 'Grazie mille per averci inviato la sua candidatura. La elaboreremo il prima possibile. In seguito riceverete una mail con ulteriori informazioni da parte nostra all\'indirizzo indicato.',
    'contact' => [
        'title' => '1. I vostri dati di contatto',
        'name' => [
            'placeholder' => 'Max Mustermann / Muster GmbH',
        ],
        'email' => [
            'placeholder' => 'max@mustermann.de',
        ],
    ],
    'fee' => [
        'description' => 'Selezionare di seguito la quota di adesione desiderata (mensile).',
        'amount' => [
            'custom' => [
                'placeholder' => '5,00€',
            ],
        ],
    ],
];
