<?php
return [
    'title' => 'MetaGer - Hjälp',
    'backarrow' => 'Tillbaka',
    'tor' => [
        'title' => 'Dold tjänst för TOR <a title="For easy help, click here" href="/hilfe/easy-language/privacy-protection#eh-torhidden" ><img class="easy-help-icon lm-only" src="/img/help-questionmark-icon-lm.svg"/><img class="easy-help-icon dm-only" src="/img/help-questionmark-icon-dm.svg"/></a>',
        '2' => 'Du kan komma åt MetaGer i Tor-webbläsaren på: http://metagerv65pwclop2rsfzg4jwowpavpwd6grhhlvdgsswvo6ii4akgyd.onion .',
        '1' => 'I många år har MetaGer dolt och inte lagrat IP-adresser. Dessa adresser är dock tillfälligt synliga på MetaGer-servern medan en sökning pågår: om MetaGer skulle äventyras skulle en angripare kunna läsa och lagra dina adresser. För att uppfylla de högsta säkerhetskraven driver vi en MetaGer-instans i Tor-nätverket: MetaGer TOR Hidden Service - tillgänglig via: <a href="/tor/" target="_blank" rel="noopener">https://metager.de/tor/</a>. För att använda den behöver du en speciell webbläsare, som du kan ladda ner från <a href="https://www.torproject.org/" target="_blank" rel="noopener">https://www.torproject.org/</a>.',
    ],
    'proxy' => [
        'title' => 'Öppna anonymt (SafeBrowse) <a title="For easy help, click here" href="/hilfe/easy-language/privacy-protection#eh-proxy" ><img class="easy-help-icon lm-only" src="/img/help-questionmark-icon-lm.svg"/><img class="easy-help-icon dm-only" src="/img/help-questionmark-icon-dm.svg"/></a>',
        'intro' => 'Med ”Öppna anonymt” besöker du en webbplats utan att den någonsin får se din enhet. Sidan öppnas i en webbläsare som körs på våra servrar: SafeBrowse. Webbplatsen har bara att göra med den webbläsaren och får varken veta din IP-adress eller något om din enhet, din webbläsare eller dina cookies.',
        'use' => [
            'title' => 'Så öppnar du en sida anonymt',
            'text' => [
                'Klicka på ”Öppna anonymt” (sköldikonen) vid ett resultat. Sidan öppnas i en ny flik.',
                'Om du vill öppna varje resultat anonymt slår du på ”Öppna resultat anonymt” i inställningarna. Då går varje länk i ett resultat via SafeBrowse, och med ”Öppna direkt” besöker du en sida utan SafeBrowse.',
            ],
        ],
        'session' => [
            'title' => 'Sessionen',
            'text' => [
                'Det första resultatet du öppnar anonymt startar en session. Varje följande öppnas i samma session, i en egen flik. Där surfar du vidare som vanligt: följer länkar, fyller i formulär, skriver en ny adress eller ett sökord i adressfältet. Du förblir skyddad under tiden.',
                'En session kostar tokens från din MetaGer-nyckel; hur många för hur lång tid visas innan den startar. Så länge du använder sessionen förlängs den automatiskt. När du slutar använda den avslutas den av sig själv kort därefter, eller så avslutar du den med ”Stäng sessionen”.',
                'När sessionen tar slut raderas webbläsaren på våra servrar med allt som fanns i den: besökta sidor, cookies, inloggningar och nedladdade filer. Nästa session börjar med en ny, tom webbläsare. Spara därför nedladdade filer på din enhet under ”Nedladdningar” innan dess.',
            ],
        ],
        'views' => [
            'title' => 'Två vyer',
            'text' => [
                '”Sida” visar en kopia av webbplatsen utan dess skript. Du läser, scrollar, söker och kopierar i den som på vilken sida som helst; dina klick och inmatningar går till webbläsaren på våra servrar. ”Hela webbläsaren” visar den webbläsaren som en bild, med alla dess flikar. Där fungerar allt, även videor och sidor som ”Sida” inte kan visa. Du växlar mellan dem i fältet längst upp.',
            ],
        ],
        'nojs' => [
            'title' => 'Utan JavaScript',
            'text' => [
                'Även när JavaScript är avstängt i din webbläsare öppnar ”Öppna anonymt” sidan i SafeBrowse. Du ser då en kopia av sidan där länkar, knappar och formulär fungerar. Den laddas om efter varje klick men ändras inte av sig själv. Videor, sidans dialogrutor och uppladdning av filer fungerar inte på det här sättet. Om din webbläsare blockerar cookies hamnar resultaten du öppnar från en resultatsida ändå i samma session, även när du söker vidare därifrån.',
            ],
        ],
        'limits' => [
            'title' => 'Vad ”Öppna anonymt” inte gör',
            'text' => [
                'Webbplatsen känner inte igen dig på din enhet, men väl på det du själv berättar för den: om du loggar in eller anger personuppgifter vet den vem du är. Om du inte heller vill visa dig för MetaGer, använd MetaGer via Tor (se ovan).',
            ],
        ],
    ],
    'content' => [
        'title' => 'Tvivelaktigt innehåll / Skydd av ungdomar <a title="For easy help, click here" href="/help/easy-language/privacy-protection#eh-content" ><img class="easy-help-icon lm-only" src="/img/help-questionmark-icon-lm.svg"/><img class="easy-help-icon dm-only" src="/img/help-questionmark-icon-dm.svg"/></a>',
        'explanation' => [
            '1' => 'Jag har fått "träffar" som jag inte bara tycker är irriterande utan som också innehåller, enligt min mening, olagligt innehåll!',
            '2' => 'Om du hittar något på Internet som du anser vara olagligt eller skadligt för minderåriga, kan du kontakta <a href="mailto:hotline@jugendschutz.net" target="_blank" rel="noopener">hotline@jugendschutz.net</a> via e-post eller besöka <a href="http://www.jugendschutz.net/" target="_blank" rel="noopener">www.jugendschutz.net</a> och fylla i det klagomålsformulär som finns där. Det är bra att ge en kort anteckning om vad du anser vara otillåtet och hur du kom över detta innehåll. Du kan också rapportera tvivelaktigt innehåll direkt till oss. Skicka då ett e-postmeddelande till vår ungdomsskyddsombud (<a href="mailto:jugendschutz@metager.de" target="_blank" rel="noopener">jugendschutz@metager.de</a>).',
        ],
    ],
    'maps' => [
        'title' => 'MetaGer-kartor',
        '1' => 'Att värna om integriteten i en tid av globala datajättar har också lett oss till att utveckla <a href="https://maps.metager.de" target="_blank">https://maps.metager.de</a>: den (såvitt vi vet) enda ruttplaneraren som erbjuder full funktionalitet via en webbläsare och app utan att lagra användarens position. Allt detta är verifierbart eftersom vår programvara är öppen källkod. För att använda maps.metager.de rekommenderar vi vår snabba appversion. Du kan ladda ner våra appar från <a href="/app" target="_blank">här</a> (eller naturligtvis också från Play Store).',
        '2' => 'Denna kartfunktion kan också nås från MetaGer-sökningen (och vice versa). När du har sökt efter en term i MetaGer kommer du att se ett nytt sökfokus "Maps" i det övre högra hörnet. Om du klickar på den kommer du till en motsvarande karta.',
        '3' => 'När kartan laddas visas de punkter (POI = Points of Interest) som MetaGer har hittat och som också listas i den högra kolumnen. Vid zoomning anpassar sig denna lista till kartutsnittet. Om du håller muspekaren över en markör på kartan eller i listan markeras motsvarande objekt. Klicka på "Detaljer" för att få mer information om den punkten från databasen nedan.',
    ],
    'easy-help' => 'Genom att klicka på symbolen <a title="For easy help, click here" href="/hilfe/easy-language/privacy-protection" ><img class="easy-help-icon lm-only" src="/img/help-questionmark-icon-lm.svg"/><img class="easy-help-icon dm-only" src="/img/help-questionmark-icon-dm.svg"/></a> , kommer du till en förenklad version av hjälpen.',
    'privacy' => [
        'title' => "Anonymitet och datasäkerhet",
        '1' => 'Spårning av cookies, sessions-ID och IP-adresser <a title="For easy help, click here" href="/hilfe/easy-language/privacy-protection#eh-tracking" ><img class="easy-help-icon lm-only" src="/img/help-questionmark-icon-lm.svg"/><img class="easy-help-icon dm-only" src="/img/help-questionmark-icon-dm.svg"/></a>',
        '2' => 'Ingen av dessa används, lagras, behålls eller behandlas på annat sätt här på MetaGer (undantag: kortvarig lagring för skydd mot hackning och botattacker). Eftersom vi anser att detta ämne är extremt viktigt har vi också skapat sätt att hjälpa dig att uppnå högsta säkerhetsnivå: MetaGer TOR Hidden Service och vår anonyma webbläsare.',
        '3' => "Mer information hittar du nedan. Funktionerna är tillgängliga under \"Tjänster\" i navigeringsfältet.",
    ],
];
