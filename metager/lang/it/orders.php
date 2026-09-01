<?php

return [
    'lookup' => [
        'heading' => 'Cerca un ordine',
        'description' => 'Inserisci l\'ID di pagamento di uno dei tuoi ordini per visualizzarne i dettagli.',
        'placeholder' => 'ID di pagamento',
        'submit' => 'Mostra ordine',
        'error' => [
            'invalid' => 'Questo non è un ID di pagamento valido.',
            'not_found' => 'Nessun ordine sulla tua chiave corrisponde a questo ID di pagamento.',
        ],
    ],

    'show' => [
        'heading' => 'Ordine :reference',
        'breadcrumb' => 'Ordini',
        'thanks' => 'Grazie per l\'acquisto!',
        'pending' => 'I tuoi gettoni saranno accreditati non appena il pagamento ci sarà pervenuto. Riceverai un\'e-mail di conferma non appena ciò avverrà.',
        'lookup_hint' => 'Puoi riaprire questo riepilogo in qualsiasi momento inserendo il tuo ID di pagamento (:reference).',
        'order_line' => 'Ordine :id del :date',
        'item' => 'Chiave MetaGer: gettoni',
        'count' => 'Quantità',
        'price' => 'Prezzo',
        'vat' => 'IVA (:rate %)',
        'total' => 'Importo totale',
        'exchange_rate' => 'Tasso di cambio',
        'download_confirmation' => 'Scarica la conferma d\'ordine',
    ],
];
