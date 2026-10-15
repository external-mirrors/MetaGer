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
        'change' => 'Change amount',
        'cancel' => 'Back to account',
        'steps' => [
            'aria_two' => 'Topping up in two steps',
            'amount' => 'Amount',
            'pay' => 'Pay',
        ],
        'summary' => [
            'buying' => 'You are buying',
            'after' => 'Balance afterwards',
            'price' => 'Price',
        ],
    ],
    'consent' => [
        'agb' => 'By continuing your purchase, you agree to our <a href=":agblink" target="_blank">Terms and Conditions</a>.',
        'label' => 'I expressly agree to the execution of the contract before the expiry of the revocation period. I understand that the <a href=":revocation_link" target="_blank">right of revocation</a> expires upon commencement of the execution of the contract. Instead, we grant you a voluntary <a href=":refundlink" target="_blank">30-day right of return</a>.',
        'error' => 'This field is required',
    ],
    'suma' => [
        'label' => 'Bank transfer, direct debit and more',
        'heading' => 'Pay',
        'lede' => 'On the next page you choose how to pay: :methods. Staying anonymous still works – name and email are voluntary, except for direct debit.',
        'submit' => 'Continue to payment',
        'privacy' => 'The payment runs through SUMA-EV\'s payment area (payments.suma-ev.de) and, depending on the method, through VR Payment or PayPal.',
        'methods' => [
            'banktransfer' => 'Bank transfer',
            'sepa_directdebit' => 'SEPA direct debit',
            'card' => 'Credit/debit card',
            'wero' => 'Wero',
            'paypal' => 'PayPal',
            'cash' => 'Cash by post',
        ],
        'error' => [
            'unreachable' => 'Something went wrong while creating your order. Please try again later.',
        ],
    ],
    'returned' => [
        'heading' => 'Charge complete',
        'pending_heading' => 'Payment is being processed',
        'paid' => 'Thank you! Your key has been topped up by :amount tokens.',
        'next' => 'Your balance is ready right away — you can keep searching now.',
        'details' => 'View order details',
        'pending' => 'Your payment is still being processed. As soon as it reaches us, your key will be topped up automatically.',
    ],
];
