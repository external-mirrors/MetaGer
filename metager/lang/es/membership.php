<?php
return [
    'title' => 'Su afiliación a SUMA-EV',
    'back' => 'Volver a la página de inicio',
    'non-de' => 'Lamentablemente, por el momento sólo podemos aceptar solicitudes de admisión de países de habla alemana. Le invitamos a que nos apoye con una donación a <a href=":donationlink"></a> .',
    'key' => [
        'description' => 'Para utilizar MetaGer, se utiliza la siguiente clave, que nosotros recargamos. Si ya estaba conectado, se utilizó su clave existente.',
        'later' => 'La primera recarga tiene lugar después de que se haya tramitado su solicitud',
        'now' => 'Ya está cargada y puede utilizarse inmediatamente.',
    ],
    'application' => [
        'description' => 'Gracias por considerar la posibilidad de afiliarse a <a href="https://suma-ev.de/en/mitglieder/" target="_blank"></a> en nuestra asociación sin ánimo de lucro. Para tramitar su solicitud, sólo necesitamos unos pocos datos, que puede rellenar aquí.',
        'cancel' => [
            'application' => 'Borrar solicitud de afiliación',
            'update' => 'Descartar cambios',
        ],
        'update_hint' => 'Los cambios solicitados para su afiliación serán revisados/aceptados en breve. Si está satisfecho con el estado mostrado puede abandonar esta página. De lo contrario, puede realizar más cambios o eliminar su solicitud de cambio con el botón de abajo.',
        'payment_block' => 'Intentaremos autorizar el pago de su próxima cuota de afiliación para validar su método de pago, pero el pago sólo se ejecutará si vence en las próximas dos semanas y se anulará en caso contrario.',
    ],
    'data' => [
        'description' => 'Hemos registrado los siguientes datos para su solicitud:',
        'payment' => [
            'interval' => [
                'six-monthly' => "Semestral",
                'annual' => "anualmente",
                'quarterly' => "trimestral",
                'monthly' => "mensualmente",
            ],
        ],
        'payment_methods' => [
            'directdebit' => "Domiciliación bancaria",
            'card' => "Tarjeta de crédito",
            'banktransfer' => "Transferencia bancaria",
            'paypal' => "PayPal",
        ],
        'company' => "Nombre de la empresa",
        'payment_method' => "Forma de pago",
        'amount' => "Cuota de afiliación",
        'email' => 'Correo electrónico',
        'name' => 'Nombre',
    ],
    'success' => 'Muchas gracias por enviar su solicitud de afiliación. La tramitaremos lo antes posible. Le enviaremos un correo electrónico con más información a la dirección indicada.',
];
