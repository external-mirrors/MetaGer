<?php
return [
    'description' => 'Di seguito troverete il link al servizio nascosto di MetaGer. Attenzione: Questo link è raggiungibile solo attraverso la rete Tor. Il vostro browser produce un messaggio di errore se non è connesso ad essa.',
    'torbutton' => 'Aprire il servizio nascosto da TOR',
    'torurl' => 'http://metagerv65pwclop2rsfzg4jwowpavpwd6grhhlvdgsswvo6ii4akgyd.onion/en/',
    // Shown only with the MetaGer web extension installed, which fills it in;
    // see resources/views/tor.blade.php.
    'extension' => [
        'disabled' => 'Usi l\'estensione web di MetaGer? Può funzionare anche sul servizio nascosto, una volta che le consenti di accedere all\'indirizzo onion.',
        'enable'   => 'Attiva l\'estensione per il servizio nascosto',
        'enabled'  => 'L\'estensione web di MetaGer è attiva per il servizio nascosto.',
        'manage'   => 'Gestisci',
    ],
];
