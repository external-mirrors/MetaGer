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
        'change' => 'Change amount',
        'methods' => [
            'heading' => 'Choose payment method',
            'more' => 'More payment methods',
            'back' => 'Choose a different payment method',
            'cash_note' => 'Anonymous',
        ],
        'cancel' => 'Back to account',
    ],

    'cash' => [
        'label' => 'Cash',
        'description' => 'You can also charge your key for cash. To do so, simply send us the following order number by mail together with the desired amount of money. Please note that the order number must be legible in order to be processed by us.',
        'note' => 'Please note the following:',
        'no_large_values' => 'For your own safety, do not send us more than 100€ by mail. We do not assume any liability for the transport route. You are responsible for ensuring that the letter reaches us.',
        'no_coins' => 'We accept only banknotes. Do not send coins!',
        'accepted_currencies' => 'We accept only the following currencies: EUR, USD, CAD, GBP.',
        'currency_translation' => 'We always charge amounts in EUR. If you send us another currency, the amount sent will be converted at the daily exchange rate',
        'no_refund' => 'Due to applicable money laundering laws, a refund or return is unfortunately not possible. However, once the charge has been posted by us, you can enter the sent payment ID under "Orders" to get an order overview and/or request an invoice.',
        'generate' => 'Generate payment ID',
        'error' => [
            'unreachable' => 'Something went wrong while creating your order. Please try again later.',
        ],
        'order' => [
            'heading' => 'Your payment ID',
            'copy' => 'Copy payment ID',
            'address_heading' => 'Send the letter to the following address and make a note of the payment ID for your own records',
            'address' => 'SUMA-EV
Postfach 51 01 43
30631 Hannover
Germany',
            'expiration' => 'The payment ID is valid until :date. After this date it can no longer be used for a recharge.',
            'unique' => 'Use the payment ID only for a single recharge. You will receive a new one every time you visit this page!',
        ],
    ],

    'consent' => [
        'agb' => 'By continuing your purchase, you agree to our <a href=":agblink" target="_blank">Terms and Conditions</a>.',
        'label' => 'I expressly agree to the execution of the contract before the expiry of the revocation period. I understand that the <a href=":revocation_link" target="_blank">right of revocation</a> expires upon commencement of the execution of the contract. Instead, we grant you a voluntary <a href=":refundlink" target="_blank">30-day right of return</a>.',
        'error' => 'This field is required',
    ],

    'manual' => [
        'label' => 'Manual (dev)',
        'description' => 'Skip an actual payment. Only available in a development environment.',
        'submit' => 'Complete payment',
    ],

    'micropayment' => [
        'prepay' => [
            'label' => 'Bank transfer',
            'email' => [
                'label' => 'E-mail address',
                'description' => 'To this address you will be sent one-time information about our bank details and a notification when the payment is completed.',
            ],
        ],
        'lastschrift' => ['label' => 'Direct debit'],
        'directbanking' => ['label' => 'Instant bank transfer'],
        'submit' => 'Make payment',
        'privacy' => 'By clicking "Make payment" you will be redirected to our payment service provider <a href="https://micropayment.de" target="_blank">MicroPayment</a> to complete the purchase. More about <a href=":link" target="_blank">privacy at :link_text</a>.',
    ],

    'vrpayment' => [
        'label' => 'Wero',
        'submit' => 'Make payment',
        'privacy' => 'By clicking "Make payment" you will be redirected to our payment service provider <a href="https://www.vr-payment.de" target="_blank">VR Payment</a> to complete the purchase. More about <a href=":link" target="_blank">privacy at VR Payment</a>.',
        'error' => [
            'failed' => 'VR Payment declined this payment. Please try again or choose a different payment method.',
            'onion' => 'Wero is not available over our onion address — the payment provider cannot send you back here afterwards. Please choose a different payment method.',
        ],
    ],

    'paypal' => [
        'heading' => 'Make payment',
        'submit' => 'Make payment',
        'loading' => 'Payment method is loaded',
        'cancel' => 'The payment process was canceled. If your payment went through before cancelling, your order will be processed as soon as the payment is confirmed by the payment processor. Otherwise please try again.',
        'privacy' => 'Payment methods in this group usually do not require a PayPal account, but are processed there. More about <a href="https://www.paypal.com/us/legalhub/privacy-full" target="_blank">privacy at PayPal</a>.',
        'noscript' => 'This payment method requires JavaScript. Please choose a different payment method or enable JavaScript.',
        'funding' => [
            'paypal' => 'PayPal',
            'card' => 'Credit / debit card',
            'p24' => 'Przelewy24',
            'bancontact' => 'Bancontact',
            'blik' => 'BLIK',
            'eps' => 'EPS',
            'mybank' => 'MyBank',
        ],
        'error' => [
            'not_available' => 'Sorry, the selected payment method is not available in your region.',
            'generic' => 'The payment process was canceled due to an error.  If your payment went through before cancelling, your order will be processed as soon as the payment is confirmed by the payment processor. Otherwise please try again.',
        ],
        'card' => [
            'label' => 'Credit / debit card',
            'name' => 'Cardholder Name (optional)',
            'number' => 'Card number',
            'expiration' => 'Valid until',
            'cvv' => 'CVV',
            'error' => [
                '9500' => 'Credit card rejected as fraudulent',
                '5100' => 'The credit card was declined by the credit institution',
                '00N7' => 'Wrong CVV. Please check input',
                '5400' => 'Credit card expired',
                '5180' => 'Luhn check failed',
                '5120' => 'Credit card declined due to insufficient funds.',
                '9520' => 'Credit card rejected as lost/stolen',
                '0500' => 'Credit card declined by credit institution',
                '1330' => 'Credit card invalid. Please check your entry',
                '3ds' => '3DS Authentication failed',
                'generic' => 'Credit card declined by credit institution',
            ],
        ],
    ],
    'returned' => [
        'heading' => 'Charge complete',
        'paid' => 'Thank you! Your key has been topped up by :amount tokens.',
        'next' => 'Your balance is ready right away — you can keep searching now.',
        'details' => 'View order details',
        'pending' => 'Your payment is still being processed. As soon as it reaches us, your key will be topped up automatically.',
    ],
];
