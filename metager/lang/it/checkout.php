<?php

/**
 * Aufladen (/konto/aufladen) — App\Http\Controllers\ChargeController.
 *
 * `cash`, `consent` und micropayment.prepay sind wortgleich aus dem Keymanager
 * übernommen (dessen checkout.json unter payments.cash/payments.prepay/
 * micropayment, dessen order.json unter agb und revocation, je Sprache) —
 * dieselbe Kasse, jetzt hier gerendert. `page`, `manual`,
 * micropayment.lastschrift/directbanking (dort nur Logos, keine Texte),
 * `returned` und vrpayment.label/submit/error.failed sind neu; vrpayment.privacy
 * ist wortgleich aus dem Keymanager übernommen wie cash/consent/micropayment.
 */
return [
    'page' => [
        'change' => 'Modifica quantità',
        'methods' => [
            'heading' => 'Scegliere il metodo di pagamento',
            'more' => 'Altri metodi di pagamento',
            'back' => 'Scegli un altro metodo di pagamento',
        ],
        'cancel' => 'Torna al conto',
    ],

    'cash' => [
        'label' => 'Contanti',
        'description' => 'È possibile anche caricare la chiave in contanti. A tal fine, è sufficiente inviarci per posta il seguente numero d\'ordine e l\'importo desiderato. Si prega di notare che il numero d\'ordine deve essere leggibile per poter essere elaborato da noi.',
        'note' => 'Si prega di notare quanto segue:',
        'no_large_values' => 'Per la vostra sicurezza, non inviateci più di 100€ per posta. Non ci assumiamo alcuna responsabilità per il percorso di trasporto. È vostra responsabilità assicurarvi che la lettera ci arrivi.',
        'no_coins' => 'Accettiamo solo banconote. Non inviate monete!',
        'accepted_currencies' => 'Accettiamo solo le seguenti valute: EUR, USD, CAD, GBP.',
        'currency_translation' => 'Addebitiamo sempre gli importi in euro. Se ci inviate un\'altra valuta, l\'importo inviato verrà convertito al tasso di cambio giornaliero.',
        'no_refund' => 'A causa delle leggi sul riciclaggio di denaro, purtroppo non è possibile effettuare un rimborso o una restituzione. Tuttavia, una volta che l\'addebito è stato da noi effettuato, è possibile inserire l\'ID del pagamento inviato alla voce "Ordini" per ottenere una panoramica dell\'ordine e/o richiedere una fattura.',
        'generate' => 'Generare l\'ID di pagamento',
        'error' => [
            'unreachable' => 'Qualcosa è andato storto durante la creazione dell\'ordine. Riprovare più tardi.',
        ],
        'order' => [
            'heading' => 'Il vostro ID di pagamento',
            'copy' => 'Copia dell\'ID di pagamento',
            'address_heading' => 'Inviate la lettera al seguente indirizzo e annotate l\'ID del pagamento per i vostri archivi',
            'address' => 'SUMA-EV
Postfach 51 01 43
30631 Hannover
Germania',
            'expiration' => 'L\'ID di pagamento è valido fino a :date. Dopo questa data non potrà più essere utilizzato per una ricarica.',
            'unique' => 'Utilizzate l\'ID di pagamento solo per una singola ricarica. Ne riceverete uno nuovo ogni volta che visiterete questa pagina!',
        ],
    ],

    'consent' => [
        'agb' => 'Proseguendo l\'acquisto, l\'utente accetta i nostri <a href=":agblink" target="_blank">Termini e Condizioni</a>.',
        'label' => 'Accetto espressamente l\'esecuzione del contratto prima della scadenza del periodo di revoca. Sono consapevole che il <a href=":revocation_link" target="_blank">diritto di recesso</a> scade con l\'inizio dell\'esecuzione del contratto. Le concediamo invece un <a href=":refundlink" target="_blank">diritto di recesso volontario di 30 giorni</a>.',
        'error' => 'Questo campo è obbligatorio',
    ],

    'manual' => [
        'label' => 'Manuale (dev)',
        'description' => 'Salta un pagamento reale. Disponibile solo in un ambiente di sviluppo.',
        'submit' => 'Completa il pagamento',
    ],

    'micropayment' => [
        'label' => 'Micropayment',
        'prepay' => [
            'label' => 'Bonifico bancario',
            'email' => [
                'label' => 'Indirizzo e-mail',
                'description' => 'A questo indirizzo verranno inviate una tantum informazioni sulle nostre coordinate bancarie e una notifica quando il pagamento sarà completato.',
            ],
        ],
        'lastschrift' => ['label' => 'Addebito diretto SEPA'],
        'directbanking' => ['label' => 'Bonifico bancario istantaneo'],
        'submit' => 'Effettuare il pagamento',
        'privacy' => 'Facendo clic su "Effettuare il pagamento" si verrà reindirizzati al nostro fornitore di servizi di pagamento <a href="https://micropayment.de" target="_blank">MicroPayment</a> per completare l\'acquisto. Per saperne di più sulla privacy <a href=":link" target="_blank"> :link_text</a> .',
    ],

    'vrpayment' => [
        'label' => 'Pagamento VR',
        'submit' => 'Effettuare il pagamento',
        'privacy' => 'Facendo clic su "Effettuare il pagamento" si verrà reindirizzati al nostro fornitore di servizi di pagamento <a href="https://www.vr-payment.de" target="_blank">VR Payment</a> per completare l\'acquisto. Maggiori informazioni su <a href=":link" target="_blank">privacy su VR Payment</a>.',
        'error' => [
            'failed' => 'VR Payment ha rifiutato questo pagamento. Riprova o scegli un altro metodo di pagamento.',
        ],
    ],

    'returned' => [
        'heading' => 'Ricarica completata',
        'paid' => 'Grazie! La tua chiave è stata ricaricata con :amount token.',
        'pending' => 'Il pagamento è ancora in fase di elaborazione. Non appena lo riceveremo, la tua chiave verrà ricaricata automaticamente.',
    ],
];
