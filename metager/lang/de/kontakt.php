<?php
return [
    'headline' => [
        '1' => 'Kontakt',
        '2' => 'E-Mail',
        'pgp' => 'Verschlüsselung',
    ],
    'letter' => [
        '1' => 'Per Brief-Post',
        '2' => 'Wir ziehen es vor, auf digitalem Wege kontaktiert zu werden. Wenn Sie jedoch eine postalische Kontaktaufnahme als unbedingt nötig erachten, erreichen Sie uns unter der folgenden Adresse:',
        '3' => "SUMA-EV\r
Postfach 51 01 43\r
D-30631 Hannover\r
Germany",
    ],
    'email' => [
        'text' => 'Sie können uns eine E-Mail schicken an: <a href="mailto::mail">:mail</a>',
        'pgp' => [
            'description' => 'Unsere Emails sind kryptografisch signiert. Wenn Sie die Signatur überprüfen oder Ihre E-Mail verschlüsselt senden möchten, verwenden Sie bitte den folgenden öffentlichen Schlüssel. Wenn Sie eine verschlüsselte Antwort erhalten möchten, fügen Sie bitte Ihren öffentlichen Schlüssel an Ihre verschlüsselte und signierte E-Mail an.',
            'pubkey' => 'PGP Publickey: <a href="/download/pubkey.asc" download="0x2185CC8F3CA782EC.asc">0x2185CC8F3CA782EC</a> oder auf <a href=":keyserver" target="_blank" rel="noopener">keys.openpgp.org</a>',
            'fingerprint' => 'PGP-Fingerabdruck: 5FA5 2398 C382 B498 B14A B7F6 2185 CC8F 3CA7 82EC',
        ],
    ],
];
