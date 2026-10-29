<?php
return [
    'description' => 'Nedan hittar du länken till MetaGers dolda tjänst. Uppmärksamhet: Den här länken kan endast nås via Tor-nätverket. Din webbläsare producerar ett felmeddelande om den inte är ansluten till den.',
    'torbutton' => 'Öppna TOR-dold tjänst',
    'torurl' => 'http://metagerv65pwclop2rsfzg4jwowpavpwd6grhhlvdgsswvo6ii4akgyd.onion/en/',
    // Shown only with the MetaGer web extension installed, which fills it in;
    // see resources/views/tor.blade.php.
    'extension' => [
        'disabled' => 'Använder du MetaGers webbtillägg? Det kan fungera på den dolda tjänsten också, när du ger det åtkomst till onion-adressen.',
        'enable'   => 'Aktivera tillägget för den dolda tjänsten',
        'enabled'  => 'MetaGers webbtillägg är aktiverat för den dolda tjänsten.',
        'manage'   => 'Hantera',
    ],
];
