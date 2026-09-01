<?php

/**
 * Aufladen (/konto/aufladen) — App\Http\Controllers\ChargeController.
 *
 * `cash`, `consent` und micropayment.prepay sind wortgleich aus dem Keymanager
 * übernommen (dessen checkout.json unter payments.cash/payments.prepay/
 * micropayment, dessen order.json unter agb und revocation, je Sprache) —
 * dieselbe Kasse, jetzt hier gerendert. `page`, `manual`,
 * micropayment.lastschrift/directbanking (dort nur Logos, keine Texte),
 * `returned` und vrpayment.label/submit/error.failed sind neu; vrpayment.privacy
 * ist wortgleich aus dem Keymanager übernommen wie cash/consent/micropayment.
 */
return [
    'page' => [
        'change' => 'Cambiar cantidad',
        'methods' => [
            'heading' => 'Seleccione el método de pago',
            'more' => 'Más métodos de pago',
            'back' => 'Elegir otro método de pago',
            'cash_note' => 'Anónimo',
        ],
        'cancel' => 'Volver a la cuenta',
    ],

    'cash' => [
        'label' => 'Efectivo',
        'description' => 'También puede cobrar su llave en efectivo. Para ello, sólo tiene que enviarnos por correo el siguiente número de pedido junto con la cantidad de dinero deseada. Tenga en cuenta que el número de pedido debe ser legible para que podamos procesarlo.',
        'note' => 'Tenga en cuenta lo siguiente:',
        'no_large_values' => 'Por su propia seguridad, no nos envíe más de 100 euros por correo. No asumimos ninguna responsabilidad por la ruta de transporte. Usted es responsable de que la carta nos llegue.',
        'no_coins' => 'Sólo aceptamos billetes. No envíe monedas.',
        'accepted_currencies' => 'Sólo aceptamos las siguientes monedas: EUR, USD, CAD, GBP.',
        'currency_translation' => 'Siempre cobramos los importes en EUR. Si nos envía otra moneda, el importe enviado se convertirá al tipo de cambio diario',
        'no_refund' => 'Debido a la legislación aplicable en materia de blanqueo de dinero, lamentablemente no es posible realizar un reembolso o una devolución. No obstante, una vez que hayamos contabilizado el cargo, puede introducir el ID de pago enviado en "Pedidos" para obtener un resumen del pedido y/o solicitar una factura.',
        'generate' => 'Generar ID de pago',
        'error' => [
            'unreachable' => 'Algo ha ido mal al crear su pedido. Vuelva a intentarlo más tarde.',
        ],
        'order' => [
            'heading' => 'Su ID de pago',
            'copy' => 'Copia de la identificación de pago',
            'address_heading' => 'Envíe la carta a la siguiente dirección y anote la identificación del pago para sus propios archivos',
            'address' => 'SUMA-EV
Postfach 51 01 43
30631 Hannover
Alemania',
            'expiration' => 'El identificador de pago es válido hasta :date. Después de esta fecha ya no podrá utilizarse para una recarga.',
            'unique' => 'Utilice el identificador de pago sólo para una recarga. Recibirá uno nuevo cada vez que visite esta página.',
        ],
    ],

    'consent' => [
        'agb' => 'Al continuar con su compra, usted acepta nuestros <a href=":agblink" target="_blank">Términos y Condiciones</a>.',
        'label' => 'Acepto expresamente la ejecución del contrato antes de que expire el plazo de revocación. Entiendo que el <a href=":revocation_link" target="_blank">derecho de revocación</a> expira al inicio de la ejecución del contrato. En su lugar, le concedemos un derecho de devolución voluntario <a href=":refundlink" target="_blank">de 30 días</a>.',
        'error' => 'Este campo es obligatorio',
    ],

    'manual' => [
        'label' => 'Manual (dev)',
        'description' => 'Omita un pago real. Solo disponible en un entorno de desarrollo.',
        'submit' => 'Completar el pago',
    ],

    'micropayment' => [
        'prepay' => [
            'label' => 'Transferencia bancaria',
            'email' => [
                'label' => 'Correo electrónico',
                'description' => 'A esta dirección se le enviará una única vez información sobre nuestros datos bancarios y una notificación cuando se complete el pago.',
            ],
        ],
        'lastschrift' => ['label' => 'Domiciliación bancaria'],
        'directbanking' => ['label' => 'Transferencia bancaria instantánea'],
        'submit' => 'Realizar el pago',
        'privacy' => 'Al hacer clic en "Realizar pago" será redirigido a nuestro proveedor de servicios de pago <a href="https://micropayment.de" target="_blank">MicroPayment</a> para completar la compra. Más información sobre <a href=":link" target="_blank">privacidad en :link_text</a>.',
    ],

    'vrpayment' => [
        'label' => 'Wero',
        'submit' => 'Realizar el pago',
        'privacy' => 'Al hacer clic en "Realizar pago" será redirigido a nuestro proveedor de servicios de pago <a href="https://www.vr-payment.de" target="_blank">VR Payment</a> para completar la compra. Más información sobre <a href=":link" target="_blank">privacidad en VR Payment</a>.',
        'error' => [
            'failed' => 'VR Payment ha rechazado este pago. Vuelva a intentarlo o elija otro método de pago.',
            'onion' => 'Wero no está disponible a través de nuestra dirección onion: el proveedor de pago no puede devolverle aquí después. Elija otro método de pago.',
        ],
    ],

    'paypal' => [
        'heading' => 'Realizar el pago',
        'submit' => 'Realizar el pago',
        'loading' => 'El método de pago está cargado',
        'cancel' => 'El proceso de pago se ha cancelado. Si el pago se ha realizado antes de la cancelación, el pedido se tramitará en cuanto el procesador de pagos confirme el pago. En caso contrario, inténtelo de nuevo.',
        'privacy' => 'Las formas de pago de este grupo no suelen requerir una cuenta PayPal, pero se procesan en ella. Más información sobre la privacidad de <a href="https://www.paypal.com/us/legalhub/privacy-full" target="_blank">en PayPal</a>.',
        'noscript' => 'Este método de pago requiere JavaScript. Elija otro método de pago o active JavaScript.',
        'funding' => [
            'paypal' => 'PayPal',
            'card' => 'Tarjeta de crédito / débito',
            'p24' => 'Przelewy24',
            'bancontact' => 'Bancontact',
            'blik' => 'BLIK',
            'eps' => 'EPS',
            'mybank' => 'MyBank',
        ],
        'error' => [
            'not_available' => 'Lo sentimos, el método de pago seleccionado no está disponible en su región.',
            'generic' => 'El proceso de pago se ha cancelado debido a un error.  Si el pago se ha realizado antes de la cancelación, el pedido se procesará en cuanto el procesador de pagos confirme el pago. En caso contrario, inténtelo de nuevo.',
        ],
        'card' => [
            'label' => 'Tarjeta de crédito / débito',
            'name' => 'Nombre del titular de la tarjeta (opcional)',
            'number' => 'Número de tarjeta',
            'expiration' => 'Válido hasta el',
            'cvv' => 'CVV',
            'error' => [
                '9500' => 'Tarjeta de crédito rechazada por fraudulenta',
                '5100' => 'La tarjeta de crédito ha sido rechazada por la entidad de crédito',
                '00N7' => 'CVV incorrecto. Por favor, compruebe la entrada',
                '5400' => 'Tarjeta de crédito caducada',
                '5180' => 'Chequeo fallido de Luhn',
                '5120' => 'Tarjeta de crédito rechazada por fondos insuficientes.',
                '9520' => 'Tarjeta de crédito rechazada por pérdida o robo',
                '0500' => 'Tarjeta de crédito rechazada por la entidad de crédito',
                '1330' => 'Tarjeta de crédito inválida. Por favor, compruebe su entrada',
                '3ds' => 'Error de autenticación 3D',
                'generic' => 'Tarjeta de crédito rechazada por la entidad de crédito',
            ],
        ],
    ],
    'returned' => [
        'heading' => 'Recarga completada',
        'paid' => '¡Gracias! Su clave se ha recargado con :amount tokens.',
        'next' => 'Su saldo está disponible de inmediato: puede seguir buscando ahora.',
        'pending' => 'Su pago aún se está procesando. En cuanto lo recibamos, su clave se recargará automáticamente.',
    ],
];
