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
        'request_invoice' => 'Opret faktura',
    ],

    'invoice' => [
        'heading' => 'Faktura',
        'breadcrumb' => 'Ordre :reference',
        'description' => 'Hvis du har brug for en faktura, bedes du indtaste dine faktureringsoplysninger i formularen nedenfor.',
        'ready' => 'Der findes allerede en faktura for denne ordre.',
        'download' => 'Download faktura',
        'submit' => 'Opret faktura',
        'storage' => 'Vi er lovmæssigt forpligtet til at opbevare en gang udstedte fakturaer <span class="bold">10 år</span> længe. Da en faktura skal udstedes til dig personligt, indeholder den nødvendigvis personlige data (navn, adresse).',
        'error' => [
            'invalid' => 'Kontroller venligst dine oplysninger — nogle obligatoriske felter mangler eller er for lange.',
        ],
        'field' => [
            'company' => 'Firmanavn (valgfrit)',
            'first_name' => 'Fornavn',
            'last_name' => 'Efternavn',
            'address1' => 'Adresse 1',
            'address2' => 'Adresse 2 (valgfrit)',
            'zip' => 'Postnummer',
            'city' => 'By',
            'state' => 'Stat (valgfrit)',
        ],
    ],
];
