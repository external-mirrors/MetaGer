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
        'request_invoice' => 'Skapa faktura',
    ],

    'invoice' => [
        'heading' => 'Faktura',
        'breadcrumb' => 'Beställning :reference',
        'description' => 'Om du behöver en faktura, vänligen ange dina faktureringsuppgifter i formuläret nedan.',
        'ready' => 'Det finns redan en faktura för den här beställningen.',
        'download' => 'Ladda ner faktura',
        'submit' => 'Skapa faktura',
        'storage' => 'Vi är enligt lag skyldiga att spara en gång utfärdade fakturor <span class="bold">10 år</span> länge. Eftersom en faktura måste utfärdas till dig personligen, innehåller den nödvändigtvis personuppgifter (namn, adress).',
        'error' => [
            'invalid' => 'Kontrollera dina uppgifter — vissa obligatoriska fält saknas eller är för långa.',
        ],
        'field' => [
            'company' => 'Företagets namn (valfritt)',
            'first_name' => 'Förnamn',
            'last_name' => 'Efternamn',
            'address1' => 'Adress 1',
            'address2' => 'Adress 2 (valfritt)',
            'zip' => 'Postnummer',
            'city' => 'Stad',
            'state' => 'Stat (frivillig uppgift)',
        ],
    ],
];
