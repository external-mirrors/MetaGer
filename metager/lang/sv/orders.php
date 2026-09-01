<?php

return [
    'lookup' => [
        'heading' => 'Slå upp en beställning',
        'description' => 'Ange betalnings-ID för en av dina beställningar för att se detaljer om den.',
        'placeholder' => 'Betalnings-ID',
        'submit' => 'Visa beställning',
        'error' => [
            'invalid' => 'Detta är inte ett giltigt betalnings-ID.',
            'not_found' => 'Ingen beställning på din nyckel matchar det betalnings-ID:t.',
        ],
    ],

    'show' => [
        'heading' => 'Beställning :reference',
        'breadcrumb' => 'Beställningar',
        'thanks' => 'Tack för ditt köp!',
        'pending' => 'Dina tokens krediteras så snart din betalning har nått oss. Du får då ett bekräftelsemejl.',
        'lookup_hint' => 'Du kan öppna den här översikten igen när som helst genom att ange ditt betalnings-ID (:reference).',
        'order_line' => 'Beställning :id från :date',
        'item' => 'MetaGer-nyckel: tokens',
        'count' => 'Antal',
        'price' => 'Pris',
        'vat' => 'moms (:rate %)',
        'total' => 'Totalt belopp',
        'exchange_rate' => 'Växelkurs',
        'download_confirmation' => 'Ladda ner orderbekräftelse',
    ],
];
