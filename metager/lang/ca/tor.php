<?php
return [
    'description' => 'A continuació trobareu l\'enllaç al servei ocult de MetaGer. Atenció: aquest enllaç només és accessible a través de la xarxa Tor. El vostre navegador mostrarà un missatge d\'error si no hi està connectat.',
    'torbutton'   => 'Obre el servei ocult TOR',
    'torurl'      => 'http://metagerv65pwclop2rsfzg4jwowpavpwd6grhhlvdgsswvo6ii4akgyd.onion/ca/',
    // Shown only with the MetaGer web extension installed, which fills it in;
    // see resources/views/tor.blade.php.
    'extension' => [
        'disabled' => 'Utilitzeu l\'extensió web de MetaGer? També pot funcionar al servei ocult, un cop li permeteu accedir a l\'adreça onion.',
        'enable'   => 'Activa l\'extensió per al servei ocult',
        'enabled'  => 'L\'extensió web de MetaGer està activada per al servei ocult.',
        'manage'   => 'Gestiona',
    ],
];
