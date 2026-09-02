<?php

/**
 * Anonyme Token — /hilfe/anonyme-token.
 *
 * Aus dem "anonymous-token"-Zweig von pass/lang/<locale>/help.json.
 * Der Pfad /keys/help/anonymous-token wird dauerhaft hierher weitergeleitet:
 * er steht in bereits versandten Mitglieds-Willkommensmails.
 */

return [
    "heading" => "Anonyma tokens",
    "description" => [
        "heading" => "Vad är anonyma tokens?",
        "text" => "Om du använder en MetaGer-nyckel får du ett slumpmässigt genererat lösenord som din webbläsare skickar till oss med varje sökfråga så att vi kan aktivera annonsfri sökning. Om du använder vår <a href=\"/app\" target=\"_blank\">Android-app</a>, eller vårt webbtillägg för <a href=\"https://chrome.google.com/webstore/detail/gjfllojpkdnjaiaokblkmjlebiagbphd\" target=\"_blank\" rel=\"noopener\">Chrome</a> och <a href=\"https://addons.mozilla.org/firefox/addon/metager-suche/\" target=\"_blank\" rel=\"noopener\">Firefox</a>, istället för lösenordet, skickar din webbläsare ett slumpmässigt genererat lösenord (anonym token) till oss med varje sökbegäran för autentisering, som genereras lokalt. Detta säkerställer att varje lösenord är unikt och inte har någon koppling till den faktiska MetaGer-nyckeln eller mellan de enskilda lösenorden.",
    ],
    "problem" => [
        "heading" => "Vilket problem är anonyma tokens tänkta att lösa?",
        "text" => "Om din webbläsare alltid skickar samma lösenord till oss vid varje sökning, skulle vi åtminstone teoretiskt ha möjlighet att fastställa ett samband mellan alla sökningar som utförs med samma nyckel. Även om vi inte gör det, skulle förtroende naturligtvis fortfarande vara nödvändigt för att vara säker på din anonyma sökning. För att vi inte bara ska behöva lova den anonyma sökningen, utan också kunna bevisa den, har vi infört de anonyma tokens.",
    ],
    "general-function" => [
        "heading" => "Hur fungerar den?",
        "texts" => [
            "Så vi vill ha engångslösenord som genereras direkt från din endpoint-enhet, som du sedan skickar till oss för autentisering under dina sökningar. För varje anonym token på din slutenhet måste vi dock se till att en vanlig token har subtraherats från din MetaGer-nyckel för den, utan att (och detta är kruxet) berätta för oss vilken MetaGer-nyckel som användes för att generera den anonyma token.",
            "Traditionellt skulle vi använda någon form av kryptografisk signatur för detta ändamål. I det här fallet skulle vi signera den genererade anonyma token. När du sedan skickar oss den anonyma token tillsammans med signaturen vid ett senare tillfälle kan vi vara säkra på att den anonyma token är giltig. För att få signaturen skulle du dock ha skickat oss den anonyma token tillsammans med din riktiga nyckel, vilket skulle upphäva anonymiteten.",
            "Därför använder vi istället en modifierad form av kryptografisk signatur, den så kallade <a href=\"https://en.wikipedia.org/wiki/Blind_signature\" target=\"_blank\">blinda signaturen</a>. För att skapa en verklighetstrogen analogi är det som att skicka oss din anonyma token i ett kuvert av karbonpapper. I det här exemplet skulle vi inte kunna öppna kuvertet, men vi skulle kunna skriva under från utsidan, så att vår signatur skulle överföras till den anonyma token inuti. När du får tillbaka kuvertet kan du ta bort det och skicka tillbaka lösenordet och underskriften senare. Vi kan då bekräfta att det verkligen är vår signatur.",
            "I själva verket är denna analogi lite missvisande, eftersom vi i den faktiska processen, i det ögonblick du skickar oss den anonyma token och signaturen, inte bara aldrig har sett den anonyma token tidigare, utan heller aldrig har sett själva signaturen. Ändå kan vi verifiera att signaturen har genererats av oss.",
        ],
    ],
    "meaning" => [
        "heading" => "Vad innebär detta för dina autentiserade sökningar?",
        "texts" => [
            "Genom att använda den beskrivna algoritmen kan vi och du se till att ett nytt slumpmässigt lösenord som inte är relaterat till din MetaGer-nyckel används varje gång för dina autentiserade sökningar.",
            "Det speciella med denna algoritm är att alla komponenter som säkerställer anonymitet exekveras lokalt på din enhet. Den exekverade källkoden kan visas och verifieras av vem som helst när som helst.",
            "Det bästa av allt är att du inte behöver konfigurera något för att använda anonyma tokens. Det räcker med att installera/använda vårt webbläsartillägg/Android-app för att din enhet ska använda anonyma tokens för alla sökningar.",
        ],
    ],
    "technical-function" => [
        "heading" => "Den bakomliggande algoritmen:",
        "texts" => [
            "I en klassisk RSA-signatur skulle vi ta den anonyma token <code>m</code>, den hemliga exponenten <code>d</code> och den offentliga modulen <code>N</code> för vår privata nyckel och skapa signaturen med <code>m^d (mod N)</code>. Vi vill dock att <code>m</code> ska förbli hemlig.",
            "Därför skapar terminalen ett slumptal <code>r</code> med hjälp av en slumptalsgenerator, som inte är divisorrelaterat till <code>N</code>. Därför måste den största gemensamma divisorn för <code>r</code> och <code>N</code> vara <code>1</code>.",
            "Eftersom <code>r</code> är ett slumptal följer att <code>m'</code> inte avslöjar någon information om den lokalt lagrade anonyma token <code>m</code>.",
            "Vår server tar nu emot den obfuskerade anonyma token <code>m'</code> från din slutenhet tillsammans med den MetaGer-nyckel som ska användas. Vi subtraherar en token från nyckeln och skickar den också obfuskerade signaturen <code>s'&Congruent; (m')^d (mod N)</code> tillbaka till din slutenhet.",
            "Din terminal kan nu beräkna den faktiska giltiga RSA-signaturen <code>s</code> för den okrypterade anonyma token: <code>s&Congruent; s' r^-1 (mod N)</code>. Detta fungerar eftersom för RSA-nycklar <code>r^(e*d)&Congruent; r (mod N)</code>. Och därför också: <code>s &Congruent; s' * r^-1 &Congruent; (m')^d*r^-1 &Congruent; m^d*r^(e*d)*r^-1 &Congruent; m^d*r*r^-1 &Congruent; m^d (mod N)</code>.",
            "Din slutenhet skickar nu den okrypterade anonyma token tillsammans med den tillhörande signaturen till oss för auktorisering under en sökning. Själva nyckeln skickas inte längre till oss under sökningen.",
        ],
    ],
];
