<?php

return [
    'login' => [
        'hint' => 'Please log in to get access to your account.',
        'email' => 'Email address',
        'code' => 'Login code',
        'email_sent' => 'If this account is already registered, we have sent you a login code by email. Please enter it to log in.',
        'submit' => 'Submit',
        'restart' => 'New login'
    ],
    'overview' => [
        "hint" => 'Here you will find an overview of your orders and information on using the API. Please make sure that the following invoice data is up-to-date and correct',
        'invoice-data' => [
            "heading" => "Invoice data",
            "email" => "Email address",
            "company" => "Company",
            "full_name" => "Name",
            "first_name" => "First Name",
            "last_name" => "Last Name",
            "street" => "Street and house number",
            "postal_code" => "Zip code",
            "city" => "City",
            "save" => "Save",
            "update" => "Update invoice data"
        ],
        "abo" => [
            "heading" => "Access to current data",
            "hint" => "Here you can set up access to the MetaGer search query logs for the coming months. Access will be automatically renewed at the selected payment interval.",
            "interval" => [
                "label" => "Payment interval",
                "setting_values" => [
                    "never" => "Never",
                    "monthly" => "monthly",
                    "annual" => "yearly",
                    "quarterly" => "quarterly",
                    "six-monthly" => "semi-annually"
                ]
            ],
            "last_invoice" => "Last Invoice",
            "next_invoice" => "Next Invoice",
            "never" => "Never",
            "create" => "Set up",
            "update" => "Update"
        ]
    ],
    "create_abo" => [
        "heading" => "Set up subscription",
        "interval" => "Payment interval",
        "conditions" => "Terms and conditions",
        "amount" => "To be made with each payment",
        "conditions_hint" => "We automatically issue an invoice for each payment interval. Your access includes access to the MetaGer logs for all months included in the billing period (including the current one). The invoice for the following period will be issued one month before the start, if possible, so that seamless use is possible.",
        "nda" => "NDA (non-disclosure agreement)",
        "conditions_nda" => "The data provided may contain personal data, even if unsorted. For this reason, the data may not be made publicly accessible by you in any form. This includes in particular the raw data itself, but also models learned from it in the field of machine learning. However, public access to the answers of a model is possible. Please read the following NDA (non-disclosure agreement) carefully and save it for your own records before you agree to it by continuing.",
        "accept" => "I agree to the NDA (non-disclosure agreement) and the terms of payment",
        "cancel" => "Cancel current subscription"
    ],
    "orders" => [
        "heading" => "Orders",
        "status" => [
            "4" => "Completed",
            "5" => "Canceled",
            "6" => "Repaid",
            "3" => "Partially paid",
            "2" => "Delivered",
            "1" => "Draft",
            "-1" => "Overdue",
            "-2" => "Payment outstanding",
            "-3" => "Viewed"
        ],
        "thead" => [
            "from" => "Access from",
            "to" => "Access until",
            "price" => "Invoice amount",
            "status" => "Invoice status",
            "invoice" => "Invoice"
        ]
    ],
    "api_keys" => [
        "heading" => "API key",
        "hint" => "Um die API abfragen zu können musst Du dich authentifizieren. Hier kannst du dir für deine Geräte API Schlüssel erstellen. <b>Hinweis</b>: Neu erstellte Schlüssel sind nur einmalig auslesbar. Bitte speichern Sie sich diese nach Erstellung ab.",
        "thead" => [
            "name" => "Device",
            "key" => "key",
            "created_at" => "Created",
            "accessed_at" => "Last Access",
            "actions" => "Actions"
        ],
        "new" => [
            "heading" => "Create New Key",
            "name" => "Device name",
            "placeholder_name" => "Laptop",
            "submit" => "Create"
        ],
        "copy" => "Copy",
        "delete" => "Delete"
    ],
    "api-docs" => [
        "hint" => "Below you will find our API documentation, which you can use to retrieve logs from our server.",
        "link" => "API Documentation",
    ]
];