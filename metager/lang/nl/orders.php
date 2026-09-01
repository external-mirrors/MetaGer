<?php

return [
    'lookup' => [
        'heading' => 'Een bestelling opzoeken',
        'description' => 'Voer de betalings-ID van een van uw bestellingen in om de details te bekijken.',
        'placeholder' => 'Betalings-ID',
        'submit' => 'Bestelling tonen',
        'error' => [
            'invalid' => 'Dit is geen geldige betalings-ID.',
            'not_found' => 'Geen bestelling op uw sleutel komt overeen met die betalings-ID.',
        ],
    ],

    'show' => [
        'heading' => 'Bestelling :reference',
        'breadcrumb' => 'Bestellingen',
        'thanks' => 'Bedankt voor uw aankoop!',
        'pending' => 'Uw tokens worden bijgeschreven zodra uw betaling bij ons is ontvangen. U ontvangt dan een bevestigingsmail.',
        'lookup_hint' => 'U kunt dit overzicht op elk moment opnieuw openen door uw betalings-ID (:reference) in te voeren.',
        'order_line' => 'Bestelling :id van :date',
        'item' => 'MetaGer-sleutel: tokens',
        'count' => 'Hoeveelheid',
        'price' => 'Prijs',
        'vat' => 'btw (:rate %)',
        'total' => 'Totaalbedrag',
        'exchange_rate' => 'Wisselkoers',
        'download_confirmation' => 'Orderbevestiging downloaden',
    ],
];
