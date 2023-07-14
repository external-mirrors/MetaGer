<?php
return [
    'title' => 'Twoje członkostwo w SUMA-EV',
    'description' => 'Dziękujemy za rozważenie członkostwa <a href=\'https://suma-ev.de/mitglieder/\' target=\'_blank\'></a> w naszym stowarzyszeniu wspierającym non-profit. Aby przetworzyć Twoją aplikację, potrzebujemy tylko kilku informacji, które możesz wypełnić tutaj.',
    'submit' => 'Darowizna',
    'success' => 'Bardzo dziękujemy za przesłanie aplikacji. Przetworzymy go tak szybko, jak to możliwe. Następnie otrzymasz od nas wiadomość z dalszymi informacjami na podany adres.',
    'startpage' => 'Powrót do strony głównej',
    'contact' => [
        'title' => '1. Dane kontaktowe',
        'name' => [
            'label' => 'Imię i nazwisko',
            'placeholder' => 'Max Mustermann / Muster GmbH',
        ],
        'email' => [
            'label' => 'Twój adres e-mail',
            'placeholder' => 'max@mustermann.de',
        ],
    ],
    'fee' => [
        'title' => '2. Opłata członkowska',
        'description' => 'Wybierz poniżej żądaną opłatę członkowską (miesięczną).',
        'amount' => [
            'custom' => [
                'label' => 'Kwota niestandardowa',
                'placeholder' => '5,00€',
            ],
        ],
    ],
    'payment' => [
        'interval' => [
            'title' => '3. Interwał płatności',
            'annual' => 'roczny',
            'six-monthly' => 'półroczny',
            'quarterly' => 'kwartalnik',
            'monthly' => 'miesięcznik',
        ],
        'method' => [
            'title' => '4. Metoda płatności',
            'directdebit' => [
                'label' => 'Polecenie zapłaty SEPA',
                'accountholder' => [
                    'label' => 'Właściciel konta (jeśli inny)',
                    'placeholder' => 'John Smith',
                ],
            ],
            'banktransfer' => 'Przelew bankowy',
        ],
    ],
];
