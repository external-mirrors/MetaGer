<?php

/**
 * Die Seite zum Erstellen eines Schlüssels — /schluessel-erstellen. Siehe
 * lang/de/key-create.php für die Herkunft der Schlüssel und dafür, was
 * gegenüber dem Keymanager neu ist.
 */
return [
    'heading' => 'Create a key',
    'lede' => 'Your key is your account. It carries your token balance, and it is all we know about you — no name, no email address, no password. Which also means: lose it, and the balance on it is gone.',

    'existing' => [
        'text' => 'Already had a MetaGer key? Log in with it instead of creating a new one — a new key gets its own separate balance, and the old one stays on the old key.',
        'action' => 'Log in with an existing key',
    ],

    'offer' => [
        'text' => 'One press of the button and you have one. No form, no credentials: MetaGer rolls a string of characters that belongs to nobody yet.',
        'button' => 'Create key now',
    ],

    'working' => 'One moment: we are rolling a new key for you …',

    'key' => [
        'label' => 'Your new key',
        'hint' => '36 characters. They are what logs you in on every further device.',
    ],

    'copy' => [
        'action' => 'Copy key',
        'done' => 'Copied',
    ],

    'save' => [
        'heading' => 'Keep it somewhere',
        'text' => 'As long as this browser keeps the cookie, you stay logged in. If it loses it — a new device, cleared browsing data — this key is the only way back.',

        'qr' => [
            'alt' => 'QR code leading to your key',
            'action' => 'Save as an image',
            'hint' => 'The image the login form asks for. You can upload it there later, or photograph it with the camera.',
        ],

        'url' => [
            'label' => 'Bookmark',
            'action' => 'Copy URL',
            'hint' => 'Opening this URL sets the key up again, together with this browser\'s settings.',
        ],

        'no_cookies' => 'This browser stores no cookies for MetaGer. Without a cookie you do not stay logged in — the URL above is then how you log in before a search. You can also add it as a search engine in your browser.',
    ],

    'continue' => 'Next: add credit',
    'continue_hint' => 'A new key has no balance yet. The next step is choosing a token package.',

    'errors' => [
        'keyserver_unreachable' => 'No key could be created just now. That is on us, not on you — try again in a moment.',
        'too_many_attempts' => 'A great many keys have just been created from this connection. Wait a few minutes and then reload the page.',
        'no_key' => 'The key got lost along the way — that happens when the page has been open for a long time. Here is a new one.',
    ],
];
