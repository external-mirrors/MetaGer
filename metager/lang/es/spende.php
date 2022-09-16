<?php
return [
    'headline'      => [
        1   => 'Su donación',
        2   => 'Con su donación, apoya el mantenimiento y desarrollo del motor de búsqueda independiente metager.de y la labor de la asociación patrocinadora sin fines de lucro SUMA-EV. 
<a href=":aboutlink" rel="noopener" target=_blank>Obtenga más información</a> y <a href=":beitrittlink" target="_blank" rel="noopener"> conviértase en miembro. </a>.',
        3   => '¿Qué cantidad desea donar?',
        4   => '¿Con qué frecuencia desea donar?',
        5   => '¿Cómo le gustaría donar?',
        6   => 'Datos de la cuenta bancaria',
    ],
    'wunschbetrag'  => [
        'placeholder' => 'Importe en €',
        'label'       => 'Cantidad deseada',
    ],
    'frequency'     => [
        'once'        => 'Única vez',
        'monthly'     => 'Mensual',
        'quarterly'   => 'Trimestral',
        'six-monthly' => 'Semestral',
        'annual'      => 'Anual',
    ],
    'head'          => [
        'lastschrift' => 'Domiciliación bancaria',
    ],
    'ueberweisung'  => 'Transferencia bancaria',
    'bankinfo'      => [
        1   => 'Para donar a nuestra asociación patrocinadora SUMA-EV, solo necesita hacer una transferencia a la siguiente cuenta:',
        2   => [
            1   => 'IBAN: DE64 4306 0967 4075 0332 01',
            2   => 'BIC: GENODEM1GLS',
            3   => 'Banco: GLS Gemeinschaftsbank, Bochum',
            4   => '(NDC: 4075 0332 01, Código: 43060967)',
        ],
        3   => 'Si desea un recibo de donación, indíquenos su dirección completa. Para las donaciones de hasta 300 euros, el extracto bancario es suficiente para la deducción fiscal.',
    ],
    'lastschrift'   => [
        1         => 'Donaciones mediante domiciliación bancaria electrónica:',
        2         => 'Ingrese los detalles de su cuenta aquí. Luego, debitaremos su cuenta según sus indicaciones. Los campos que están marcados con "*" son obligatorios.',
        4         => 'Su correo electrónico:',
        5         => 'Su número de teléfono, para que podamos verificar su donación con una llamada telefónica si es necesario:',
        6         => 'Su IBAN:',
        7         => 'Su BIC: (Solo es necesario para transacciones desde otros países de la UE):',
        8         => [
            'message' => [
                'placeholder' => 'Más información',
                'label'       => 'Aquí puede enviarnos un mensaje adicional si lo desea:',
            ],
        ],
        10        => 'Sus datos se nos transmiten a través de una conexión cifrada y no pueden ser leídos por terceros. SUMA-EV utiliza sus datos exclusivamente para la liquidación de donaciones; Nunca compartiremos sus datos. Las donaciones al SUMA-EV son deducibles de impuestos, ya que es una asociación sin fines de lucro y está reconocida como tal por la Oficina de Impuestos del Norte de Hanover e inscrita en el registro de asociaciones en el Tribunal de Distrito de Hanover bajo VR200033.',
        'info'    => 'Si desea hacer una donación por domiciliación bancaria, introduzca la información sobre el importe de la donación y los datos de su cuenta en el siguiente formulario. A continuación, cargaremos convenientemente la cuenta especificada en las próximas 2 semanas.',
        'info2'   => 'Salvo que usted indique lo contrario en el apartado de Regularidad, una domiciliación bancaria siempre tendrá lugar una sola vez.',
        '3f'      => [
            'placeholder' => 'Nombre',
        ],
        '3l'      => [
            'placeholder' => 'Apellido',
        ],
        '3c'      => [
            'placeholder' => 'Nombre de la empresa',
        ],
        'private' => 'Particulares:',
        'company' => 'La empresa:',
    ],
    'paypal'        => [
        1   => 'Con un clic en donar, será redirigido a PayPal.',
        0   => 'Paypal / tarjeta de crédito',
    ],
    'submit'        => 'Donar',
    'member'        => [
        1   => '¿O prefiere hacerse socio?',
        2   => 'No cuesta más y tiene muchas ventajas:',
        3   => 'Uso de MetaGer sin publicidad ',
        4   => 'Promoción del buscador MetaGer',
        5   => 'Cuota de socio deducible de impuestos',
        6   => 'Derechos de cogestión en la asociación',
        7   => 'Formulario de aplicación',
    ],
    'drucken'       => 'Imprimir',
    'danke'         => [
        'title'      => '¡¡Muchas gracias!! SUMA-EV ha recibido su notificación de donación para MetaGer.',
        'nachricht'  => 'Si ha proporcionado datos de contacto, pronto recibirá un mensaje personal.',
        'kontrolle'  => 'Hemos recibido el siguiente mensaje:',
        'message'    => 'Su mensaje',
        'schluessel' => 'Como pequeño agradecimiento, ofrecemos a nuestros donantes una clave para realizar búsquedas sin publicidad. <br> Se puede introducir haciendo clic en el símbolo de la llave junto a la barra de búsqueda. <br> Tu llave es</br></br> ',
    ],
    'telefonnummer' => 'Teléfono',
    'iban'          => 'IBAN:',
    'bic'           => 'BIC:',
    'betrag'        => 'Cantidad',
    'error'         => [
        'iban'      => 'El IBAN ingresado no parece ser correcto. No se envió el mensaje.',
        'bic'       => 'El IBAN introducido no pertenece a ningún país del área SEPA. Necesitamos su BIC para una domiciliación bancaria.',
        'amount'    => 'El monto de la donación ingresado no es válido. Corrija su entrada y vuelva a intentarlo.',
        'frequency' => 'La frecuencia ingresada para su donación no es válida.',
        'name'      => 'Parece que no han introducido un nombre. Por favor, inténtalo de nuevo.',
        'robot'     => 'La entrada no era correcta',
    ],
];
