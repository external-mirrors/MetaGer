<?php
return [
    'description' => 'Alta löydät linkin MetaGerin piilotettuun palveluun. Huomio! Tähän linkkiin pääsee vain Tor-verkon kautta. Selaimesi tuottaa virheilmoituksen, jos se ei ole yhteydessä siihen.',
    'torbutton' => 'Avaa TOR-salattu palvelu',
    'torurl' => 'http://metagerv65pwclop2rsfzg4jwowpavpwd6grhhlvdgsswvo6ii4akgyd.onion/en/',
    // Shown only with the MetaGer web extension installed, which fills it in;
    // see resources/views/tor.blade.php.
    'extension' => [
        'disabled' => 'Käytätkö MetaGer-selainlaajennusta? Se toimii myös piilopalvelussa, kun annat sille luvan käyttää onion-osoitetta.',
        'enable'   => 'Ota laajennus käyttöön piilopalvelussa',
        'enabled'  => 'MetaGer-selainlaajennus on käytössä piilopalvelussa.',
        'manage'   => 'Hallitse',
    ],
];
