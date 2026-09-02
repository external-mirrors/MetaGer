<?php

/**
 * Orders and their confirmations (/konto/bestellungen) —
 * App\Http\Controllers\OrderController.
 *
 * Taken from the keymanager's `/key/<uuid>/orders` area: `lookup.*` and the
 * line-item labels are the wording of its `order.json` (`orders.*`,
 * `details.*`, `summary.*`), the same page rendered here now. `show.heading`,
 * `show.lookup_hint`, `show.request_invoice` and `show.request_refund` are
 * new; `invoice.*` is the wording of its `invoice.json` (`form.*`),
 * `refund.*` of its `order.json` (`refund.*`).
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
        'request_invoice' => 'Create invoice',
        'request_refund' => 'Request refund',
    ],

    'invoice' => [
        'heading' => 'Invoice',
        'breadcrumb' => 'Order :reference',
        'description' => 'If you need an invoice, please enter your billing information in the form below.',
        'ready' => 'An invoice already exists for this order.',
        'download' => 'Download invoice',
        'submit' => 'Create invoice',
        'storage' => 'We are legally obliged to keep once issued invoices <span class="bold">10 years</span> long. Since an invoice must be issued to you personally, it necessarily contains personal data (name, address).',
        'error' => [
            'invalid' => 'Please check your details — some required fields are missing or too long.',
        ],
        'field' => [
            'company' => 'Company name (optional)',
            'first_name' => 'First name',
            'last_name' => 'Last name',
            'address1' => 'Address 1',
            'address2' => 'Address 2 (optional)',
            'zip' => 'Postal code',
            'city' => 'City',
            'state' => 'State (optional)',
        ],
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
