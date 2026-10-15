<?php

/**
 * Aufladen (/konto/aufladen) — App\Http\Controllers\ChargeController.
 *
 * Bezahlt wird bei suma-payments: hier stehen nur noch die Zustimmung
 * (`suma`, `consent`), die Schrittleiste und Kurzfassung (`page`) und die
 * Rückkehrseite (`returned`). Die Texte der eigenen Seiten für Bar, Wero,
 * micropayment und PayPal sind mit ihnen gegangen (suma-crm
 * docs/keymanager-migration-plan.md, Schritt 7).
 */
return [
    'page' => [
        'change' => 'Cambiar cantidad',
        'cancel' => 'Volver a la cuenta',
        'steps' => [
            'aria_two' => 'Recarga en dos pasos',
            'amount' => 'Cantidad',
            'pay' => 'Pagar',
        ],
        'summary' => [
            'buying' => 'Usted compra',
            'after' => 'Saldo después',
            'price' => 'Precio',
        ],
    ],
    'consent' => [
        'agb' => 'Al continuar con su compra, usted acepta nuestros <a href=":agblink" target="_blank">Términos y Condiciones</a>.',
        'label' => 'Acepto expresamente la ejecución del contrato antes de que expire el plazo de revocación. Entiendo que el <a href=":revocation_link" target="_blank">derecho de revocación</a> expira al inicio de la ejecución del contrato. En su lugar, le concedemos un derecho de devolución voluntario <a href=":refundlink" target="_blank">de 30 días</a>.',
        'error' => 'Este campo es obligatorio',
    ],
    'suma' => [
        'label' => 'Transferencia, domiciliación y más',
        'heading' => 'Pagar',
        'lede' => 'En la página siguiente elige cómo pagar: :methods. Seguir siendo anónimo sigue funcionando: el nombre y el correo electrónico son voluntarios, salvo para la domiciliación.',
        'submit' => 'Continuar al pago',
        'privacy' => 'El pago se realiza a través del área de pagos de SUMA-EV (payments.suma-ev.de) y, según el método, a través de VR Payment o PayPal.',
        'methods' => [
            'banktransfer' => 'Transferencia bancaria',
            'sepa_directdebit' => 'Domiciliación SEPA',
            'card' => 'Tarjeta de crédito/débito',
            'wero' => 'Wero',
            'paypal' => 'PayPal',
            'cash' => 'Efectivo por correo',
        ],
        'error' => [
            'unreachable' => 'Algo ha ido mal al crear su pedido. Vuelva a intentarlo más tarde.',
        ],
    ],
    'returned' => [
        'heading' => 'Recarga completada',
        'pending_heading' => 'El pago se está procesando',
        'paid' => '¡Gracias! Su clave se ha recargado con :amount tokens.',
        'next' => 'Su saldo está disponible de inmediato: puede seguir buscando ahora.',
        'details' => 'Ver detalles del pedido',
        'pending' => 'Su pago aún se está procesando. En cuanto lo recibamos, su clave se recargará automáticamente.',
    ],
];
