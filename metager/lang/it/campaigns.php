<?php

return [
    'heading' => 'Campagne di buoni',
    'description' => 'Regala chiavi dal tuo saldo di token, ad esempio ad amici o colleghi. Le chiavi regalate detraggono i token dalla tua chiave solo quando vengono effettivamente usate: i regali non utilizzati non ti costano nulla.',
    'unreachable' => 'Al momento non è stato possibile caricare le tue campagne di buoni. Riprova più tardi.',
    'copy_link' => 'Copia link',
    'public_link' => 'Link pubblico',
    'delete_note' => 'Le campagne scadute e disattivate vengono eliminate automaticamente.',
    'print_cards' => 'Stampa le carte (PDF)',
    'disable' => 'Disattiva',
    'delete' => 'Elimina ora',

    'status' => [
        'active' => 'attiva',
        'disabled' => 'disattivata',
        'expired' => 'scaduta',
    ],

    'facts' => [
        'tokens_per_key' => ':tokens token per chiave',
        'redeemed' => ':redeemed di :total riscattati',
        'budget' => ':left di :total token rimasti',
        'expires' => 'termina il :date',
    ],

    'create' => [
        'heading' => 'Crea una campagna',
        'info' => 'La campagna è garantita da questa chiave: i token regalati vengono detratti dal tuo saldo quando vengono usati. Le campagne durano 3 mesi, le chiavi regalate sono valide 1 mese dopo il riscatto.',
        'name' => 'Nome (visibile solo a te)',
        'tokens_per_key' => 'Token per chiave regalata',
        'total_volume' => 'Numero massimo totale di token',
        'total_volume_hint' => 'La tua chiave contiene attualmente :charge token. Non puoi mai regalare più del tuo saldo.',
        'voucher_count' => 'Numero di buoni (opzionale)',
        'voucher_count_hint' => 'Predefinito: totale massimo diviso per i token per chiave.',
        'submit' => 'Crea campagna',
        'error' => [
            'tokens_per_key_too_high' => 'I token per chiave non possono superare il totale massimo.',
            'voucher_count_out_of_range' => 'Il numero di buoni non è compatibile con i token per chiave e il totale massimo.',
            'over_budget' => 'Il totale massimo supera il saldo disponibile.',
            'too_many_active' => 'Hai già raggiunto il numero massimo di campagne attive.',
            'invalid' => 'Non è stato possibile creare la campagna. Controlla i tuoi dati.',
            'unreachable' => 'Al momento non è stato possibile creare la campagna. Riprova più tardi.',
        ],
    ],

    /**
     * /c — App\Http\Controllers\VoucherController.
     */
    'redeem' => [
        'enter' => [
            'heading' => 'Riscatta il tuo buono',
            'description' => 'Hai ricevuto un codice buono per ricerche gratuite su MetaGer? Inseriscilo qui per ottenere la tua chiave MetaGer personale.',
            'label' => 'Il tuo codice buono',
            'submit' => 'Riscatta codice',
            'invalid_code' => 'Questo codice non è valido. Controlla i dati inseriti.',
            'rate_limited' => 'Troppi tentativi. Riprova più tardi.',
        ],
        'teaser' => [
            'heading' => 'Il tuo regalo MetaGer',
            'tokens' => 'Token',
            'description' => 'Questo codice ti dà una tua chiave MetaGer caricata con :tokens token - cerca sul web senza pubblicità e senza essere tracciato.',
            'validity' => 'La chiave è valida per :days giorni dopo il riscatto.',
            'submit' => 'Ottieni la mia chiave',
        ],
        'redeemed' => [
            'heading' => 'Ecco la tua chiave MetaGer!',
            'description' => 'La tua nuova chiave è caricata con :tokens token.',
            'save' => [
                'heading' => '1. Salva la tua chiave',
                'description' => 'La tua chiave è il tuo accesso - viene mostrata solo qui e non può essere recuperata. Salvala nel tuo gestore di password, scarica il codice QR o stampa questa pagina.',
            ],
            'copy_key' => 'Copia chiave',
            'validity' => 'La chiave è valida fino al :date.',
            'use' => [
                'heading' => '2. Inizia a cercare',
                'description' => 'Apri questo link per attivare la chiave nel tuo browser. Aggiungilo ai preferiti per rimanere connesso.',
            ],
            'copy_url' => 'Copia link',
            'start_searching' => 'Inizia a cercare ora',
            'to_account' => 'Vai al mio account',
            'qr_alt' => 'Codice QR per la chiave',
            'no_cookies' => 'Questo browser non sembra conservare i cookie. Salva invece la chiave o il codice QR qui sopra.',
        ],
        'error' => [
            'heading' => 'Non ha funzionato',
            'invalid_code' => 'Questo codice non esiste. Controlla i dati inseriti.',
            'invalid_token' => 'Questo link non è valido o è scaduto.',
            'already_redeemed' => 'Questo codice è già stato riscattato.',
            'campaign_inactive' => 'Questa campagna è terminata. Il codice non può più essere riscattato.',
            'budget_exhausted' => 'Tutti i regali di questa campagna sono già stati distribuiti.',
            'rate_limited' => 'Troppi tentativi. Riprova più tardi.',
            'unreachable' => 'Al momento non è stato possibile riscattare il buono. Riprova più tardi.',
            'unknown' => 'Si è verificato un errore imprevisto. Riprova più tardi.',
            'retry' => 'Inserisci un codice',
        ],
    ],
];
