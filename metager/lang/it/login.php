<?php

/**
 * Die Anmeldeseite — /anmelden. Siehe lang/de/login.php für die Herkunft der
 * Schlüssel und dafür, was gegenüber dem Keymanager neu ist.
 */
return [
    'heading' => 'Accedere a MetaGer',
    'lede' => 'La sua chiave è il suo account. Porta il suo saldo di token ed è tutto ciò che sappiamo di lei: nessun nome, nessun indirizzo e-mail, nessuna password.',

    'key' => [
        'label' => 'Chiave o codice di accesso',
        'hint' => '36 caratteri. Da un dispositivo già connesso va bene anche la password monouso a sei cifre della finestra di trasferimento.',
        'placeholder' => 'xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx',
    ],

    'submit' => 'Accedere',
    'or' => 'o',

    'file' => [
        'button' => 'Scegliere il file di backup',
        'hint' => 'Il file o l\'immagine del codice QR che ha salvato quando ha creato la chiave.',
    ],

    'qr' => [
        'button' => 'Scansione del codice QR',
        'hint' => 'Con la fotocamera di questo dispositivo, per esempio dallo schermo di un altro.',
        'no_camera' => 'Nessuna fotocamera disponibile.',
        'invalid' => 'Quel codice QR non contiene una chiave.',
        'close' => 'Chiudere',
    ],

    'create' => [
        'prompt' => 'Non ha ancora una chiave?',
        'action' => 'Creare una chiave',
    ],

    'errors' => [
        'invalid_key' => 'Questa non è una chiave valida. Una chiave ha 36 caratteri, un codice di accesso sei cifre.',
        'invalid_login_code' => 'Questo codice di accesso non è più valido. Dura pochi secondi e vale per un solo accesso: si faccia mostrare un codice nuovo dal dispositivo connesso. La sigla accanto al suo saldo non è un codice di accesso.',
        // Sei caratteri che non sono una chiave. Quasi sempre la sigla accanto
        // al saldo — vedi KeyIdenticon.
        'key_mark' => 'Questi sei caratteri sono la sigla della sua chiave: quella che compare accanto al suo saldo. Identifica il suo account, ma non lo apre. Per accedere serve la chiave completa di 36 caratteri oppure un codice di accesso da un dispositivo già connesso.',
        'invalid_key_payment_id' => 'Questo è un numero di pagamento, non una chiave. La sua chiave ha 36 caratteri e non comincia con una Z.',
        'no_input' => 'Inserisca una chiave o scelga un file di backup.',
        'file_unreadable' => 'Da quel file non è stato possibile leggere alcuna chiave. Dovrebbe contenere il codice QR che ha salvato quando ha creato la chiave.',
    ],

    'validation' => [
        'hex' => 'Una chiave contiene solo i caratteri 0–9, a–f e i trattini.',
        'uuid' => 'Questa non è una chiave valida.',
        'login' => 'Questa non è né una chiave completa né un codice di accesso.',
    ],

    'empty_key' => [
        'message' => 'Su questa chiave non c\'è saldo. Se è previsto, acceda pure; altrimenti potrebbe esserci un carattere digitato male.',
        'entered' => 'Chiave inserita',
        'revalidate' => 'Controllare l\'inserimento',
        'confirm' => 'Accedere comunque',
    ],

    'extension' => [
        'heading' => 'L\'estensione MetaGer per il suo browser',
        'text' => 'Rimanga connesso anche dopo aver cancellato i dati del browser — e resti <a href=":tokenlink">dimostrabilmente anonimo</a> pur essendo connesso.',
        'install' => 'Installare per :browser',
        'install_generic' => "Installare l'estensione",
    ],
];
