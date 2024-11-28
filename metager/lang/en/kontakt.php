<?php
return [
    'headline' => [
        '1' => 'Contact',
        '2' => 'Email',
        'pgp' => 'Encryption',
    ],
    'email' => [
        'text' => 'You can reach out to us by sending a mail to: <a href="mailto::mail">:mail</a>',
        'pgp' => [
            'description' => 'Our Emails are cryptocraphically signed. If you want to verify the signature or send your mail encrypted please use the following public key. If you want to receive an encrypted answer please attach your public key to your encrypted and signed mail.',
            'pubkey' => 'PGP Publickey: <a href="/download/pubkey.asc" download="0x2185CC8F3CA782EC.asc">0x2185CC8F3CA782EC</a> or on <a href=":keyserver" target="_blank" rel="noopener">keys.openpgp.org</a>',
            'fingerprint' => 'PGP Fingerprint: 5FA5 2398 C382 B498 B14A  B7F6 2185 CC8F 3CA7 82EC',
        ],
    ],
    'form' => [
        '1' => 'Anonymous Contact Form',
        '2' => 'You can send us an anonymous message by using this form. If you chose to not include your email address you will however of course receive no answer.',
        'name' => 'Name',
        '5' => 'Your e-mail-address (optional)',
        '6' => 'Your message',
        '7' => 'Subject',
        '8' => 'Send',
        '9' => 'Up to 5 attachments (filesize < 5 MB)',
    ],
    'letter' => [
        '1' => 'By Letter Mail',
        '2' => 'We prefer digital contact. However, if you consider it neccessary to contact us postally, you can mail us at:',
        '3' => "SUMA-EV\r\nPostfach 51 01 43\r\nD-30631 Hannover\r\nGermany",
    ],
    'error' => [
        '1' => 'We are sorry, but unfortunately we did not receive any data with your contact request. The message was not sent.',
        '2' => 'There was an error delivering your message. You can contact us directly under :email',
    ],
    'success' => [
        '1' => 'Your message was delivered successfully. A first automatic reply was sent to :email.',
    ],
];