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
];
