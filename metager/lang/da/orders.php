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
        'documents' => 'Kvittering og faktura',
        'request_refund' => 'Anmod om refusion',
    ],


    'refund' => [
        'heading' => 'Tilbagebetaling',
        'breadcrumb' => 'Ordre :reference',
        'unavailable' => 'Der er ikke længere nogen refusion tilbage for denne ordre — enten er der allerede anmodet om refusion, eller også understøtter den anvendte betalingsmetode ikke en refusionsanmodning via denne formular.',
        'success' => 'Din anmodning er blevet sendt til os. Vi vil behandle den så hurtigt som muligt. Afhængigt af betalingsmetoden kan det tage et par dage, før en refusion er synlig i dit salg.',
        'description' => 'Er du utilfreds med din nøgle? Det er vi meget kede af at høre! Selvfølgelig refunderer vi fakturabeløbet i dette tilfælde. En refundering sker altid til den samme konto, som blev brugt til den oprindelige betaling. Vi er også glade for at modtage din kritik.',
        'partial_note' => 'Bemærk: En del af din købte kredit er allerede blevet brugt. Derfor kan vi kun refundere dig <span class="bold">:count</span> ud af <span class="bold">:total</span> søgninger.',
        'message' => [
            'label' => 'Din besked (valgfri)',
        ],
        'submit' => ':amount € Anmod om refusion',
        'error' => [
            'not_allowed' => 'En refusion er ikke længere mulig for denne ordre.',
            'unreachable' => 'Fejl ved afsendelse af din besked. Prøv venligst igen senere.',
        ],
    ],
];
