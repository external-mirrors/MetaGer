<?php
return [
    /**
     * The account, wherever it appears: the pill in the corner, the block at the
     * top of the site menu, and the one alert that interrupts.
     *
     * Its own file rather than more keys under index/sidebar, because the same
     * strings are now rendered from three different views on two different
     * layouts, and none of them is "the index page".
     */
    'pill' => [
        'charge' => ':charge tokens',
        // Shown instead of the key code when the key cannot be named — a legacy
        // non-UUID key whose canonical form we could not resolve.
        'signed_in' => 'Signed in',
        // The webextension: the key never reaches us, so there is nothing to name.
        'anonymous' => 'signed in anonymously',
        // The mark is decorative and the code is styled shorthand, so the whole
        // answer has to be in the label for anyone not reading the pixels.
        //
        // "ending in", not a bare "key :fingerprint": these are the last six
        // characters of the key (KeyUser::getKeyFingerprint()), and calling them
        // the key invited people to type them into the sign-in form — where,
        // until the guard in the keymanager's POST /key/enter, they resolved to
        // an empty phantom account.
        'aria' => 'My account – key ending in :fingerprint, :charge tokens',
        'aria_nocharge' => 'My account – key ending in :fingerprint',
        'aria_nofingerprint' => 'My account – :charge tokens',
        'aria_anonymous' => 'My account – signed in anonymously through the web extension',
    ],
    'sidebar' => [
        'balance' => ':charge tokens · ad-free',
        // Not "0 tokens · ad-free": at zero the searches are not ad-free,
        // they do not happen at all.
        'balance_empty' => 'No tokens left',
        'manage' => 'Manage account',
        'topup' => 'Top up',
        'logout' => 'Log out',
        'login' => 'Log in',
        'create' => 'Set up',
        'logged_out' => 'Not logged in. With a key you search ad-free and anonymously.',
        'anonymous_hint' => 'Ad-free · managed by the web extension',
        // The webextension holds the key, so the account lives in its popup.
        // The button this labels is hidden until the extension reveals it.
        'extension_settings' => 'Manage in the extension',
    ],
    /**
     * The account page itself — /konto, moved here from /keys/key/<uuid>.
     *
     * Taken from the keymanager's pass/lang/<locale>/key.json, but mostly new.
     * The old page was almost nothing but button labels; what it never said is
     * what any of them are *for* — which is exactly what support gets asked.
     *
     * Not carried over: `key.share.*`. The share button handed the settings URL,
     * key included, to `navigator.share` and therefore to the operating system's
     * share sheet. Passing an account on is not something a button should
     * advertise; whoever wants to can copy the URL. The copy button stayed.
     */
    'page' => [
        'heading' => 'My account',

        // Not "your key: 123456". These are the last six characters, and calling
        // them the key led people to type them into the sign-in form.
        'fingerprint' => 'Key ending in :fingerprint',
        'fingerprint_unknown' => 'Signed in',

        'balance' => [
            'unit' => 'tokens',
            'one_token' => 'One token is one search.',
            'valid_until' => 'Balance valid until :date',
            'empty' => 'No balance left. Without tokens you cannot search — top up to carry on.',
            'low' => 'Your balance is running low.',
            'unknown' => 'We cannot read your balance right now. That is on us, not on you — please try again in a few minutes.',
            'orders_summary' => 'From :count top-ups, which expire one after another',
            'orders_heading' => 'Expiry dates',
            'order' => ':amount tokens until :date',
        ],

        'actions' => [
            'topup' => 'Top up balance',
            'search' => 'Go to search',
        ],

        'charge' => [
            'heading' => 'Top up balance',
            'lede' => 'One token is one search and costs one cent. All prices include VAT.',
            'tokens' => ':amount tokens',
            'price' => ':price €',
            'more' => 'All prices and payment methods',

            /**
             * Why no package is on offer right now. Three sentences for three
             * states, all three of which the old page had too — except that it
             * said "your key is already fully charged", which is not true: what
             * is full is not the balance but the number of open top-ups.
             */
            'blocked' => [
                'proxy' => 'You are browsing through one of our proxy sessions. Topping up is switched off while you do, for your own safety — a payment leads to a payment provider, and it should not see this session. Open this page without a proxy session to top up.',
                'full' => 'This key already carries three top-ups. As soon as the oldest one is used up or has expired, you can top up again.',
                'member' => 'You are a member of SUMA-EV and search at no further cost. You do not need a token package.',
            ],
        ],

        /**
         * The section the old page did not have: QR code, settings URL and the
         * transfer button sat there in one row, with not a sentence about what
         * they are for.
         */
        'save' => [
            'heading' => 'Secure your access',
            'text' => 'As long as this browser keeps the cookie, you stay signed in. If it loses it — a new device, cleared browsing data — your key is the only way back to your balance. Here it is, and here are three ways to take it with you.',

            /**
             * The key itself.
             *
             * It has to be here — the sign-in form asks for it first of all —
             * and it is collapsed, because this page gets photographed for
             * support tickets. The old page showed it large and always.
             */
            'key' => [
                'summary' => 'Show and copy key',
                'label' => 'Your key',
                'action' => 'Copy key',
                'hint' => '36 characters. This is what signs you in on any other device. Collapsed because this page often gets photographed — anyone who sees your key searches at your expense.',
            ],

            'qr' => [
                'label' => 'QR code',
                'alt' => 'QR code leading to your key',
                'action' => 'Save as image',
                'hint' => 'The image the sign-in form asks for. You can upload it there or photograph it with your camera.',
            ],

            'url' => [
                'label' => 'Bookmark',
                'action' => 'Copy URL',
                'hint' => 'Opening this URL restores the key along with this browser\'s search settings.',
            ],

            /**
             * The transfer dialog. The keymanager called it "generate login
             * code" — a label naming the means rather than the end, which is why
             * it never answered "how do I get MetaGer onto my phone?", even
             * though that is exactly what the button does.
             */
            'transfer' => [
                'label' => 'Another device',
                'action' => 'Sign in a device',
                'hint' => 'Shows a short code you type into the sign-in form on the other device — instead of copying out the whole key.',

                'title' => 'Sign in another device',
                'description' => 'Enter this code on the other device in the sign-in form, where the key would normally go.',
                'waiting' => 'Fetching code …',
                'note' => 'The code is valid for a single sign-in and only while it is shown here. Close this window once you have entered it.',
                'failed' => 'The code could not be fetched. Close the window and try again in a moment.',
                'close' => 'Close',
            ],
        ],

        /**
         * What still lives in the keymanager. A list at the foot rather than the
         * three equal tabs of before: hardly anybody has campaigns, and a third
         * tab claimed otherwise.
         */
        'more' => [
            'heading' => 'More',
            'orders' => 'Orders and invoices',
            'campaigns' => 'Voucher campaigns',
            'help' => 'Help with your key',
            'logout' => 'Sign out',
            // Signing out only clears the cookie. Someone who does not know
            // that will not click it — and someone who reads it as "delete
            // account" certainly will not.
            'logout_hint' => 'Removes the key from this browser. The balance stays on the key.',
        ],
    ],

    'empty' => [
        'message' => 'Your tokens are used up.',
        'action' => 'Top up now',
    ],
];
