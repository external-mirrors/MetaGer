<?php
return [
    'plugin' => 'Installera MetaGer',
    'plugin-title' => 'Lägg till MetaGer i din webbläsare',
    'key' => [
        'placeholder' => 'Ange din MetaGer-nyckel för att börja söka.',
        'tooltip' => [
            'nokey' => 'Skapa annonsfri sökning',
            'empty' => 'Token förbrukad. Ladda om nu.',
            'low' => 'Token snart förbrukad. Ladda om nu.',
            'full' => 'Annonsfri sökning aktiverad.',
        ],
    ],
    'placeholder' => 'MetaGer: Integritetsskyddad sökning och sökning',
    'searchbutton' => 'Starta MetaGer-sökning',
    'foki' => [
        'web' => 'Webb',
        'bilder' => 'Bilder',
        'nachrichten' => 'Nyheter',
        'science' => 'Vetenskap',
        'produkte' => 'Produkter',
        'maps' => 'Kartor',
    ],
    'adfree' => 'Använd MetaGer annonsfritt',
    'skip' => [
        'search' => 'Hoppa till inmatning av sökfråga',
        'navigation' => 'Hoppa till navigering',
        'fokus' => 'Hoppa till val av sökfokus',
    ],
    'lang' => 'wwitch språk',
    'searchreset' => 'radera sökfråga inmatning',
    'searchbar-replacement' => [
        'tagline' => 'Open source. Annonsfri. Anonym.',
        'message' => 'Din nyckel är din åtkomst – inget konto, ingen e-postadress. Endast ditt saldo hänger på den.',
        'first_time' => 'Första gången här?',
        'start' => 'Skapa en nyckel',
        // Swapped in by resources/js/accountBreadcrumb.js when this browser has
        // rendered a signed-in page before. Three strings, replacing three
        // elements in place — nothing appears, nothing moves.
        'welcome_back' => 'Välkommen tillbaka.',
        'welcome_back_message' => 'Du har varit inloggad på den här enheten tidigare. Logga in med samma nyckel – ditt saldo finns kvar.',
        'welcome_back_button' => 'Logga in igen',
        'have_key' => 'Logga in med min nyckel',
        'login' => 'Logga in',
        'key_error' => "Den inmatade nyckeln var inte giltig. Vänligen kontrollera inmatningen.",
        'login_code_error' => "Den angivna inloggningskoden var inte giltig. Tips: Inloggningskoder är endast giltiga när de är synliga på en annan enhet!",
        'payment_id_error' => "Du har angett ett betalnings-id som inte är en korrekt nyckel. Din nyckel är 36 tecken lång.",
        'new_key' => 'Ingen nyckel än?',
        'extension' => 'Håll dig inloggad och anonym med vårt webbtillägg',
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
        'title' => 'Sök och surfa på webben utan att bli övervakad',
        'description' => 'MetaGer respekterar din integritet och låter dig även besöka vilken webbplats som helst anonymt.',
        'advantages' => [
            'ads' => 'Inga annonser',
            'tracking' => 'Ingen spårning',
            'logging' => 'Ingen loggning',
            'compromise' => 'Inga kompromisser',
        ],
        'calltoaction' => 'Så fungerar det',
        'benefits' => [
            'browsing' => [
                'heading' => 'Inte bara anonym sökning – även anonym surfning',
                'description' => 'Med din MetaGer-nyckel kan du också öppna vilken webbplats som helst i en privat webbläsare som körs säkert på våra servrar, inte på din enhet. Webbplatser kan inte se vem du är eller varifrån du surfar, och allt raderas automatiskt när din session tar slut. Ingen installation, ingen konfiguration – bara öppna och sätt igång.',
                'fingerprinting' => 'Fingerprinting',
                'tracking' => 'Spårning',
            ],
            'ads' => [
                'heading' => 'Utan annonser',
                'description' => 'Annonser och integritet går sällan ihop. Därför finns det ingen som helst reklam hos MetaGer, så att vi kan skydda din integritet utan kompromisser.',
                'ads' => 'Reklam',
                'tracking' => 'Spårningslänkar',
            ],
            'logging' => [
                'heading' => 'Utan loggning',
                'description' => 'Att söka på internet lämnar vanligtvis ett spår av data efter sig. Vi behöver inte spara något av det: vår sökmotor är byggd så att spambekämpning inte kräver loggar. Du stöter inte heller på en enda captcha på vår sida, inte ens med VPN.',
                'logging' => 'Loggning',
            ],
            'compromise' => [
                'heading' => 'Utan kompromisser',
                'description' => 'I stället för ett konto kopplat till dina personuppgifter får du helt enkelt en slumpmässigt genererad nyckel, utan namn och utan e-postadress. Välj bland flera <a href=":linkPaymentMethods">betalningsmetoder</a>, inklusive helt anonym kontantbetalning. Med vår <a href=":linkApp">Android-app</a> eller vårt webbläsartillägg kan du till och med bevisa att dina sökningar förblir anonyma, med hjälp av <a href=":linkToken">anonyma tokens</a>.',
                'compromise' => 'Personuppgifter',
            ],
            'efficiency' => [
                'heading' => 'Sök effektivare',
                'description' => 'Hitta det du söker snabbare. När det är till hjälp lägger vi in tydliga djuplänkar, relevanta nyheter och videor direkt i sökresultaten. Vår bildsökning använder också ytterligare källor.',
            ],
        ],
        'howitworks' => [
            'heading' => 'Så fungerar det',
            'steps' => [
                [
                    'heading' => 'Nyckeln skapas automatiskt',
                    'description' => 'Din MetaGer-nyckel genereras automatiskt. Ingen registrering, inga personuppgifter behövs. Den är allt du behöver för att använda MetaGer.',
                ],
                [
                    'heading' => 'Aktivera din åtkomst',
                    'description' => 'En <a href=":linkCost">betalning</a> vid ett enda tillfälle fyller på din nyckel med saldo som vi kallar token. Det aktiverar annonsfri och spårningsfri sökning samt anonym surfning – inklusive alla nuvarande och framtida MetaGer-funktioner. Omkring 500 token (5 €) räcker vanligtvis i ungefär 2 månader.',
                    'membership' => 'Observera: medlemmar i vår ideella förening <a href="https://suma-ev.de" target="_blank">SUMA-EV</a> kan använda MetaGer utan extra kostnad. <a href=":linkMembership" target="_blank">Bli medlem nu</a>',
                ],
                [
                    'heading' => 'Använd MetaGer överallt',
                    'description' => 'Använd samma nyckel på hur många enheter du vill, eller dela den med vänner och familj. Öppna bara MetaGer på valfri enhet, ange din nyckel, så kan du söka – eller surfa anonymt.',
                ],
            ],
            'start' => 'Kom igång',
            'login' => 'Jag har redan en nyckel',
        ],
    ],
];
