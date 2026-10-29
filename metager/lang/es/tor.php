<?php
return [
    'description' => 'A continuación, encontrará el enlace al servicio oculto de MetaGer. Atención: Solo se puede acceder a este enlace a través de la red Tor. Su navegador genera un mensaje de error si no está conectado a él.',
    'torbutton' => 'Usar Tor',
    'torurl' => 'http://metagerv65pwclop2rsfzg4jwowpavpwd6grhhlvdgsswvo6ii4akgyd.onion/es/',
    // Shown only with the MetaGer web extension installed, which fills it in;
    // see resources/views/tor.blade.php.
    'extension' => [
        'disabled' => '¿Usas la extensión web de MetaGer? También puede funcionar en el servicio oculto, en cuanto le permitas acceder a la dirección onion.',
        'enable'   => 'Activar la extensión para el servicio oculto',
        'enabled'  => 'La extensión web de MetaGer está activada para el servicio oculto.',
        'manage'   => 'Gestionar',
    ],
];
