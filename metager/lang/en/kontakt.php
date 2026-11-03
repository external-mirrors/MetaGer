<?php
return [
    'headline' => [
        '1' => 'Contact',
        '2' => 'Email',
    ],
    'email' => [
        'write' => 'Write an email',
        'pgp' => [
            'summary' => 'Write encrypted (PGP)',
            'description' => 'Our Emails are cryptocraphically signed. If you want to verify the signature or send your mail encrypted please use the following public key. If you want to receive an encrypted answer please attach your public key to your encrypted and signed mail.',
            'pubkey' => 'PGP Publickey: <a href="/download/pubkey.asc" download="0x2185CC8F3CA782EC.asc">0x2185CC8F3CA782EC</a> or on <a href=":keyserver" target="_blank" rel="noopener">keys.openpgp.org</a>',
            'fingerprint' => 'PGP Fingerprint: 5FA5 2398 C382 B498 B14A  B7F6 2185 CC8F 3CA7 82EC',
        ],
    ],
    'letter' => [
        '1' => 'By Letter Mail',
        '2' => 'Email gets the fastest answer. By post, you can reach us at:',
        '3' => "SUMA-EV\r\nPostfach 51 01 43\r\nD-30631 Hannover\r\nGermany",
    ],
    'removal' => [
        'hint' => 'Is this about removing a search result? Please read <a href=":link">our guide</a> first: MetaGer shows other search engines\' results, so a result can usually only be removed there.',
    ],
    'topics' => [
        'heading' => 'Common questions',
        'key' => [
            'title' => 'MetaGer key and tokens',
            'text' => 'How the key works, topping up and balance',
        ],
        'orders' => [
            'title' => 'Orders and invoices',
            'text' => 'Find an order, receipts and refunds',
        ],
        'donation' => [
            'title' => 'Donate',
            'text' => 'Support MetaGer with a donation',
        ],
        'membership' => [
            'title' => 'Become a member',
            'text' => 'Join SUMA-EV, the association behind MetaGer',
        ],
        'help' => [
            'title' => 'Search help',
            'text' => 'Features, settings and frequent questions',
        ],
        'privacy' => [
            'title' => 'Privacy',
            'text' => 'What we store — and what we do not',
        ],
    ],
];
