<?php
return [
    'description' => 'Vous trouverez ci-dessous le lien vers le service caché de MetaGer. Attention : Ce lien n\'est accessible que via le réseau Tor. Votre navigateur produit un message d\'erreur s\'il n\'y est pas connecté.',
    'torbutton' => 'Ouvrir un service caché par TOR',
    'torurl' => 'http://metagerv65pwclop2rsfzg4jwowpavpwd6grhhlvdgsswvo6ii4akgyd.onion/en/',
    // Shown only with the MetaGer web extension installed, which fills it in;
    // see resources/views/tor.blade.php.
    'extension' => [
        'disabled' => 'Vous utilisez l\'extension web MetaGer ? Elle peut aussi fonctionner sur le service caché, dès que vous l\'autorisez à accéder à l\'adresse onion.',
        'enable'   => 'Activer l\'extension pour le service caché',
        'enabled'  => 'L\'extension web MetaGer est activée pour le service caché.',
        'manage'   => 'Gérer',
    ],
];
