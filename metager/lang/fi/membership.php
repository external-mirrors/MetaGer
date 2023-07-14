<?php
return [
    'title' => 'Jäsenyytesi SUMA-EV:ssä',
    'description' => 'Kiitos, että harkitsit <a href=\'https://suma-ev.de/mitglieder/\' target=\'_blank\'></a> jäsenyyttä voittoa tavoittelemattomassa yhdistyksessämme. Hakemuksenne käsittelyä varten tarvitsemme vain muutamia tietoja, jotka voitte täyttää tässä.',
    'submit' => 'Lahjoita',
    'success' => 'Paljon kiitoksia, että lähetit meille hakemuksesi. Käsittelemme sen mahdollisimman pian. Sen jälkeen saat meiltä sähköpostia, jossa on lisätietoja annettuun osoitteeseen.',
    'startpage' => 'Takaisin etusivulle',
    'contact' => [
        'title' => '1. Yhteystietosi',
        'name' => [
            'label' => 'Nimesi',
            'placeholder' => 'Max Mustermann / Muster GmbH',
        ],
        'email' => [
            'label' => 'Sähköpostiosoitteesi',
            'placeholder' => 'max@mustermann.de',
        ],
    ],
    'fee' => [
        'title' => '2. Jäsenmaksusi',
        'description' => 'Valitse alla haluamasi jäsenmaksu (kuukausittain).',
        'amount' => [
            'custom' => [
                'label' => 'Mukautettu määrä',
                'placeholder' => '5,00€',
            ],
        ],
    ],
    'payment' => [
        'interval' => [
            'title' => '3. Maksuväli',
            'annual' => 'vuotuinen',
            'six-monthly' => 'puolivuosittain',
            'quarterly' => 'neljännesvuosittain',
            'monthly' => 'kuukausittain',
        ],
        'method' => [
            'title' => '4. Maksutapasi',
            'directdebit' => [
                'label' => 'SEPA-suoraveloitus',
                'accountholder' => [
                    'label' => 'Tilinomistaja (jos eri henkilö)',
                    'placeholder' => 'John Smith',
                ],
            ],
            'banktransfer' => 'Pankkisiirto',
        ],
    ],
];
