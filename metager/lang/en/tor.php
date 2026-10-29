<?php
return [
    'description' => 'Below you will find the link to MetaGer\'s hidden service. Attention: This link can only be reached via the Tor network. Your browser produces an error message if it is not connected to it.',
    'torbutton'   => 'Open TOR-hidden service',
    'torurl'      => 'http://metagerv65pwclop2rsfzg4jwowpavpwd6grhhlvdgsswvo6ii4akgyd.onion/en/',
    // Shown only with the MetaGer web extension installed, which fills it in;
    // see resources/views/tor.blade.php.
    'extension' => [
        'disabled' => 'Using the MetaGer web extension? It can work on the hidden service too, once you allow it to access the onion address.',
        'enable'   => 'Enable the extension for the hidden service',
        'enabled'  => 'The MetaGer web extension is enabled for the hidden service.',
        'manage'   => 'Manage',
    ],
];
