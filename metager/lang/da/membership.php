<?php
return [
    'title' => 'Dit medlemskab af SUMA-EV',
    'submit' => 'Donér',
    'description' => 'Tak fordi du overvejer <a href=\'https://suma-ev.de/mitglieder/\' target=\'_blank\'>medlemskab</a> i vores non-profit støtteforening. For at kunne behandle din ansøgning har vi kun brug for nogle få oplysninger, som du kan udfylde her.',
    'success' => 'Mange tak, fordi du har sendt os din ansøgning. Vi vil behandle den så hurtigt som muligt. Bagefter vil du modtage en mail med yderligere information fra os til den angivne adresse.',
    'startpage' => 'Tilbage til startsiden',
    'contact' => [
        'title' => '1. Dine kontaktoplysninger',
        'name' => [
            'label' => 'Dit navn',
            'placeholder' => 'Max Mustermann / Muster GmbH',
        ],
        'email' => [
            'label' => 'Din e-mailadresse',
            'placeholder' => 'max@mustermann.de',
        ],
    ],
    'fee' => [
        'title' => '2. Dit medlemskontingent',
        'description' => 'Vælg venligst dit ønskede medlemsgebyr (månedligt) nedenfor.',
        'amount' => [
            'custom' => [
                'label' => 'Brugerdefineret beløb',
                'placeholder' => '5,00€',
            ],
        ],
    ],
    'payment' => [
        'interval' => [
            'title' => '3. Dit betalingsinterval',
            'annual' => 'årlig',
            'six-monthly' => 'halvårlig',
            'quarterly' => 'kvartalsvis',
            'monthly' => 'månedligt',
        ],
        'method' => [
            'title' => '4. Din betalingsmetode',
            'directdebit' => [
                'label' => 'SEPA direkte debitering',
                'accountholder' => [
                    'label' => 'Kontohaver (hvis forskellig)',
                    'placeholder' => 'John Smith',
                ],
            ],
            'banktransfer' => 'Bankoverførsel',
        ],
    ],
];
