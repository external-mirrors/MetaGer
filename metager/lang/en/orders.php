<?php

/**
 * Orders and their confirmations (/konto/bestellungen) —
 * App\Http\Controllers\OrderController.
 *
 * Taken from the keymanager's `/key/<uuid>/orders` area: `lookup.*` and the
 * line-item labels are the wording of its `order.json` (`orders.*`,
 * `details.*`, `summary.*`), the same page rendered here now. `show.heading`
 * and `show.lookup_hint` are new.
 */

return [
    'lookup' => [
        'heading' => 'Look up an order',
        'description' => 'Enter the payment ID of one of your orders to see its details.',
        'placeholder' => 'Payment ID',
        'submit' => 'Show order',
        'error' => [
            'invalid' => 'That is not a valid payment ID.',
            'not_found' => 'No order on your key matches that payment ID.',
        ],
    ],

    'show' => [
        'heading' => 'Order :reference',
        'breadcrumb' => 'Orders',
        'thanks' => 'Thank you for your purchase!',
        'pending' => 'Your tokens will be credited as soon as your payment reaches us. You will get a confirmation email once it has.',
        'lookup_hint' => 'You can open this overview again at any time by entering your payment ID (:reference).',
        'order_line' => 'Order :id of :date',
        'item' => 'MetaGer key: tokens',
        'count' => 'Quantity',
        'price' => 'Price',
        'vat' => 'VAT (:rate %)',
        'total' => 'Total',
        'exchange_rate' => 'Exchange rate',
        'download_confirmation' => 'Download order confirmation',
    ],
];
