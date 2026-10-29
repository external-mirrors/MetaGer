<?php
return [
    'description' => 'Nedenfor finder du linket til MetaGers skjulte tjeneste. Vær opmærksom på dette: Dette link kan kun nås via Tor-netværket. Din browser producerer en fejlmeddelelse, hvis den ikke er forbundet til det.',
    'torbutton' => 'Åbn TOR-skjult tjeneste',
    'torurl' => 'http://metagerv65pwclop2rsfzg4jwowpavpwd6grhhlvdgsswvo6ii4akgyd.onion/en/',
    // Shown only with the MetaGer web extension installed, which fills it in;
    // see resources/views/tor.blade.php.
    'extension' => [
        'disabled' => 'Bruger du MetaGer-webudvidelsen? Den kan også fungere på den skjulte tjeneste, når du giver den adgang til onion-adressen.',
        'enable'   => 'Aktivér udvidelsen for den skjulte tjeneste',
        'enabled'  => 'MetaGer-webudvidelsen er aktiveret for den skjulte tjeneste.',
        'manage'   => 'Administrer',
    ],
];
