<?php
return [
    'lead' => 'Email gets the fastest answer.',
    'headline' => [
        '1' => 'Contact',
        '2' => 'Email',
    ],
    'email' => [
        'write' => 'Write an email',
        'pgp' => [
            'summary' => 'Write encrypted (PGP)',
            'signed' => 'Every email from us is signed with our OpenPGP key, which also travels in its Autocrypt header. Use this key to write to us encrypted:',
            'replies' => [
                'heading' => 'Encrypted replies',
                'how' => 'We reply encrypted automatically once we have your key and trust it. For that, sign your email to us. We take your key from the email (attached or in its Autocrypt header) or from keys.openpgp.org, and use it by ourselves if',
                'trusted_when' => [
                    'keys.openpgp.org hands it out for your address (so your address is verified there), or',
                    'your email carries a valid DKIM signature from your sender domain.',
                ],
                'otherwise' => 'Otherwise someone on our team confirms it by hand first. From then on, everything we send you is encrypted, not just replies.',
                'tofu' => 'We trust the first key we receive this way for your address (trust on first use). We don\'t switch to a new key for the same address automatically: until we have checked it, we keep encrypting to the previous one.',
                'never_plain' => 'We never answer an encrypted email unencrypted. If we don\'t have your key, we can\'t reply, so please always sign encrypted emails to us.',
            ],
            'pubkey' => 'PGP Publickey: <a href="/download/pubkey.asc" download="0x2185CC8F3CA782EC.asc">0x2185CC8F3CA782EC</a> or on <a href=":keyserver" target="_blank" rel="noopener">keys.openpgp.org</a>',
            'fingerprint' => 'PGP Fingerprint: 5FA5 2398 C382 B498 B14A  B7F6 2185 CC8F 3CA7 82EC',
        ],
    ],
    'letter' => [
        '1' => 'Post',
        '3' => "SUMA-EV\r\nPostfach 51 01 43\r\nD-30631 Hannover\r\nGermany",
    ],
    'reason' => [
        'legend' => 'What is this about?',
        'general' => 'A question or request',
        'removal' => 'Remove a search result',
    ],
    'removal' => [
        'heading' => 'Removing a search result',
        'how' => 'Removal requests are made online: we guide you through yours step by step and show you where it has the most effect.',
        'only' => 'We cannot accept such requests by email, phone or post.',
        'button' => 'Request a removal',
        'why' => [
            'heading' => 'Why search engines remove results',
            'text' => 'Search engines take a result out when there is a legal reason to. If a result for a person\'s name leads to information that is wrong, out of date or no longer relevant, that person can demand that it is no longer shown (right to be forgotten, Art. 17 GDPR). The public\'s interest in the information counts too. Copyright infringement and criminal content can also be reasons. That is why a result you find elsewhere may be missing from MetaGer.',
        ],
    ],
    'phone' => [
        'heading' => 'Phone',
        'hours' => 'Monday to Friday, :from–:to German time',
        'outside' => 'Within these hours you will almost always reach us, outside them almost never. Then it is better to send us an email.',
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
            'text' => 'What we store and what we do not',
        ],
    ],
];
