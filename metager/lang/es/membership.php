<?php
return [
    'title'       => 'Su afiliación a SUMA-EV',
    'description' => 'Gracias por considerar la posibilidad de <a href=\'https://suma-ev.de/mitglieder/\' target=\'_blank\'>afiliarse</a> a nuestra asociación de apoyo sin ánimo de lucro. Para tramitar su solicitud, sólo necesitamos unos pocos datos, que puede rellenar aquí.',
    'submit'      => 'Donar',
    'success'     => 'Muchas gracias por enviarnos su solicitud. La tramitaremos lo antes posible. Le enviaremos un correo electrónico con más información a la dirección indicada.',
    'startpage'   => 'Volver a la página de inicio',
    'contact'     => [
        'title' => '1. Sus datos de contacto',
        'name'  => [
            'label'       => 'Su nombre',
            'placeholder' => 'Max Mustermann / Muster GmbH',
        ],
        'email' => [
            'label'       => 'Su dirección de correo electrónico',
            'placeholder' => 'max@mustermann.de',
        ],
    ],
    'fee'         => [
        'title'       => '2. Su cuota de afiliación',
        'description' => 'Seleccione a continuación la cuota de afiliación (mensual) que desee.',
        'amount'      => [
            'custom' => [
                'label'       => 'Cantidad deseada',
                'placeholder' => '5,00€',
            ],
        ],
    ],
    'payment'     => [
        'interval' => [
            'title'       => '3. Su garantía de pago',
            'annual'      => 'anual',
            'six-monthly' => 'semestral',
            'quarterly'   => 'trimestral',
            'monthly'     => 'mensualmente',
        ],
        'method'   => [
            'title'        => '4. Su forma de pago',
            'directdebit'  => [
                'label'         => 'Adeudo directo SEPA',
                'accountholder' => [
                    'label'       => 'Titular de la cuenta (si es diferente)',
                    'placeholder' => 'Max Mustermann',
                ],
            ],
            'banktransfer' => 'Transferencia bancaria',
        ],
    ],
];
