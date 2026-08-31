<?php

/**
 * Aufladen (/konto/aufladen) — App\Http\Controllers\ChargeController.
 *
 * `cash`, `consent` und micropayment.prepay sind wortgleich aus dem Keymanager
 * übernommen (dessen checkout.json unter payments.cash/payments.prepay/
 * micropayment, dessen order.json unter agb und revocation, je Sprache) —
 * dieselbe Kasse, jetzt hier gerendert. `page`, `manual`,
 * micropayment.lastschrift/directbanking (dort nur Logos, keine Texte) und
 * `returned` sind neu.
 */
return [
    'page' => [
        'change' => 'Cambiar cantidad',
        'methods' => [
            'heading' => 'Seleccione el método de pago',
            'more' => 'Más métodos de pago',
            'back' => 'Elegir otro método de pago',
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
        'label' => 'Micropayment',
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

    'returned' => [
        'heading' => 'Recarga completada',
        'paid' => '¡Gracias! Su clave se ha recargado con :amount tokens.',
        'pending' => 'Su pago aún se está procesando. En cuanto lo recibamos, su clave se recargará automáticamente.',
    ],
];
