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
        'request_invoice' => 'Creare la fattura',
    ],

    'invoice' => [
        'heading' => 'Fattura',
        'breadcrumb' => 'Ordine :reference',
        'description' => 'Se avete bisogno di una fattura, inserite i vostri dati di fatturazione nel modulo sottostante.',
        'ready' => 'Per questo ordine esiste già una fattura.',
        'download' => 'Scarica la fattura',
        'submit' => 'Creare la fattura',
        'storage' => 'Siamo obbligati per legge a conservare le fatture emesse <span class="bold">per 10 anni</span>. Poiché la fattura deve essere emessa personalmente, essa contiene necessariamente dati personali (nome, indirizzo).',
        'error' => [
            'invalid' => 'Controllate i vostri dati — alcuni campi obbligatori mancano o sono troppo lunghi.',
        ],
        'field' => [
            'company' => 'Nome della società (facoltativo)',
            'first_name' => 'Nome',
            'last_name' => 'Cognome',
            'address1' => 'Indirizzo 1',
            'address2' => 'Indirizzo 2 (opzionale)',
            'zip' => 'Codice postale',
            'city' => 'Città',
            'state' => 'Stato (opzionale)',
        ],
    ],
];
