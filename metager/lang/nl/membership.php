<?php
return [
    'fee' => [
        'amount' => [
            'custom' => [
                'label' => 'Aangepast bedrag',
                'placeholder' => '5,00€',
            ],
        ],
        'title' => '2. Je lidmaatschapsgeld',
        'description' => 'Selecteer hieronder het gewenste lidmaatschapsbedrag (maandelijks).',
    ],
    'title' => 'Jouw lidmaatschap bij SUMA-EV',
    'description' => 'Hartelijk dank voor het overwegen van <a href=\'https://suma-ev.de/mitglieder/\' target=\'_blank\'>lidmaatschap</a> in onze ondersteunende vereniging zonder winstoogmerk. Om uw aanvraag te verwerken hebben we slechts een paar gegevens nodig, die u hier kunt invullen.',
    'submit' => 'Doneer',
    'success' => 'Hartelijk dank voor je sollicitatie. We zullen deze zo snel mogelijk verwerken. Daarna ontvang je van ons een e-mail met verdere informatie op het opgegeven adres.',
    'startpage' => 'Terug naar de startpagina',
    'contact' => [
        'title' => '1. Uw contactgegevens',
        'name' => [
            'label' => 'Uw naam',
            'placeholder' => 'Max Mustermann / Muster GmbH',
        ],
        'email' => [
            'label' => 'Uw e-mailadres',
            'placeholder' => 'max@mustermann.de',
        ],
    ],
    'payment' => [
        'interval' => [
            'title' => '3. Uw betalingsinterval',
            'annual' => 'jaarlijks',
            'six-monthly' => 'halfjaarlijks',
            'quarterly' => 'driemaandelijks',
            'monthly' => 'maandelijks',
        ],
        'method' => [
            'title' => '4. Je betaalmethode',
            'directdebit' => [
                'label' => 'SEPA-incasso',
                'accountholder' => [
                    'label' => 'Rekeninghouder (indien anders)',
                    'placeholder' => 'John Smith',
                ],
            ],
            'banktransfer' => 'Overschrijving',
        ],
    ],
];
