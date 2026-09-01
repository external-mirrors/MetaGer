<?php

return [
    'lookup' => [
        'heading' => 'Slå en ordre op',
        'description' => 'Indtast betalings-ID\'et for en af dine ordrer for at se detaljer om den.',
        'placeholder' => 'Betalings-ID',
        'submit' => 'Vis ordre',
        'error' => [
            'invalid' => 'Det er ikke et gyldigt betalings-ID.',
            'not_found' => 'Ingen ordre på din nøgle matcher det betalings-ID.',
        ],
    ],

    'show' => [
        'heading' => 'Ordre :reference',
        'breadcrumb' => 'Bestillinger',
        'thanks' => 'Tak for dit køb!',
        'pending' => 'Dine tokens bliver krediteret, så snart din betaling er modtaget hos os. Du får en bekræftelsesmail, når det er sket.',
        'lookup_hint' => 'Du kan altid åbne denne oversigt igen ved at indtaste dit betalings-ID (:reference).',
        'order_line' => 'Ordre :id fra :date',
        'item' => 'MetaGer-nøgle: tokens',
        'count' => 'Antal',
        'price' => 'Pris',
        'vat' => 'moms (:rate %)',
        'total' => 'Samlet beløb',
        'exchange_rate' => 'Valutakurs',
        'download_confirmation' => 'Download ordrebekræftelse',
    ],
];
