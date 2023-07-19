<?php
return [
    'title' => 'Ditt medlemskap i SUMA-EV',
    'submit' => 'Donera',
    'startpage' => 'Tillbaka till startsidan',
    'contact' => [
        'name' => [
            'label' => 'Ditt namn',
        ],
        'email' => [
            'label' => 'Din e-postadress',
        ],
    ],
    'fee' => [
        'title' => '2. Din medlemsavgift',
        'amount' => [
            'custom' => [
                'label' => 'Anpassat belopp',
            ],
        ],
    ],
    'payment' => [
        'interval' => [
            'title' => '3. Ditt betalningsintervall',
            'six-monthly' => 'halvårsvis',
            'monthly' => 'månadsvis',
        ],
        'method' => [
            'directdebit' => [
                'label' => 'SEPA direktdebitering',
                'accountholder' => [
                    'placeholder' => 'John Smith',
                ],
            ],
        ],
    ],
];
