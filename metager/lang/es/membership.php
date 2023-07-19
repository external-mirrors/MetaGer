<?php
return [
    'title' => 'Su afiliación a SUMA-EV',
    'submit' => 'Donar',
    'startpage' => 'Volver a la página de inicio',
    'contact' => [
        'name' => [
            'label' => 'Su nombre',
        ],
        'email' => [
            'label' => 'Su dirección de correo electrónico',
        ],
    ],
    'fee' => [
        'title' => '2. Su cuota de afiliación',
        'amount' => [
            'custom' => [
                'label' => 'Cantidad deseada',
            ],
        ],
    ],
    'payment' => [
        'interval' => [
            'title' => '3. Su garantía de pago',
            'six-monthly' => 'semestral',
            'monthly' => 'mensualmente',
        ],
        'method' => [
            'directdebit' => [
                'label' => 'Adeudo directo SEPA',
                'accountholder' => [
                    'placeholder' => 'Max Mustermann',
                ],
            ],
        ],
    ],
];
