<?php

/**
 * Orders and their confirmations (/konto/bestellungen) —
 * App\Http\Controllers\OrderController.
 *
 * Taken from the keymanager's `/key/<uuid>/orders` area: `lookup.*` and the
 * line-item labels are the wording of its `order.json` (`orders.*`,
 * `details.*`, `summary.*`), the same page rendered here now. `show.heading`,
 * `show.lookup_hint` and `show.request_refund` are new; `refund.*` is the
 * wording of its `order.json` (`refund.*`).
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
        'documents' => 'Receipt and invoice',
        'request_refund' => 'Request refund',
    ],


    'refund' => [
        'heading' => 'Refund',
        'breadcrumb' => 'Order :reference',
        'unavailable' => 'There is no refundable balance left for this order — either a refund has already been requested, or the payment method used does not support a refund request through this form.',
        'success' => 'Your request has been successfully sent to us. We will process it as soon as possible. Depending on the payment method, it may take a few days before a refund is visible in your sales.',
        'description' => 'Are you dissatisfied with your key? We are very sorry to hear that! Of course, we will refund the invoice amount in this case. A refund is always made to the same account that was used for the original payment. We are also happy to receive your feedback.',
        'partial_note' => 'Part of your purchased credit has already been used. We can therefore only refund you <span class="bold">:count</span> of <span class="bold">:total</span> searches.',
        'message' => [
            'label' => 'Your message (optional)',
        ],
        'submit' => ':amount € Request refund',
        'error' => [
            'not_allowed' => 'A refund is no longer possible for this order.',
            'unreachable' => 'Error sending your message. Please try again later.',
        ],
    ],
];
