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
        'request_refund' => 'Anmod om refundering',
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

    'refund' => [
        'heading' => 'Refundering',
        'breadcrumb' => 'Ordre :reference',
        'unavailable' => 'Der er ikke længere nogen refunderbar saldo for denne ordre — enten er der allerede anmodet om en refundering, eller også understøtter den anvendte betalingsmetode ikke en refunderingsanmodning via denne formular.',
        'description' => 'Er du utilfreds med din nøgle? Det er vi meget kede af! Selvfølgelig refunderer vi fakturabeløbet i dette tilfælde. En refundering sker altid til den samme konto, der blev brugt til den oprindelige betaling. Vi modtager også gerne din kritik.',
        'partial_note' => 'En del af din købte saldo er allerede blevet brugt. Derfor kan vi kun refundere dig <span class="bold">:count</span> ud af <span class="bold">:total</span> søgninger.',
        'message' => [
            'label' => 'Din besked (valgfrit)',
        ],
        'submit' => 'Anmod om refundering af :amount €',
        'error' => [
            'not_allowed' => 'En refundering er ikke længere mulig for denne ordre.',
            'unreachable' => 'Fejl ved afsendelse af din besked. Prøv venligst igen senere.',
        ],
    ],
];
