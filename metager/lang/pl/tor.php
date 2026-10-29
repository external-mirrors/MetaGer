<?php
return [
    'description' => 'Poniżej znajduje się link do ukrytej usługi MetaGer. Uwaga: Do tego linku można dotrzeć wyłącznie za pośrednictwem sieci Tor. Przeglądarka wyświetli komunikat o błędzie, jeśli nie jest z nią połączona.',
    'torbutton' => 'Otwórz usługę ukrytą pod TOR',
    'torurl' => 'http://metagerv65pwclop2rsfzg4jwowpavpwd6grhhlvdgsswvo6ii4akgyd.onion/en/',
    // Shown only with the MetaGer web extension installed, which fills it in;
    // see resources/views/tor.blade.php.
    'extension' => [
        'disabled' => 'Korzystasz z rozszerzenia MetaGer? Może ono działać także w ukrytej usłudze, gdy zezwolisz mu na dostęp do adresu onion.',
        'enable'   => 'Włącz rozszerzenie dla ukrytej usługi',
        'enabled'  => 'Rozszerzenie MetaGer jest włączone dla ukrytej usługi.',
        'manage'   => 'Zarządzaj',
    ],
];
