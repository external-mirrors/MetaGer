<?php
return [
    'title' => 'Your membership in SUMA-EV',
    'non-de' => 'Unfortunately, we can currently only accept applications for admission for German-speaking countries. You are very welcome to support us with a <a href=":donationlink">donation</a>.',
    'success' => 'Thank you very much for submitting your application for membership. We will process it as quickly as possible. You will then receive an e-mail with further information from us at the address provided.',
    'back' => 'Back to the startpage',
    'application' => [
        'cancel' => [
            'application' => 'Delete Membership Application',
            'update' => 'Discard Changes'
        ],
        'update_hint' => 'The requested changes for your membership will be reviewed/accepted soon. If you are satisfied with the shown state you can leave this page. Otherwise you can make more changes or delete your change request with the button below.',
        'description' => 'Thank you for considering <a href="https://suma-ev.de/en/mitglieder/" target="_blank">membership</a> in our non-profit association. In order to process your application, we only need a few pieces of information, which you can fill in here.',
        'update' => 'Below you\'ll see the information we\'ve stored for your membership. You can change this information by clicking "Edit". Changing your contact details isn\'t possible here. If these have changed, please send us an <a href=":contact_link" target="_blank">email</a> with your updated information.',
        "payment_block" => 'We will try to authorize a payment for your next membership fee to validate your payment method but the payment will only be executed if it is due within the next two weeks and voided otherwise.'
    ],
    'data' => [
        'description' => 'We have recorded the following data for your application:',
        'name' => 'Name',
        'email' => 'Email address',
        "company" => "Company name",
        "amount" => "Membership fee",
        "payment_method" => "Payment method",
        "payment_methods" => [
            "banktransfer" => "Bank transfer",
            "directdebit" => "Direct debit",
            "paypal" => "PayPal",
            "card" => "Credit card"
        ],
        "payment" => [
            "interval" => [
                "monthly" => "monthly",
                "quarterly" => "quarterly",
                "six-monthly" => "Half-yearly",
                "annual" => "annually"
            ]
        ]
    ],
    'key' => [
        'description' => 'To use MetaGer, the following key is used and topped up by us. If you were already logged in, your existing key was used.',
        'later' => 'The first top-up takes place after your application has been processed',
        'now' => 'It is already charged and can be used immediately.',
    ],
];