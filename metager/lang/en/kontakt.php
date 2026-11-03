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
    'letter' => [
        '1' => 'By Letter Mail',
        '2' => 'We prefer digital contact. However, if you consider it neccessary to contact us postally, you can mail us at:',
        '3' => "SUMA-EV\r\nPostfach 51 01 43\r\nD-30631 Hannover\r\nGermany",
    ],
    'intro' => 'Before you write: many questions already have an answer here — usually quicker than by email.',
    'removal' => [
        'heading' => 'Want a search result removed?',
        'text' => 'Please read our guide first. MetaGer shows the results of other search engines, so a result can only be removed at the website and at those search engines. The guide shows where and how.',
        'link' => 'Read the guide',
    ],
    'topics' => [
        'heading' => 'Other common questions',
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
    'reach' => 'How to reach us',
];
