<?php
return [
    'lead' => 'Vastaamme nopeimmin sähköpostiin.',
    'headline' => [
        '1' => 'Ota yhteyttä',
        '2' => 'Sähköposti',
    ],
    'letter' => [
        '1' => 'Posti',
        '3' => "SUMA-EV\r
Postfach 51 01 43\r
D-30631 Hannover\r
Saksa",
    ],
    'email' => [
        'write' => 'Kirjoita sähköposti',
        'pgp' => [
            'summary' => 'Kirjoita salattuna (PGP)',
            'signed' => 'Jokainen meiltä tuleva sähköposti on allekirjoitettu OpenPGP-avaimellamme, joka kulkee myös Autocrypt-otsakkeessa. Tällä avaimella voit kirjoittaa meille salattuna:',
            'replies' => [
                'heading' => 'Salatut vastaukset',
                'how' => 'Vastaamme salattuna automaattisesti heti, kun meillä on avaimesi ja luotamme siihen. Allekirjoita siksi sähköpostisi meille. Otamme avaimesi sähköpostista (liitteenä tai Autocrypt-otsakkeessa) tai keys.openpgp.org-palvelusta ja käytämme sitä itsestään, jos',
                'trusted_when' => [
                    'keys.openpgp.org antaa sen osoitteellesi (osoitteesi on siis vahvistettu siellä), tai',
                    'sähköpostissasi on lähettäjäverkkotunnuksesi kelvollinen DKIM-allekirjoitus.',
                ],
                'otherwise' => 'Muuten joku meistä vahvistaa sen ensin käsin. Sen jälkeen salaamme kaiken, mitä lähetämme sinulle, ei vain vastauksia.',
                'tofu' => 'Luotamme ensimmäiseen avaimeen, jonka saamme näin osoitteellesi (Trust on First Use). Samalle osoitteelle tulevaa uutta avainta emme ota käyttöön automaattisesti: kunnes olemme tarkistaneet sen, salaamme edelleen aiemmalla.',
                'never_plain' => 'Emme koskaan vastaa salattuun sähköpostiin salaamatta. Jos avaimesi puuttuu, emme voi vastata – allekirjoita siksi aina meille lähettämäsi salatut sähköpostit.',
            ],
            'pubkey' => 'PGP Publiclickey: <a href="/download/pubkey.asc" download="0x2185CC8F3CA782EC.asc">0x2185CC8F3CA782EC</a> tai osoitteessa <a href=":keyserver" target="_blank" rel="noopener">keys.openpgp.org.</a>',
            'fingerprint' => 'PGP Fingerprint: 5FA5 2398 C382 B498 B14A B7F6 2185 CC8F 3CA7 82EC',
        ],
    ],
    'removal' => [
        'hint' => 'Haluatko poistaa hakutuloksen? Lue ensin <a href=":link">ohjeemme</a>: MetaGer näyttää muiden hakukoneiden tuloksia, joten tuloksen voi yleensä poistaa vain niissä.',
    ],
    'phone' => [
        'heading' => 'Puhelin',
        'hours' => 'Maanantaista perjantaihin klo :from–:to Saksan aikaa',
        'outside' => 'Silloin tavoitat meidät lähes aina, muulloin tuskin koskaan. Lähetä meille silloin mieluummin sähköpostia.',
    ],
    'topics' => [
        'heading' => 'Yleisiä kysymyksiä',
        'key' => [
            'title' => 'MetaGer-avain ja tokenit',
            'text' => 'Miten avain toimii, lataaminen ja saldo',
        ],
        'orders' => [
            'title' => 'Tilaukset ja laskut',
            'text' => 'Löydä tilaus, kuitit ja hyvitykset',
        ],
        'donation' => [
            'title' => 'Lahjoita',
            'text' => 'Tue MetaGeria lahjoituksella',
        ],
        'membership' => [
            'title' => 'Liity jäseneksi',
            'text' => 'Liity SUMA-EV:hen, MetaGerin taustalla olevaan yhdistykseen',
        ],
        'help' => [
            'title' => 'Hakuohje',
            'text' => 'Toiminnot, asetukset ja usein kysytyt kysymykset',
        ],
        'privacy' => [
            'title' => 'Yksityisyys',
            'text' => 'Mitä tallennamme ja mitä emme',
        ],
    ],
];
