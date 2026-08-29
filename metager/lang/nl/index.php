<?php
return [
    'plugin' => 'MetaGer installeren',
    'plugin-title' => 'MetaGer aan uw browser toevoegen',
    'key' => [
        'placeholder' => 'Voer je MetaGer Key in om te beginnen zoeken.',
        'tooltip' => [
            'nokey' => 'Advertentievrij zoeken instellen',
            'empty' => 'Fiche opgebruikt. Nu herladen.',
            'low' => 'Muntje snel opgebruikt. Nu herladen.',
            'full' => 'Zoeken zonder advertenties ingeschakeld.',
        ],
    ],
    'foki' => [
        'web' => 'Web',
        'bilder' => 'Afbeeldingen',
        'nachrichten' => 'Nieuws',
        'science' => 'Wetenschap',
        'produkte' => 'Producten',
        'maps' => 'Kaarten',
    ],
    'placeholder' => 'MetaGer: Beschermd zoeken en vinden',
    'searchbutton' => 'MetaGer zoeken starten',
    'adfree' => 'Gebruik MetaGer reclamevrij',
    'skip' => [
        'search' => 'Doorgaan naar invoer zoekopdracht',
        'navigation' => 'Ga naar navigatie',
        'fokus' => 'Doorgaan naar selectie van zoekfocus',
    ],
    'lang' => 'Witch-taal',
    'searchreset' => 'invoer zoekopdracht verwijderen',
    'searchbar-replacement' => [
        'tagline' => 'Open source. Advertentievrij. Anoniem.',
        'message' => 'Je sleutel is je toegang – geen account, geen e-mailadres. Je saldo en instellingen hangen eraan.',
        'first_time' => 'Voor het eerst hier?',
        'start' => 'Een sleutel instellen',
        // Swapped in by resources/js/accountBreadcrumb.js when this browser has
        // rendered a signed-in page before. Three strings, replacing three
        // elements in place — nothing appears, nothing moves.
        'welcome_back' => 'Welkom terug.',
        'welcome_back_message' => 'Je was op dit apparaat al eens aangemeld. Meld je aan met dezelfde sleutel – je saldo staat er nog.',
        'welcome_back_button' => 'Opnieuw aanmelden',
        'have_key' => 'Aanmelden met mijn sleutel',
        'login' => 'Inloggen',
        'key_error' => "De ingevoerde sleutel is ongeldig. Controleer de invoer.",
        'login_code_error' => "De ingevoerde inlogcode was niet geldig. Tip: Inlogcodes zijn alleen geldig als ze zichtbaar zijn op een ander apparaat!",
        'payment_id_error' => "Je hebt een betalings-id ingevoerd die geen correcte sleutel is. Je sleutel is 36 tekens lang.",
        'new_key' => 'Nog geen sleutel?',
        'extension' => 'Blijf ingelogd en anoniem met onze webextensie',
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
        'title' => 'MetaGer: zoeken en surfen op het web zonder bekeken te worden',
        'description' => 'MetaGer respecteert je privacy: zonder advertenties, zonder tracking, zonder logging. En nu kun je ook elke website anoniem bezoeken.',
        'advantages' => [
            'ads' => 'Geen advertenties',
            'tracking' => 'Geen tracking',
            'logging' => 'Geen logging',
            'compromise' => 'Geen compromissen',
        ],
        'calltoaction' => 'Hoe het werkt',
        'benefits' => [
            'browsing' => [
                'heading' => 'Niet alleen anoniem zoeken – ook anoniem surfen',
                'description' => 'Met je MetaGer-sleutel kun je ook elke website openen in een privébrowser die veilig op onze servers draait, niet op je eigen apparaat. Websites kunnen niet zien wie je bent of waarvandaan je surft, en na afloop van je sessie wordt alles automatisch gewist. Geen installatie, geen instellingen – gewoon openen en beginnen.',
                'fingerprinting' => 'Fingerprinting',
                'tracking' => 'Tracking',
            ],
            'ads' => [
                'heading' => 'Zonder advertenties',
                'description' => 'Advertenties en privacy gaan zelden samen. Daarom is er bij MetaGer geen enkele vorm van reclame, zodat we je privacy zonder compromissen kunnen beschermen.',
                'ads' => 'Reclame',
                'tracking' => 'Trackinglinks',
            ],
            'logging' => [
                'heading' => 'Zonder logging',
                'description' => 'Zoeken op internet laat normaal gesproken een spoor van gegevens achter. Wij hoeven daar niets van te bewaren: onze zoekmachine is zo gebouwd dat we voor spambestrijding geen logs nodig hebben. Je komt op onze site ook geen enkele captcha tegen, zelfs niet met een VPN.',
                'logging' => 'Logging',
            ],
            'compromise' => [
                'heading' => 'Zonder compromissen',
                'description' => 'In plaats van een account met je persoonsgegevens krijg je gewoon een willekeurig gegenereerde sleutel, zonder naam en zonder e-mailadres. Kies uit verschillende <a href=":linkPaymentMethods">betaalmethoden</a>, waaronder volledig anoniem contant betalen. Met onze <a href=":linkApp">Android-app</a> of browserextensie kun je zelfs bewijzen dat je zoekopdrachten anoniem blijven, dankzij <a href=":linkToken">anonieme tokens</a>.',
                'compromise' => 'Persoonsgegevens',
            ],
            'efficiency' => [
                'heading' => 'Efficiënter zoeken',
                'description' => 'Vind sneller wat je zoekt. Waar het nuttig is, voegen we overzichtelijke deeplinks, relevant nieuws en video\'s direct toe aan de zoekresultaten. Ook onze afbeeldingenzoekfunctie put uit extra bronnen.',
            ],
        ],
        'howitworks' => [
            'heading' => 'Hoe het werkt',
            'steps' => [
                [
                    'heading' => 'Ontvang je gratis sleutel',
                    'description' => 'Je MetaGer-sleutel wordt automatisch gegenereerd. Geen registratie, geen persoonsgegevens nodig. Het is het enige wat je nodig hebt om MetaGer te gebruiken.',
                ],
                [
                    'heading' => 'Activeer je toegang',
                    'description' => 'Met een eenmalige <a href=":linkCost">betaling</a> zet je tegoed op je sleutel, dat wij token noemen. Daarmee schakel je reclamevrij en trackingvrij zoeken en anoniem surfen vrij – inclusief alle huidige en toekomstige MetaGer-functies. Ongeveer 500 token (€ 5) is meestal genoeg voor zo\'n 2 maanden.',
                    'membership' => 'Let op: leden van onze non-profitvereniging <a href="https://suma-ev.de" target="_blank">SUMA-EV</a> kunnen MetaGer zonder extra kosten gebruiken. <a href=":linkMembership" target="_blank">Word nu lid</a>',
                ],
                [
                    'heading' => 'Gebruik MetaGer overal',
                    'description' => 'Gebruik dezelfde sleutel op zo veel apparaten als je wilt, of deel hem met vrienden en familie. Open MetaGer gewoon op een willekeurig apparaat, voer je sleutel in en je kunt zoeken – of anoniem surfen.',
                ],
            ],
            'start' => 'Aan de slag',
            'login' => 'Ik heb al een sleutel',
        ],
    ],
];
