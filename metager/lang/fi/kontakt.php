<?php
return [
    'headline' => [
        '1' => 'Ota yhteyttä',
        '2' => 'Sähköposti',
        'pgp' => 'Salaus',
    ],
    'letter' => [
        '1' => 'Kirjepostitse',
        '2' => 'Suosimme digitaalista yhteydenpitoa. Jos kuitenkin katsot tarpeelliseksi ottaa meihin yhteyttä postitse, voit lähettää meille sähköpostia osoitteeseen:',
        '3' => "SUMA-EV\r
Postfach 51 01 43\r
D-30631 Hannover\r
Saksa",
    ],
    'email' => [
        'text' => 'Voit ottaa meihin yhteyttä lähettämällä sähköpostia osoitteeseen: <a href="mailto::mail">:mail</a>',
        'pgp' => [
            'description' => 'Sähköpostimme on kryptografisesti allekirjoitettu. Jos haluat tarkistaa allekirjoituksen tai lähettää sähköpostisi salattuna, käytä seuraavaa julkista avainta. Jos haluat saada salatun vastauksen, liitä julkinen avaimesi salattuun ja allekirjoitettuun sähköpostiisi.',
            'pubkey' => 'PGP Publiclickey: <a href="/download/pubkey.asc" download="0x2185CC8F3CA782EC.asc">0x2185CC8F3CA782EC</a> tai osoitteessa <a href=":keyserver" target="_blank" rel="noopener">keys.openpgp.org.</a>',
            'fingerprint' => 'PGP Fingerprint: 5FA5 2398 C382 B498 B14A B7F6 2185 CC8F 3CA7 82EC',
        ],
    ],
];
