<?php
return [
    'title' => 'Ditt medlemskap i SUMA-EV',
    'description' => 'Tack för att du överväger <a href=\'https://suma-ev.de/mitglieder/\' target=\'_blank\'>medlemskap</a> i vår ideella stödförening. För att kunna behandla din ansökan behöver vi bara några få uppgifter, som du kan fylla i här.',
    'submit' => 'Donera',
    'success' => 'Tack så mycket för att du skickat in din ansökan till oss. Vi kommer att behandla den så snart som möjligt. Därefter kommer du att få ett mail med ytterligare information från oss till den angivna adressen.',
    'startpage' => 'Tillbaka till startsidan',
    'contact' => [
        'title' => '1. Dina kontaktuppgifter',
        'name' => [
            'label' => 'Ditt namn',
            'placeholder' => 'Max Mustermann / Muster GmbH',
        ],
        'email' => [
            'label' => 'Din e-postadress',
            'placeholder' => 'max@mustermann.de',
        ],
    ],
    'fee' => [
        'title' => '2. Din medlemsavgift',
        'description' => 'Vänligen välj önskad medlemsavgift (månadsavgift) nedan.',
        'amount' => [
            'custom' => [
                'label' => 'Anpassat belopp',
                'placeholder' => '5,00€',
            ],
        ],
    ],
    'payment' => [
        'interval' => [
            'title' => '3. Ditt betalningsintervall',
            'annual' => 'årlig',
            'six-monthly' => 'halvårsvis',
            'quarterly' => 'kvartalsvis',
            'monthly' => 'månadsvis',
        ],
        'method' => [
            'title' => '4. Din betalningsmetod',
            'directdebit' => [
                'label' => 'SEPA direktdebitering',
                'accountholder' => [
                    'label' => 'Kontoinnehavare (om annan)',
                    'placeholder' => 'John Smith',
                ],
            ],
            'banktransfer' => 'Banköverföring',
        ],
    ],
];
