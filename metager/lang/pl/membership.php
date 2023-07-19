<?php
return [
    'title' => 'Twoje członkostwo w SUMA-EV',
    'submit' => 'Darowizna',
    'startpage' => 'Powrót do strony głównej',
    'contact' => [
        'name' => [
            'label' => 'Imię i nazwisko',
        ],
        'email' => [
            'label' => 'Twój adres e-mail',
        ],
    ],
    'fee' => [
        'title' => '2. Opłata członkowska',
        'amount' => [
            'custom' => [
                'label' => 'Kwota niestandardowa',
            ],
        ],
    ],
    'payment' => [
        'interval' => [
            'title' => '3. Interwał płatności',
            'six-monthly' => 'półroczny',
            'monthly' => 'miesięcznik',
        ],
        'method' => [
            'directdebit' => [
                'label' => 'Polecenie zapłaty SEPA',
                'accountholder' => [
                    'placeholder' => 'John Smith',
                ],
            ],
        ],
    ],
];
