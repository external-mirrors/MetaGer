<?php

/**
 * Die Seite zum Erstellen eines Schlüssels — /schluessel-erstellen. Siehe
 * lang/de/key-create.php für die Herkunft der Schlüssel und dafür, was
 * gegenüber dem Keymanager neu ist.
 */
return [
    'heading' => 'Creare una chiave',
    'lede' => 'La sua chiave è il suo account. Porta il suo saldo di token ed è tutto ciò che sappiamo di lei: nessun nome, nessun indirizzo e-mail, nessuna password. Significa anche che, se la perde, perde il saldo che vi si trova.',

    'existing' => [
        'text' => 'Aveva già una chiave MetaGer? Acceda con quella invece di crearne una nuova: una chiave nuova riceve un proprio saldo separato, e quello vecchio resta sulla chiave vecchia.',
        'action' => 'Accedere con una chiave esistente',
    ],

    'offer' => [
        'text' => 'Una pressione del pulsante e ne ha una. Nessun modulo, nessuna credenziale: MetaGer sorteggia una sequenza di caratteri che non appartiene ancora a nessuno.',
        'button' => 'Creare subito la chiave',
    ],

    'working' => 'Un momento: stiamo sorteggiando una nuova chiave per lei …',

    /**
     * The mark that sits in the corner of every page from here on.
     *
     * Derived from the key and stored nowhere
     * ({@see \App\Authentication\KeyIdenticon}). It is here because a mark you
     * are meant to recognise has to be shown the first time — otherwise it is
     * just a coloured square the second time.
     */
    'identity' => 'Così riconoscerà il suo account: da ora questo contrassegno compare in alto a destra su ogni pagina.',

    'key' => [
        'label' => 'La sua nuova chiave',
        'hint' => '36 caratteri. Sono quelli con cui accede su ogni altro dispositivo.',
    ],

    'copy' => [
        'action' => 'Copiare la chiave',
        'done' => 'Copiata',
    ],

    'save' => [
        'heading' => 'La conservi da qualche parte',
        'text' => 'Finché questo browser conserva il cookie, lei resta connesso. Se lo perde — un nuovo dispositivo, dati di navigazione cancellati —, questa chiave è l\'unica via di ritorno.',

        'qr' => [
            'alt' => 'Codice QR che porta alla sua chiave',
            'action' => 'Salvare come immagine',
            'hint' => 'L\'immagine che il modulo di accesso richiede. Più avanti può caricarla lì o fotografarla con la fotocamera.',
        ],

        'url' => [
            'label' => 'Segnalibro',
            'action' => 'Copiare l\'URL',
            'hint' => 'Aprire questo URL reimposta la chiave insieme alle impostazioni di questo browser.',
        ],

        'no_cookies' => 'Questo browser non memorizza cookie per MetaGer. Senza cookie lei non resta connesso: allora l\'URL qui sopra è il modo di accedere prima di una ricerca. Può anche aggiungerlo come motore di ricerca nel suo browser.',
    ],

    'continue' => 'Avanti: ricaricare il saldo',
    'continue_hint' => 'Una chiave nuova non ha ancora saldo. Nel passo successivo sceglie un pacchetto di token.',

    'errors' => [
        'keyserver_unreachable' => 'Al momento non è stato possibile creare una chiave. Dipende da noi e non da lei — riprovi tra poco.',
        'too_many_attempts' => 'Da questa connessione sono appena state create moltissime chiavi. Attenda qualche minuto e poi ricarichi la pagina.',
        'no_key' => 'La chiave si è persa per strada — succede quando la pagina è rimasta aperta a lungo. Eccone una nuova.',
    ],
];
