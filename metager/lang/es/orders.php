<?php

return [
    'lookup' => [
        'heading' => 'Buscar un pedido',
        'description' => 'Introduzca el ID de pago de uno de sus pedidos para ver sus detalles.',
        'placeholder' => 'ID de pago',
        'submit' => 'Mostrar pedido',
        'error' => [
            'invalid' => 'El ID de pago no es válido.',
            'not_found' => 'Ningún pedido de su clave coincide con ese ID de pago.',
        ],
    ],

    'show' => [
        'heading' => 'Pedido :reference',
        'breadcrumb' => 'Pedidos',
        'thanks' => 'Gracias por su compra.',
        'pending' => 'Sus fichas se abonarán en cuanto recibamos su pago. Recibirá un correo electrónico de confirmación en cuanto esto ocurra.',
        'lookup_hint' => 'Puede volver a abrir este resumen en cualquier momento introduciendo su ID de pago (:reference).',
        'order_line' => 'Pedido :id de :date',
        'item' => 'Llave MetaGer: fichas',
        'count' => 'Cantidad',
        'price' => 'Precio',
        'vat' => 'IVA (:rate %)',
        'total' => 'Importe total',
        'exchange_rate' => 'Tipo de cambio',
        'download_confirmation' => 'Descargar confirmación de pedido',
        'request_invoice' => 'Crear factura',
        'request_refund' => 'Solicitar reembolso',
    ],

    'invoice' => [
        'heading' => 'Factura',
        'breadcrumb' => 'Pedido :reference',
        'description' => 'Si necesita una factura, introduzca sus datos de facturación en el siguiente formulario.',
        'ready' => 'Ya existe una factura para este pedido.',
        'download' => 'Descargar factura',
        'submit' => 'Crear factura',
        'storage' => 'Estamos legalmente obligados a conservar las facturas emitidas <span class="bold">durante 10 años</span>. Dado que una factura debe expedirse personalmente a usted, contiene necesariamente datos personales (nombre, dirección).',
        'error' => [
            'invalid' => 'Compruebe sus datos — faltan algunos campos obligatorios o son demasiado largos.',
        ],
        'field' => [
            'company' => 'Nombre de la empresa (opcional)',
            'first_name' => 'Nombre',
            'last_name' => 'Apellido',
            'address1' => 'Dirección 1',
            'address2' => 'Dirección 2 (opcional)',
            'zip' => 'Código postal',
            'city' => 'Ciudad',
            'state' => 'Estado (opcional)',
        ],
    ],

    'refund' => [
        'heading' => 'Reembolso',
        'breadcrumb' => 'Pedido :reference',
        'unavailable' => 'Ya no queda saldo reembolsable para este pedido — o bien ya se ha solicitado un reembolso, o bien el método de pago utilizado no admite una solicitud de reembolso a través de este formulario.',
        'success' => 'Su solicitud nos ha sido enviada correctamente. La tramitaremos lo antes posible. Dependiendo del método de pago, pueden pasar unos días antes de que el reembolso sea visible en tus ventas.',
        'description' => '¿No está satisfecho con su llave? Lo sentimos mucho. Por supuesto, en este caso le devolveremos el importe de la factura. El reembolso se realiza siempre en la misma cuenta que se utilizó para el pago original. También nos alegramos de recibir sus críticas.',
        'partial_note' => 'Nota: Parte del crédito adquirido ya se ha utilizado. Por lo tanto, solo podemos reembolsarte <span class="bold">:count</span> de <span class="bold">:total</span> búsquedas.',
        'message' => [
            'label' => 'Su mensaje (opcional)',
        ],
        'submit' => ':amount € Solicitar reembolso',
        'error' => [
            'not_allowed' => 'Ya no es posible un reembolso para este pedido.',
            'unreachable' => 'Error al enviar su mensaje. Vuelva a intentarlo más tarde.',
        ],
    ],
];
