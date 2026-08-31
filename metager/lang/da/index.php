<?php
return [
    'plugin' => 'Installer MetaGer',
    'plugin-title' => 'Tilføj MetaGer til din browser',
    'key' => [
        'placeholder' => 'Indtast din MetaGer-nøgle for at begynde at søge.',
        'tooltip' => [
            'nokey' => 'Opsæt annoncefri søgning',
            'empty' => 'Token brugt op. Genoplad nu.',
            'low' => 'Token snart opbrugt. Genoplad nu.',
            'full' => 'Annoncefri søgning aktiveret.',
        ],
    ],
    'placeholder' => 'MetaGer: Privatlivsbeskyttet søgning og find',
    'searchbutton' => 'Start MetaGer-søgning',
    'foki' => [
        'web' => 'Web',
        'bilder' => 'Billeder',
        'nachrichten' => 'Nyheder',
        'science' => 'Videnskab',
        'produkte' => 'Produkter',
        'maps' => 'Kort',
    ],
    'adfree' => 'Brug MetaGer uden reklamer',
    'skip' => [
        'search' => 'Spring til indtastning af søgeforespørgsel',
        'navigation' => 'Spring til navigation',
        'fokus' => 'Spring til valg af søgefokus',
    ],
    'lang' => 'wwitch-sprog',
    'searchreset' => 'Slet input til søgeforespørgsel',
    'searchbar-replacement' => [
        'tagline' => 'Open source. Reklamefri. Anonym.',
        'message' => 'Din nøgle er din adgang – ingen konto, ingen e-mailadresse. Kun din saldo hænger på den.',
        'first_time' => 'Første gang her?',
        'start' => 'Opret en nøgle',
        // Swapped in by resources/js/accountBreadcrumb.js when this browser has
        // rendered a signed-in page before. Three strings, replacing three
        // elements in place — nothing appears, nothing moves.
        'welcome_back' => 'Velkommen tilbage.',
        'welcome_back_message' => 'Du har været logget ind på denne enhed før. Log ind med den samme nøgle – din saldo er der stadig.',
        'welcome_back_button' => 'Log ind igen',
        'have_key' => 'Log ind med min nøgle',
        'login' => 'Log ind',
        'key_error' => "Den indtastede nøgle var ikke gyldig. Kontroller venligst indtastningen.",
        'login_code_error' => "Den indtastede login-kode var ikke gyldig. Tip: Login-koder er kun gyldige, når de er synlige på en anden enhed!",
        'payment_id_error' => "Du har indtastet et betalingsid, som ikke er en korrekt nøgle. Din nøgle er 36 tegn lang.",
        'new_key' => 'Ingen nøgle endnu?',
        'extension' => 'Forbliv logget ind og anonym med vores webudvidelse',
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
        'title' => 'Søg og surf på nettet uden at blive overvåget',
        'description' => 'MetaGer respekterer dit privatliv og lader dig også besøge enhver hjemmeside anonymt.',
        'advantages' => [
            'ads' => 'Ingen reklamer',
            'tracking' => 'Ingen sporing',
            'logging' => 'Ingen logning',
            'compromise' => 'Ingen kompromiser',
        ],
        'calltoaction' => 'Sådan fungerer det',
        'benefits' => [
            'browsing' => [
                'heading' => 'Ikke kun anonym søgning – også anonym surfing',
                'description' => 'Med din MetaGer-nøgle kan du også åbne enhver hjemmeside i en privat browser, der kører sikkert på vores servere – ikke på din enhed. Hjemmesider kan ikke se, hvem du er, eller hvorfra du surfer, og alt slettes automatisk, når din session slutter. Ingen installation, ingen opsætning – bare åbn og gå i gang.',
                'fingerprinting' => 'Fingerprinting',
                'tracking' => 'Sporing',
            ],
            'ads' => [
                'heading' => 'Uden reklamer',
                'description' => 'Reklamer og privatliv går sjældent hånd i hånd. Derfor er der overhovedet ingen reklamer hos MetaGer, så vi kan beskytte dit privatliv uden kompromiser.',
                'ads' => 'Reklame',
                'tracking' => 'Sporingslinks',
            ],
            'logging' => [
                'heading' => 'Uden logning',
                'description' => 'Søgninger på internettet efterlader normalt et spor af data. Vi har ikke brug for at gemme noget af det: vores søgemaskine er bygget sådan, at bekæmpelse af spam ikke kræver logs. Du støder heller ikke på en eneste captcha på vores side, selv når du bruger en VPN.',
                'logging' => 'Logning',
            ],
            'compromise' => [
                'heading' => 'Uden kompromiser',
                'description' => 'I stedet for en konto knyttet til dine personlige oplysninger får du blot en tilfældigt genereret nøgle – uden navn og uden e-mailadresse. Vælg mellem flere <a href=":linkPaymentMethods">betalingsmetoder</a>, herunder helt anonym kontant betaling. Med vores <a href=":linkApp">Android-app</a> eller browserudvidelse kan du endda bevise, at dine søgninger forbliver anonyme, ved hjælp af <a href=":linkToken">anonyme tokens</a>.',
                'compromise' => 'Personlige oplysninger',
            ],
            'efficiency' => [
                'heading' => 'Søg mere effektivt',
                'description' => 'Find det, du leder efter, hurtigere. Når det er nyttigt, indsætter vi overskuelige dybe links, relevante nyheder og videoer direkte i søgeresultaterne. Vores billedsøgning trækker også på yderligere kilder.',
            ],
        ],
        'howitworks' => [
            'heading' => 'Sådan fungerer det',
            'steps' => [
                [
                    'heading' => 'Nøglen oprettes automatisk',
                    'description' => 'Din MetaGer-nøgle bliver genereret automatisk. Ingen tilmelding, ingen personlige oplysninger nødvendige. Den er alt, hvad du skal bruge for at benytte MetaGer.',
                ],
                [
                    'heading' => 'Aktivér din adgang',
                    'description' => 'En enkelt <a href=":linkCost">betaling</a> tilføjer kredit til din nøgle, som vi kalder token. Det aktiverer reklamefri og sporingsfri søgning samt anonym surfing – inklusive alle nuværende og fremtidige MetaGer-funktioner. Omkring 500 token (5 €) rækker normalt i cirka 2 måneder.',
                    'membership' => 'Bemærk: medlemmer af vores almennyttige forening <a href="https://suma-ev.de" target="_blank">SUMA-EV</a> kan bruge MetaGer uden yderligere omkostninger. <a href=":linkMembership" target="_blank">Bliv medlem nu</a>',
                ],
                [
                    'heading' => 'Brug MetaGer overalt',
                    'description' => 'Brug den samme nøgle på så mange enheder, du vil, eller del den med venner og familie. Åbn blot MetaGer på en vilkårlig enhed, indtast din nøgle, og så kan du søge – eller surfe anonymt.',
                ],
            ],
            'start' => 'Kom i gang',
            'login' => 'Jeg har allerede en nøgle',
        ],
    ],
];
