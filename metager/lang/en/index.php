<?php
return [
    'skip' => [
        'search' => 'Skip to search query input',
        'navigation' => 'Skip to navigation',
        'fokus' => 'Skip to search focus selection',
    ],
    'lang' => 'wwitch language',
    'plugin' => 'Install MetaGer',
    'plugin-title' => 'Add MetaGer to your browser',
    'key' => [
        'placeholder' => 'Enter your MetaGer Key to start searching.',
        'tooltip' => [
            'nokey' => 'Set up ad-free search',
            'empty' => 'Token used up. Recharge now.',
            'low' => 'Token soon used up. Recharge now.',
            'full' => 'Ad-free search enabled.',
        ],
    ],
    'placeholder' => 'MetaGer: Privacy Protected Search & Find',
    'searchbutton' => 'Start MetaGer-Search',
    'searchreset' => 'delete search query input',
    'foki' => [
        'web' => 'Web',
        'bilder' => 'Images',
        'nachrichten' => 'News',
        'science' => 'Science',
        'produkte' => 'Products',
        'maps' => 'Maps'
    ],
    'adfree' => 'MetaGer ad-free',
    'searchbar-replacement' => [
        'tagline' => 'Open Source. Ad-Free. Anonymous.',
        'message' => 'Your key is your access – no account, no email address. Your balance and settings hang off it.',
        'have_key' => 'Log in with my key',
        'first_time' => 'First time here?',
        'start' => 'Set up a key',
        // Swapped in by resources/js/accountBreadcrumb.js when this browser has
        // rendered a signed-in page before. Three strings, replacing three
        // elements in place — nothing appears, nothing moves.
        'welcome_back' => 'Welcome back.',
        'welcome_back_message' => 'You have been logged in on this device before. Log in with the same key – your balance is still there.',
        'welcome_back_button' => 'Log in again',
        'new_key' => 'No key yet?',
        'extension' => 'Stay logged in and anonymous with our webextension',
        "key_error" => "The entered key was not valid. Please check the input.",
        "login_code_error" => "The entered login code was not valid. Hint: Login Codes are only valid while visible on another device!",
        "payment_id_error" => "You've entered a payment id which is not a correct key. Your key is 36 characters long.",
        "login" => "Log in",
    ],
    // The landing page shown to a visitor without a key: hero, "how it works",
    // and the five benefit cards. It came from the keymanager's own root page
    // (pass/views/index.ejs, pass/lang/*/index.json), which /keys used to serve
    // and which now redirects here.
    //
    // Placeholders are Laravel's :name, not i18next's {{name}}, and the links
    // are passed in from parts/landing/* so the locale prefix and the /keys
    // paths stay in one place.
    'landing' => [
        'title' => 'MetaGer: search and browse the web without being watched',
        'description' => 'MetaGer respects your privacy: no ads, no tracking, no logging. And now, you can browse any website anonymously too.',
        'advantages' => [
            'ads' => 'No ads',
            'tracking' => 'No tracking',
            'logging' => 'No logging',
            'compromise' => 'No compromises',
        ],
        'calltoaction' => 'How it works',
        'benefits' => [
            'browsing' => [
                'heading' => 'Not just anonymous search — anonymous browsing too',
                'description' => 'With your MetaGer key you can also open any website in a private browser that runs securely on our servers, not on your device. Websites can\'t see who you are or where you\'re browsing from, and everything is automatically deleted once your session ends. No installation, no setup — just open and go.',
                'fingerprinting' => 'Fingerprinting',
                'tracking' => 'Tracking',
            ],
            'ads' => [
                'heading' => 'No advertisements',
                'description' => 'Ads and privacy rarely mix. That\'s why there is no advertising at MetaGer whatsoever, so we can protect your privacy without compromise.',
                'ads' => 'Advertising',
                'tracking' => 'Tracking links',
            ],
            'logging' => [
                'heading' => 'No logging',
                'description' => 'Searching the internet usually leaves a trail of data behind. We don\'t need to keep any of it: our search engine is built so that fighting spam doesn\'t require logs. You also won\'t run into a single captcha on our site, even when using a VPN.',
                'logging' => 'Logging',
            ],
            'compromise' => [
                'heading' => 'No compromises',
                'description' => 'Instead of an account tied to your personal data, you simply get a randomly generated key, no name or email required. Choose from several <a href=":linkPaymentMethods">payment methods</a>, including fully anonymous cash payment. With our <a href=":linkApp">Android app</a> or browser extension, you can even prove that your searches stay anonymous using <a href=":linkToken">anonymous tokens</a>.',
                'compromise' => 'Personal data',
            ],
            'efficiency' => [
                'heading' => 'Search more efficiently',
                'description' => 'Find what you\'re looking for, faster. When helpful, we add clear deep links, relevant news and videos right into your search results. Our image search draws on additional sources too.',
            ],
        ],
        'howitworks' => [
            'heading' => 'How it works',
            'steps' => [
                [
                    'heading' => 'Get your free key',
                    'description' => 'Your MetaGer key is generated automatically. No sign-up, no personal details needed. It\'s the only thing you need to use MetaGer.',
                ],
                [
                    'heading' => 'Activate your access',
                    'description' => 'A one-time <a href=":linkCost">payment</a> adds credit to your key, which we call token. This activates ad-free, tracking-free search and anonymous browsing, plus all current and future MetaGer features. About 500 token (€5) usually lasts around 2 months.',
                    'membership' => 'Note: members of our non-profit association <a href="https://suma-ev.de" target="_blank">SUMA-EV</a> can use MetaGer at no extra cost. <a href=":linkMembership" target="_blank">Become a member now</a>',
                ],
                [
                    'heading' => 'Use MetaGer everywhere',
                    'description' => 'Use the same key on as many devices as you like, or share it with friends and family. Just open MetaGer on any device, enter your key, and you\'re ready to search — or browse anonymously.',
                ],
            ],
            'start' => 'Get started',
            'login' => 'I already have a key',
        ],
    ],
];
