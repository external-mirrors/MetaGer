<?php

/**
 * Die Anmeldeseite — /anmelden. Siehe lang/de/login.php für die Herkunft der
 * Schlüssel und dafür, was gegenüber dem Keymanager neu ist.
 */
return [
    'heading' => 'Log in to MetaGer',
    'lede' => 'Your key is your account. It carries your token balance, and it is all we know about you — no name, no email address, no password.',

    'key' => [
        'label' => 'Key or login code',
        'hint' => '36 characters. From a device that is already logged in, the six-digit one-time password from the transfer dialog works too.',
        'placeholder' => 'xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx',
    ],

    'submit' => 'Log in',
    'or' => 'or',

    'file' => [
        'button' => 'Choose backup file',
        'hint' => 'The file or QR code image you saved when you set your key up.',
    ],

    'qr' => [
        'button' => 'Scan QR code',
        'hint' => "With this device's camera, from another device's screen for instance.",
        'no_camera' => 'No camera available.',
        'invalid' => 'That QR code does not contain a key.',
        'close' => 'Close',
    ],

    'create' => [
        'prompt' => 'No key yet?',
        'action' => 'Set up a key',
    ],

    'errors' => [
        'invalid_key' => 'That is not a valid key. A key has 36 characters, a login code has six digits.',
        'invalid_login_code' => 'That login code is no longer valid. It lasts a few seconds and works for one login only — have the logged-in device show you a new one. The short mark next to your balance is not a login code.',
        // Six characters that are not a key. Nearly always the short mark shown
        // next to the balance — see KeyIdenticon.
        'key_mark' => 'Those six characters are your key\'s short mark — the one shown next to your balance. It names your account, it does not open it. To sign in you need the full 36-character key, or a login code from a device that is already signed in.',
        'invalid_key_payment_id' => 'That is a payment id, not a key. Your key has 36 characters and does not start with a Z.',
        'no_input' => 'Please enter a key or choose a backup file.',
        'file_unreadable' => 'No key could be read from that file. It should contain the QR code you saved when you set your key up.',
    ],

    'validation' => [
        'hex' => 'A key only contains the characters 0–9, a–f and dashes.',
        'uuid' => 'That is not a valid key.',
        'login' => 'That is neither a complete key nor a login code.',
    ],

    'empty_key' => [
        'message' => 'There is no balance on this key. If that is expected, go ahead and log in — otherwise a character may have been mistyped.',
        'entered' => 'Key entered',
        'revalidate' => 'Check the input',
        'confirm' => 'Log in anyway',
    ],

    'extension' => [
        'heading' => 'The MetaGer extension for your browser',
        'text' => 'Stay logged in even after clearing your browser data — and stay <a href=":tokenlink">provably anonymous</a> while logged in.',
        'install' => 'Install for :browser',
        'install_generic' => 'Install the extension',
    ],
];
