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
    ],
];
