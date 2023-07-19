<?php
return [
    'title' => 'Dit medlemskab af SUMA-EV',
    'description' => 'Tak fordi du overvejer <a href=\'https://suma-ev.de/mitglieder/\' target=\'_blank\'>medlemskab</a> i vores non-profit støtteforening. For at kunne behandle din ansøgning har vi kun brug for nogle få oplysninger, som du kan udfylde her.',
    'startpage' => 'Tilbage til startsiden',
    'contact' => [
        'name' => [
            'label' => 'Dit navn',
        ],
        'email' => [
            'label' => 'Din e-mailadresse',
        ],
    ],
    'fee' => [
        'title' => '2. Dit medlemskontingent',
        'amount' => [
            'custom' => [
                'label' => 'Brugerdefineret beløb',
            ],
        ],
    ],
    'payment' => [
        'interval' => [
            'title' => '3. Dit betalingsinterval',
            'six-monthly' => 'halvårlig',
            'monthly' => 'månedligt',
        ],
        'method' => [
            'directdebit' => [
                'label' => 'SEPA direkte debitering',
                'accountholder' => [
                    'placeholder' => 'John Smith',
                ],
            ],
        ],
    ],
];
