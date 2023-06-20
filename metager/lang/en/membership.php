<?php
return [
    'title'       => 'Your membership in SUMA-EV',
    'description' => 'Thank you for considering <a href=\'https://suma-ev.de/mitglieder/\' target=\'_blank\'>membership</a> in our non-profit supporting association. In order to process your application, we only need a few pieces of information, which you can fill in here.',
    'submit'      => 'Donate',
    'success'     => 'Thank you very much for sending us your application. We will process it as soon as possible. Afterwards you will receive a mail with further information from us to the given address.',
    'startpage'   => 'Back to home page',
    'contact'     => [
        'title' => '1. Your contact details',
        'name'  => [
            'label'       => 'Your name',
            'placeholder' => 'Max Mustermann / Muster GmbH',
        ],
        'email' => [
            'label'       => 'Your email address',
            'placeholder' => 'max@mustermann.de',
        ],
    ],
    'fee'         => [
        'title'       => '2. Your membership fee',
        'description' => 'Please select your desired membership fee (monthly) below.',
        'amount'      => [
            'custom' => [
                'label'       => 'Custom amount',
                'placeholder' => '5,00€',
            ],
        ],
    ],
    'payment'     => [
        'interval' => [
            'title'       => '3. Your payment interval',
            'annual'      => 'annual',
            'six-monthly' => 'semiannual',
            'quarterly'   => 'quarterly',
            'monthly'     => 'monthly',
        ],
        'method'   => [
            'title'        => '4. Your payment method',
            'directdebit'  => [
                'label'         => 'SEPA direct debit',
                'accountholder' => [
                    'label'       => 'Account holder (if different)',
                    'placeholder' => 'John Smith',
                ],
            ],
            'banktransfer' => 'Bank transfer',
        ],
    ],
];
