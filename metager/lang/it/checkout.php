<?php

/**
 * Aufladen (/konto/aufladen) — App\Http\Controllers\ChargeController.
 *
 * Bezahlt wird bei suma-payments: hier stehen nur noch die Zustimmung
 * (`suma`, `consent`), die Schrittleiste und Kurzfassung (`page`) und die
 * Rückkehrseite (`returned`). Die Texte der eigenen Seiten für Bar, Wero,
 * micropayment und PayPal sind mit ihnen gegangen (suma-crm
 * docs/keymanager-migration-plan.md, Schritt 7).
 */
return [
    'page' => [
        'change' => 'Modifica quantità',
        'cancel' => 'Torna al conto',
        'steps' => [
            'aria_two' => 'Ricarica in due passaggi',
            'amount' => 'Quantità',
            'pay' => 'Pagare',
        ],
        'summary' => [
            'buying' => 'Stai acquistando',
            'after' => 'Credito successivo',
            'price' => 'Prezzo',
        ],
    ],
    'consent' => [
        'agb' => 'Proseguendo l\'acquisto, l\'utente accetta i nostri <a href=":agblink" target="_blank">Termini e Condizioni</a>.',
        'label' => 'Accetto espressamente l\'esecuzione del contratto prima della scadenza del periodo di revoca. Sono consapevole che il <a href=":revocation_link" target="_blank">diritto di recesso</a> scade con l\'inizio dell\'esecuzione del contratto. Le concediamo invece un <a href=":refundlink" target="_blank">diritto di recesso volontario di 30 giorni</a>.',
        'error' => 'Questo campo è obbligatorio',
    ],
    'suma' => [
        'label' => 'Bonifico, addebito diretto e altro',
        'heading' => 'Paga',
        'lede' => 'Nella pagina successiva scegli come pagare: :methods. Puoi pagare in forma anonima – nome ed e-mail sono facoltativi, tranne per l’addebito diretto.',
        'submit' => 'Continua al pagamento',
        'privacy' => 'Il pagamento passa per l’area pagamenti di SUMA-EV (payments.suma-ev.de). Bonifico, addebito diretto SEPA e contanti per posta li gestiamo direttamente noi – un fornitore di pagamenti esterno interviene solo con carta e Wero (VR Payment) e con PayPal.',
        'methods' => [
            'banktransfer' => 'Bonifico bancario',
            'sepa_directdebit' => 'Addebito diretto SEPA',
            'card' => 'Carta di credito/debito',
            'wero' => 'Wero',
            'paypal' => 'PayPal',
            'cash' => 'Contanti per posta',
        ],
        'error' => [
            'unreachable' => 'Qualcosa è andato storto durante la creazione dell\'ordine. Riprovare più tardi.',
        ],
    ],
    'returned' => [
        'heading' => 'Ricarica completata',
        'pending_heading' => 'Pagamento in elaborazione',
        'paid' => 'Grazie! La tua chiave è stata ricaricata con :amount token.',
        'next' => 'Il tuo credito è subito disponibile: puoi continuare a cercare ora.',
        'details' => 'Vedi i dettagli dell\'ordine',
        'pending' => 'Il pagamento è ancora in fase di elaborazione. Non appena lo riceveremo, la tua chiave verrà ricaricata automaticamente.',
    ],
];
