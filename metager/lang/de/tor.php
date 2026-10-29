<?php
return [
    'description' => 'Nachstehend finden Sie den Link zum "hidden Service" von MetaGer. Achtung! Dieser Link kann nur über das Tor-Netzwerk erreicht werden. Ihr Browser gibt eine Fehlermeldung aus, wenn er nicht mit diesem verbunden ist.',
    'torbutton' => '"Hidden Service" öffnen',
    'torurl' => 'http://metagerv65pwclop2rsfzg4jwowpavpwd6grhhlvdgsswvo6ii4akgyd.onion/',
    // Shown only with the MetaGer web extension installed, which fills it in;
    // see resources/views/tor.blade.php.
    'extension' => [
        'disabled' => 'Sie nutzen die MetaGer-Web-Erweiterung? Sie funktioniert auch auf dem "Hidden Service", sobald Sie ihr den Zugriff auf die Onion-Adresse erlauben.',
        'enable'   => 'Erweiterung für den "Hidden Service" aktivieren',
        'enabled'  => 'Die MetaGer-Web-Erweiterung ist für den "Hidden Service" aktiviert.',
        'manage'   => 'Verwalten',
    ],
];
