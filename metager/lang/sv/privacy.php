<?php
return [
    'contexts' => [
        'suma' => [
            'title' => 'Användning av webbplatsen <a href="https://suma-ev.de">suma-ev.de</a>',
            'description' => 'Vid besök på webbplatser som tillhör domänen "suma-ev.de" samlas följande uppgifter in och lagras i upp till en vecka:',
            'function' => 'Vid besök på webbplatser som tillhör domänen "suma-ev.de" samlas följande uppgifter in och lagras i upp till en vecka:',
            'other' => 'På de andra webbplatserna inom våra domäner behandlar vi endast de uppgifter som samlats in för att besvara förfrågningar och inom ramen för de andra punkterna i denna dataskyddsdeklaration.',
            'startpage' => 'På startsidan för vår MetaGer-tjänst använder vi den användaragent som du har överfört för att visa dig lämpliga installationsinstruktioner för plug-in för din webbläsare.',
        ],
        'title' => 'Inkommande data efter kontext',
        'metager' => [
            'title' => 'Användning av webbsökmotorn MetaGer',
            'description' => 'När du använder vår webbsökmotor MetaGer via dess webbformulär eller via dess OpenSearch-gränssnitt genereras följande data:',
            'query' => 'Som en integrerad del av metasökningen överförs sökfrågan till våra partner för att erhålla sökresultat som visas på resultatsidan. De mottagna resultaten, inklusive söktermen, sparas för visning under några timmar.',
            'preferences' => 'Vi använder dessa uppgifter (t.ex. språkinställningar) för att besvara respektive sökfråga. Vi lagrar en del av dessa uppgifter på en icke-personlig basis för statistiska ändamål.',
            'suggest' => 'Om sökförslag är aktiverade för adressfältet i din webbläsare (valfritt) måste vi lagra dina förslagsinställningar på vår server. Nyckeln kommer att vara en sammanhängande sträng av din IP-adress, din useragent och din webbläsares accept-language header som är sha1-hashad. Om inställningen är avaktiverad (standard) lagras ingenting.',
        ],
        'contact' => [
            'title' => 'Användning av kontaktformuläret',
            'description' => 'När du använder MetaGers kontaktformulär genereras följande uppgifter, som vi lagrar för referensändamål upp till 2 månader efter det att din förfrågan har slutförts:',
            'contact' => 'Kommer att lagras för referensändamål upp till 2 månader efter att din begäran har slutförts.',
        ],
        'donate' => [
            'title' => 'Användning av donationsformuläret',
            'description' => 'Följande uppgifter som överförs i donationsformuläret kommer att lagras i 2 månader för bearbetning:',
            'contact' => 'Vi använder dessa uppgifter uteslutande för eventuella förfrågningar och vidarebefordrar dem under inga omständigheter till tredje part.',
            'payment' => 'Betalningsuppgifterna kommer endast att användas för att behandla donationen och kommer inte att vidarebefordras till tredje part under några omständigheter. Av skatteskäl är vi skyldiga att spara och sparar därför dessa uppgifter i 10 år. Därefter raderas de automatiskt och kommer inte att behandlas vidare.',
            'message' => 'Det meddelande du anger här kommer att överföras till oss och beaktas vid behandlingen av din donation.',
        ],
        'key' => [
            'title' => 'Checkout MetaGer nyckel',
            'contact' => 'Vi använder dessa uppgifter uteslutande för eventuella förfrågningar eller för fakturering och vidarebefordrar dem under inga omständigheter till tredje part.',
            'payment' => 'Betalningsuppgifterna kommer endast att användas för att behandla donationen och kommer inte att vidarebefordras till tredje part under några omständigheter. Av skatteskäl är vi skyldiga att spara och sparar därför dessa uppgifter i 10 år. Därefter raderas de automatiskt och kommer inte att behandlas vidare.',
        ],
        'newsletter' => [
            'title' => 'Registrera dig för SUMA-EV:s nyhetsbrev',
            'description' => 'För att hålla dig informerad om våra aktiviteter erbjuder vi ett nyhetsbrev via e-post. Vi lagrar följande uppgifter tills du avregistrerar dig:',
            'contact' => 'Vi använder dessa uppgifter uteslutande för att skicka vårt nyhetsbrev till dig och vidarebefordrar dem under inga omständigheter till tredje part.',
        ],
        'maps' => [
            'title' => 'Användning av Maps.MetaGer.de',
            'description' => 'Vid användning av karttjänsten MetaGer genereras följande data:',
            'ip' => 'Vi använder din IP-adress för att uppskatta en bra startplats att fokusera kartan på inledningsvis. För att göra detta behandlas din IP-adress lokalt. Resultaten lagras inte någonstans och kommer att raderas omedelbart efter din begäran.',
        ],
        'proxy' => [
            'title' => 'Användning av anonymiserande proxy',
            'description' => 'När du använder den anonymiserande proxyn genereras följande data:',
        ],
        'quote' => [
            'title' => 'Användning av citatsökning',
            'description' => 'Den sökterm som anges används för att söka efter resultat i citatdatabasen. Till skillnad från webbsökningar med MetaGer är det inte nödvändigt att vidarebefordra sökordet till tredje part eftersom citeringsdatabasen finns på vår server. Andra uppgifter sparas eller överförs inte.',
        ],
        'asso' => [
            'title' => 'Användning av associatorn',
            'description' => 'Associatorn använder söktermen för att fastställa och visa de termer som är associerade med den. Övriga data sparas eller överförs inte.',
        ],
        'mapsapp' => [
            'title' => 'Användning av MetaGer-appen',
            'description' => 'Att använda MetaGer-appen är detsamma som att använda MetaGer via en webbläsare.',
        ],
        'plugin' => [
            'title' => 'Användning av MetaGer-plugin',
            'description' => 'Vid användning av MetaGer-plugin genereras följande data:',
        ],
        'membership' => [
            'title' => 'Att ansöka om och hantera ett SUMA-EV-medlemskap',
            'description' => 'När du ansöker om medlemskap i SUMA-EV, och under hela din medlemsperiod, samlas följande uppgifter in:',
            'contact' => 'För privatpersoner: namn och e-postadress; för organisationer: organisationens namn, dess ungefärliga storlek samt namn och e-postadress till en kontaktperson. Postadress är frivillig och används, om den anges, framför allt för att utfärda ett donationskvitto. Vi lämnar under inga omständigheter ut dessa uppgifter till tredje part.',
            'payment' => 'Vi använder dina betalningsuppgifter uteslutande för att regelbundet dra in din medlemsavgift. Hur vi behandlar uppgifterna, vem som tar del av dem och hur länge vi lagrar dem beskrivs under ”Betalningshantering”.',
            'reduction' => 'Om du ansöker om nedsatt medlemsavgift behandlar vi de handlingar du laddar upp (t.ex. ett utdrag över överföringar) uteslutande för att kontrollera att du har rätt till nedsättningen. Sådana handlingar kan innehålla särskilt känsliga uppgifter enligt artikel 9 i GDPR; vi lagrar dem därför endast så länge som denna kontroll kräver och vidarebefordrar dem inte till tredje part.',
            'crm' => 'För att hantera medlemskap använder vi vårt eget medlemsadministrationssystem, som också drivs av SUMA-EV. De uppgifter som anges ovan lagras där under hela din medlemsperiod och därefter under de lagstadgade bevarandeperioderna. Ditt medlemskap är dessutom kopplat till en MetaGer-nyckel, genom vilken din medlemsavgift krediteras som användarkredit för MetaGer (se ”Kassa – MetaGer-nyckel”).',
            'cookies' => 'Ansökningsformuläret och medlemsportalen placerar inga cookies när du bara öppnar dem. Först när du skickar in ett formulär eller loggar in på medlemsportalen placerar vi en tekniskt nödvändig sessionscookie, så att vi till exempel kan visa dig inmatningsfel eller se till att du förblir inloggad. Den upphör att gälla efter två timmars inaktivitet och raderas när du loggar ut. Om du öppnar formuläret via en länk som anger ett annat språk än det som är inställt i din webbläsare, lagrar vi det språket i en cookie som raderas när du stänger webbläsaren. Den rättsliga grunden är § 25(2) nr 2 i TDDDG.',
        ],
        'payments' => [
            'title' => 'Betalningshantering',
            'description' => 'Betalningar till SUMA-EV – för närvarande medlemsavgifter, i framtiden även donationer och MetaGer-nycklar – hanteras via vårt eget betalningssystem, som också drivs av SUMA-EV. Följande uppgifter genereras:',
            'ip' => 'Endast vid betalning via SEPA-autogiro: som en del av registreringen av ditt autogiromandat (se nedan). I övrigt sparas uppgiften inte.',
            'useragent' => 'Endast vid betalning via SEPA-autogiro: som en del av registreringen av ditt autogiromandat (se nedan). I övrigt sparas uppgiften inte.',
            'contact' => 'Vi får ditt namn och din e-postadress från den tjänst du betalar för (t.ex. medlemsansökan). Vi använder dessa uppgifter för att skicka e-postmeddelanden om din betalning, till exempel uppgifter om banköverföring, betalningslänkar eller betalningspåminnelser.',
            'payment' => 'Beroende på betalningssätt, kontoinnehavare och IBAN, en hänvisning till ditt PayPal-konto eller ditt kort hos respektive leverantör, samt belopp, tidpunkt och status för varje betalning.',
            'methods' => 'Vi hanterar SEPA-autogiro och banköverföringar själva; autogirouppdragen skickas till vår bank med uppgifter om kontoinnehavare, IBAN, belopp och fullmaktsreferens. När du betalar med PayPal anger du dina uppgifter direkt hos PayPal (Europe) S.à r.l. et Cie, S.C.A.; först när du väljer PayPal som betalningsmetod laddar betalningssidan in programkod från PayPal, vilket ger PayPal din IP-adress och gör det möjligt för dem att sätta sina egna cookies. När du betalar med kort eller Wero anger du dina uppgifter direkt hos VR Payment GmbH. Vi får aldrig tillgång till kortnummer eller inloggningsuppgifter för PayPal eller Wero, utan endast en referens som gör det möjligt för oss att dra in återkommande avgifter. Den rättsliga grunden är artikel 6.1 b i GDPR.',
            'mandate' => 'Om du ger oss ett SEPA-autogiromandat online sparar vi uppgifter om mandatets innehåll (kontoinnehavare, IBAN, mandatreferens, belopp och intervall samt den mandattext som visades för dig) tillsammans med tidpunkten för när mandatet gavs, din IP-adress och din användaragent. Vi behöver denna dokumentation för att kunna bevisa för vår bank att du har gett fullmakten om en autogirobetalning bestrids; endast i det fallet visar vi den för banken. Den rättsliga grunden är artikel 6.1 b i GDPR och vårt berättigade intresse av att kunna bevisa fullmakten (artikel 6.1 f i GDPR).',
            'cookies' => 'Betalningssidorna i sig sätter inga cookies när du öppnar dem. Endast om dina uppgifter är ofullständiga eller ogiltiga sätter vi tillfälligt en tekniskt nödvändig sessionscookie för att visa dig felmeddelandena; den raderas så snart felmeddelandena har visats (25 § andra stycket nr 2 i TDDDG).',
            'retention' => 'Om en betalning inte går igenom på grund av att du avbryter den raderar vi ditt namn och din e-postadress en vecka efter att betalningsprocessen har löpt ut. Om till exempel en medlemsansökan avslås innan någon betalning har mottagits raderar vi dina betalningsuppgifter och fullmaktsregistret omedelbart. Alla övriga betalningsuppgifter och fullmaktsregister sparas så länge som betalningsförhållandet består och därefter under de lagstadgade bevarandeperioderna (upp till 10 år).',
        ],
    ],
    'description' => [
        'ip' => [
            'example_full' => 'Exempel (fullständig IP-adress)',
            'example_partial' => 'Exempel (endast de två första blocken)',
            'title' => 'Internetprotokolladress',
            'description' => 'Internetprotokolladressen (nedan kallad IP) är obligatorisk för att kunna använda webbtjänster som MetaGer. Denna IP-adress, i kombination med ett datum - liknande ett telefonnummer - identifierar tydligt en Internetåtkomst och dess ägare. I allmänhet är de tre första (av totalt fyra) blocken i ett IP inte personliga. Om de bakre blocken av IP förkortas, identifierar den förkortade adressen det ungefärliga geografiska området runt Internetanslutningen.',
        ],
        'useragent' => [
            'title' => 'Identifiering av användaragent',
            'description' => 'När du besöker en webbplats skickar din webbläsare automatiskt en identifierare, vanligtvis med uppgifter om vilken webbläsare och vilket operativsystem som används. Denna webbläsaridentifiering (den så kallade användaragenten) kan användas av webbplatser, till exempel för att känna igen mobila enheter och presentera dem med en anpassad utmatning.',
            'example' => 'Exempel',
        ],
        'payment' => [
            'title' => 'Betalningsuppgifter',
            'description' => 'Vid köp av en MetaGer-nyckel krävs olika betalningsuppgifter beroende på betalningsleverantör',
            'examples' => 'Exempel',
            'name' => 'Max Mustermann, mail@example.com',
            'card' => 'Sista siffrorna i kreditkortsnumret',
        ],
        'query' => [
            'title' => 'Inmatad sökfråga',
            'description' => 'Inmatade sökord är absolut nödvändiga för en webbsökning. I regel kan inga personuppgifter erhållas från dem, bland annat eftersom de inte har en fast struktur.',
            'examples' => 'Exempel',
            'example_1' => 'vattenförbrukning dusch',
            'example_2' => 'Lyrics På ett träd en gök',
        ],
        'preferences' => [
            'title' => 'Användarinställningar',
            'description' => 'Förutom formulärdata och användaragenter överför webbläsaren ofta andra data. Det gäller t.ex. språkval, sökinställningar, accept-headers, do not track-headers m.m.',
        ],
        'contact' => [
            'title' => 'Kontaktuppgifter',
            'description' => 'Här nedan finns det av dig angivna namnet (för- och efternamn), samt din e-postadress. Dessa uppgifter använder vi uteslutande för att svara dig och ger dig under inga omständigheter vidare till tredje part.',
        ],
        'message' => [
            'title' => 'Meddelande',
            'description' => 'Det meddelande som du anger här kommer att överföras till oss och användas för att behandla din begäran.',
        ],
        'title' => 'Beskrivning av resulterande data',
        'error' => [
            'title' => 'Felrapport (stacktrace)',
            'description' => 'När ett tekniskt fel uppstår i vår applikation genereras automatiskt en felbeskrivning tillsammans med en stacktrace. Detta visar oss var i källkoden felet inträffade, så att vi kan åtgärda det.',
        ],
        'reduction' => [
            'title' => 'Intyg för reducerad medlemsavgift',
            'description' => 'Ett dokument som du laddar upp för att styrka din rätt till reducerad medlemsavgift, till exempel ett kvitto som bekräftar mottagandet av överföringsbetalningar.',
        ],
    ],
    'base' => [
        'title' => 'Rättslig grund för behandling',
        'description' => 'Den rättsliga grunden för behandlingen av dina personligt identifierbara uppgifter är antingen Art. 6 (1) (a) GDPR om du samtycker till behandlingen genom att använda våra tjänster, eller Art. 6 (1) (f) GDPR om behandlingen är nödvändig för att skydda våra legitima intressen, eller en annan rättslig grund om vi meddelar dig separat.',
    ],
    'rights' => [
        'obligation_notify' => [
            'description' => 'Enligt artikel 19 GDPR; om vi skulle ha gjort uppgifter som du har anförtrott oss tillgängliga för tredje part (vilket vi aldrig gör), skulle vi vara skyldiga att informera dem om att vi, på din begäran, skulle radera, ändra, etc. har utfört.',
            'title' => 'Anmälningsskyldighet i samband med rättelse eller radering av personuppgifter eller begränsning av behandling:',
        ],
        'perception' => 'För att utöva dessa rättigheter räcker det att du kontaktar oss via vårt <a href=":contact_link">kontaktformulär</a></b>. Om du föredrar brevformuläret kan du skicka e-post till vår kontorsadress:',
        'title' => 'Dina rättigheter som användare (och våra skyldigheter)',
        'description' => 'För att du också ska kunna skydda dina personuppgifter klargör vi (enligt art. 13 DSGVO) att du har följande rättigheter:',
        'information' => [
            'title' => 'Rätt att tillhandahålla information',
            'description' => 'Du har rätt (art. 15 GDPR) att när som helst begära information från oss om huruvida och i så fall vilka av dina uppgifter vi (metager.de och SUMA-EV) har om dig. Vi skickar dig så snart som möjligt, dvs. inom några dagar, en fullständig kopia av de uppgifter som vi har lagrat eller på annat sätt lagrat om dig i enlighet med artikel 15 paragraf 3 underavsnitt 1 GDPR. Vi föredrar den elektroniska metoden för detta i enlighet med artikel 15 paragraf 3 stycke 3 GDPR; För detta ändamål kommer vi att spara din e-postadress under behandlingens varaktighet. Vänligen informera oss om du specifikt vill ha informationen i pappersform.',
        ],
        'correction' => [
            'title' => 'Rätt till rättelse och komplettering',
            'description' => 'Enligt artikel 16 i GDPR. Om vi har lagrat felaktiga uppgifter om dig kan du begära att dessa korrigeras. Detta gäller även saknade komponenter, här har du rätt att komplettera.',
        ],
        'deletion' => [
            'title' => 'Rätt till radering',
            'description' => 'Enligt artikel 17 i GDPR',
        ],
        'processing' => [
            'title' => 'Rätt till begränsning av behandling',
            'description' => 'I enlighet med artikel 18 GDPR; Om du till exempel har bett oss att radera eller ändra uppgifter om dig, kan du införa ett behandlingsförbud för oss under den tid det tar oss att göra det. Detta är möjligt oavsett om vi i slutändan ändrar, raderar etc. uppgifterna i fråga.',
        ],
        'complaint' => [
            'title' => 'Rätt att klaga',
            'description' => 'Enligt artikel 13.2 d i GDPR kan du klaga på oss till dataskyddsombudet i delstaten Niedersachsen. Online: <a href="https://www.lfd.niedersachsen.de/startseite/">Ombud för dataskydd</a>',
        ],
        'opposition' => [
            'title' => 'Rätt att invända mot behandling',
            'description' => 'Enligt artikel 21 i GDPR kan du, om du t.ex. finns med på en lista och vill vara kvar där, fortfarande förbjuda behandling eller ytterligare behandling av dessa uppgifter.',
        ],
        'portability' => [
            'title' => 'Rätt till dataportabilitet',
            'description' => 'Enligt artikel 20 GDPR; Detta innebär att vi är skyldiga att förse dig med de begärda uppgifterna på ett läsbart, eventuellt maskinläsbart eller vanligt sätt så att du skulle kunna göra uppgifterna tillgängliga för en annan person som de är (att överföra).',
        ],
    ],
    'data' => [
        'useragent' => 'Användaragent',
        'query' => 'Sök Förfrågan',
        'ip' => 'IP-adress',
        'preferences' => 'Användarinställningar',
        'contact' => 'Kontaktuppgifter',
        'message' => 'Meddelande',
        'payment' => 'Betalningsuppgifter',
        'referrer' => 'den referrer du skickade',
        'gps' => 'Uppgifter om plats',
        'optional' => 'valfri',
        'unused' => 'Kommer inte att sparas eller delas.',
        'error' => 'Felrapport',
        'reduction' => 'Reduktionsbevis',
    ],
    'title' => 'Integritetspolicy',
    'introduction' => 'För maximal transparens listar vi vilka uppgifter vi samlar in från dig och hur vi använder dem. Skyddet av dina uppgifter är viktigt för oss och det borde det vara för dig också. <strong>Vänligen läs detta uttalande noggrant; det ligger i ditt intresse.</strong>',
    'responsible_party' => [
        'title' => 'Ansvariga personer och kontaktpersoner',
        'description' => 'MetaGer och relaterade tjänster drivs av <a href="https://suma-ev.de">SUMA-EV</a>, som också är författare till detta uttalande. I detta uttalande betyder "vi" i allmänhet SUMA-EV. Du kan hitta våra kontaktuppgifter i vår <a href=":link_impress">Imprint</a>. Du kan nå oss via e-post genom att använda vårt kontaktformulär <a href=":link_contact"></a> .',
    ],
    'principles' => [
        'title' => 'Principer',
        'description' => 'Som en ideell förening är vi engagerade i fri tillgång till kunskap. Eftersom vi vet att fri forskning inte är förenligt med massövervakning tar vi också dataskyddet på största allvar. Vi har alltid bara behandlat de uppgifter som är absolut nödvändiga för att våra tjänster ska fungera. Dataskydd är alltid vår standard. Vi använder oss inte av profilering - dvs. automatiskt skapande av användarprofiler.',
    ],
    'changes' => [
        'title' => 'Ändringar av detta uttalande',
        'description' => 'Liksom våra erbjudanden är även denna dataskyddsdeklaration föremål för ständiga förändringar. Du bör därför läsa dem igen regelbundet.',
        'date' => 'Denna version av vår integritetspolicy är daterad: :datum',
    ],
    'hosting' => [
        'title' => 'Värdskap',
        'description' => 'Våra tjänster administreras av oss, SUMA-EV, och drivs på hårdvara som hyrs av Hetzner Online GmbH.',
    ],
    'monitoring' => [
        'title' => 'Feluppföljning och applikationsövervakning',
        'description' => 'För att säkerställa tillförlitligheten i våra tjänster använder vi det öppen källkodsbaserade felspårningsverktyget GlitchTip, både i backend- och frontend-delen av våra applikationer. GlitchTip körs uteslutande på vår egen infrastruktur; inga data delas med eller överförs till någon extern leverantör av analys- eller övervakningstjänster.',
        'collected' => [
            'title' => 'Vilka uppgifter genereras?',
            'description' => 'När ett fel uppstår i vår webbapplikation eller på våra servrar samlar vi automatiskt in följande:',
            'error' => 'En felbeskrivning och ett stacktrace som identifierar den berörda platsen i koden.',
            'useragent' => 'Webbläsartyp, operativsystem och ungefärlig enhetstyp, fastställda utifrån din användaragent.',
            'url' => 'Adressen (URL) till den sida eller funktion där felet inträffade.',
        ],
        'not_collected' => [
            'title' => 'Vilka uppgifter genereras inte',
            'description' => 'Din IP-adress raderas innan den överförs till GlitchTip och når därför aldrig systemet. Vi kopplar inte felrapporter till någon enskild person eller något användarkonto – inte ens för inloggade användare. Vi samlar inte heller in lösenord, betalningsuppgifter eller innehållet i formulär eller meddelanden som du skickar via våra tjänster.',
        ],
        'retention' => 'Vi lagrar felrapporter i 30 dagar; därefter raderas de automatiskt.',
        'base' => 'Den rättsliga grunden för denna behandling är vårt berättigade intresse av att driva våra tjänster på ett tillförlitligt och säkert sätt (artikel 6.1 f i GDPR).',
    ],
];
