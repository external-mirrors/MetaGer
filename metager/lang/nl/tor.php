<?php
return [
    'description' => 'Hieronder vind je de link naar de verborgen service van MetaGer. Let op: Deze link is alleen bereikbaar via het Tor-netwerk. Je browser geeft een foutmelding als hij er geen verbinding mee heeft.',
    'torbutton' => 'Open TOR verborgen service',
    'torurl' => 'http://metagerv65pwclop2rsfzg4jwowpavpwd6grhhlvdgsswvo6ii4akgyd.onion/en/',
    // Shown only with the MetaGer web extension installed, which fills it in;
    // see resources/views/tor.blade.php.
    'extension' => [
        'disabled' => 'Gebruik je de MetaGer-webextensie? Die werkt ook op de verborgen dienst, zodra je haar toegang geeft tot het onion-adres.',
        'enable'   => 'Extensie inschakelen voor de verborgen dienst',
        'enabled'  => 'De MetaGer-webextensie is ingeschakeld voor de verborgen dienst.',
        'manage'   => 'Beheren',
    ],
];
