<?php
return [
    'title' => 'Jäsenyytesi SUMA-EV:ssä',
    'submit' => 'Lahjoita',
    'startpage' => 'Takaisin etusivulle',
    'contact' => [
        'name' => [
            'label' => 'Nimesi',
        ],
        'email' => [
            'label' => 'Sähköpostiosoitteesi',
        ],
    ],
    'fee' => [
        'title' => '2. Jäsenmaksusi',
        'amount' => [
            'custom' => [
                'label' => 'Mukautettu määrä',
            ],
        ],
    ],
    'payment' => [
        'interval' => [
            'title' => '3. Maksuväli',
            'six-monthly' => 'puolivuosittain',
            'monthly' => 'kuukausittain',
        ],
        'method' => [
            'directdebit' => [
                'label' => 'SEPA-suoraveloitus',
                'accountholder' => [
                    'placeholder' => 'John Smith',
                ],
            ],
        ],
    ],
];
