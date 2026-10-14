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
        'documents' => 'Ricevuta e fattura',
        'request_refund' => 'Richiedi un rimborso',
    ],


    'refund' => [
        'heading' => 'Rimborso',
        'breadcrumb' => 'Ordine :reference',
        'unavailable' => 'Non è rimasto alcun saldo rimborsabile per questo ordine — o è già stato richiesto un rimborso, oppure il metodo di pagamento utilizzato non supporta una richiesta di rimborso tramite questo modulo.',
        'success' => 'La vostra richiesta ci è stata inviata con successo. La elaboreremo il prima possibile. A seconda del metodo di pagamento, potrebbero essere necessari alcuni giorni prima che il rimborso sia visibile nelle vendite.',
        'description' => 'Non siete soddisfatti della vostra chiave? Ci dispiace molto sentirlo! Naturalmente, in questo caso rimborseremo l\'importo della fattura. Il rimborso viene sempre effettuato sullo stesso conto utilizzato per il pagamento originale. Siamo inoltre lieti di ricevere le vostre critiche.',
        'partial_note' => 'Nota: parte del credito acquistato è già stato utilizzato. Pertanto, possiamo rimborsare solo <span class="bold">:count</span> su <span class="bold">:total</span> ricerche.',
        'message' => [
            'label' => 'Messaggio (facoltativo)',
        ],
        'submit' => ':amount € Richiesta di rimborso',
        'error' => [
            'not_allowed' => 'Per questo ordine non è più possibile un rimborso.',
            'unreachable' => 'Errore nell\'invio del messaggio. Riprovare più tardi.',
        ],
    ],
];
