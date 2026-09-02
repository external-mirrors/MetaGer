<?php

/**
 * Fragen zum MetaGer-Schlüssel — /hilfe/schluessel.
 *
 * Aus dem "faq"-Zweig von pass/lang/<locale>/help.json des Keymanagers.
 */

return [
    "heading" => "Frågor om MetaGer-nyckeln",
    "faqs" => [
        [
            "summary" => "Hur fungerar MetaGer-nyckeln?",
            "description" => "Med en MetaGer-nyckel söker du annonsfritt. Du får tokens från vilka en sökning dras av per sökning. När du använder en MetaGer-nyckel inaktiveras alla funktioner som skyddar MetaGer från automatiserade samtal. Detta innebär att du inte kommer att se captcha-förfrågningar och att din IP-adress inte kommer att sparas under en begränsad tid. Enkelt uttryckt kommer detta att göra MetaGer snabbare, mer tillförlitlig och säkrare.",
        ],
        [
            "summary" => "Hur fungerar den anonyma token?",
            "description" => "Du kan använda den anonyma token med vårt webbläsartillägg eller app. Detta gör att du kan söka ännu säkrare med MetaGer. När du använder anonym token kommer en del av din kredit, i form av slumpmässiga lösenord, att lagras på din enhet. Genom en <a href=\":tokenlink\">komplex kryptografisk process</a> blir det omöjligt även för oss att associera dina utförda sökningar med varandra eller med din nyckel.",
        ],
        [
            "steps" => [
                [
                    "heading" => "Kopiera URL",
                    "description" => "När du är på MetaGer-nyckelhanteringssidan finns det ett alternativ att kopiera en URL. Med denna URL kan alla inställningar för MetaGer samt MetaGer-nyckeln sparas på en annan enhet.",
                ],
                [
                    "heading" => "Spara fil",
                    "description" => "När du är på MetaGer-nyckelhanteringssidan finns det ett alternativ för att spara en fil. Då sparas din MetaGer-nyckel som en fil. Du kan sedan använda den här filen på en annan enhet för att logga in där med din nyckel.",
                ],
                [
                    "heading" => "Skanna QR-kod",
                    "description" => "Alternativt kan du också skanna QR-koden som visas på administrationssidan för att logga in på en annan enhet.",
                ],
                [
                    "description" => "Naturligtvis kan du också ange nyckeln manuellt på en annan enhet.",
                    "heading" => "Ange MetaGer-nyckel manuellt",
                ],
            ],
            "summary" => "Hur använder jag MetaGer-nyckeln?",
            "description" => "MetaGer-nyckeln installeras och används automatiskt i webbläsaren. Du behöver alltså inte göra något annat. Om du vill använda MetaGer-nyckeln på fler enheter finns det flera sätt att konfigurera MetaGer-nyckeln:",
        ],
        [
            "summary" => "Jag måste ange min nyckel regelbundet. Vad kan jag göra åt det?",
            "description" => "Vi instruerar din webbläsare att permanent lagra nyckeln när den har genererats eller loggat in. Beroende på din webbläsarkonfiguration kan du ha ställt in den för att regelbundet radera cookies och webbplatsdata som naturligtvis loggar ut dig från MetaGer också. Du har följande alternativ:",
            "steps" => [
                [
                    "heading" => "Lägg till ett undantag",
                    "description" => "I Firefox-inställningarna kan du sätta MetaGer på en vitlista för ett undantag från att radera cookies och webbplatsdata som håller dig inloggad.",
                ],
                [
                    "heading" => "Installera vårt webbläsartillägg",
                    "description" => "Vårt webbläsartillägg för <a href=\"https://addons.mozilla.org/firefox/addon/metager-suche/\" target=\"_blank\" rel=\"noopener\">Firefox</a> och <a href=\"https://chrome.google.com/webstore/detail/gjfllojpkdnjaiaokblkmjlebiagbphd\" target=\"_blank\" rel=\"noopener\">Chrome</a> kan lagra dina sökinställningar som inkluderar din nyckel utan att använda cookies så att du kan radera all webbläsardata utan att vara inloggad på MetaGer.",
                ],
                [
                    "description" => "Om du använder en lösenordshanterare kan du lagra nyckeln i den så att du kan loggas in automatiskt. Alternativt erbjuder vi en URL för <a href=\":keylink\">inställningar</a> som kan lagras, t.ex. som ett bokmärke. När du öppnar inställnings-URL:en loggas du in utan att manuellt ange nyckeln.",
                    "heading" => "Logga in utan att ange nyckeln med 36 tecken",
                ],
            ],
        ],
        [
            "summary" => "Jag är missnöjd med MetaGer-nyckeln. Vad kan jag göra åt det?",
            "description" => "I så fall kan du begära återbetalning för oanvända tokens inom 30 dagar efter köpet. För att göra detta behöver du ditt betalnings-ID. För att begära återbetalning, öppna MetaGer-nyckelhanteringssidan. Där klickar du på menyalternativet \"Beställningar\" och anger ditt betalnings-ID. Därefter kan du klicka på knappen \"Begär återbetalning\" och skicka återbetalningsbegäran.",
        ],
        [
            "summary" => "Hur gör jag för att söka helt anonymt?",
            "description" => "Din integritet och anonymitet är mycket viktiga för oss. Det är därför vi erbjuder anonyma betalningsmetoder (kontanter). Vi erbjuder också användning av <a href=\":tokenlink\">anonyma tokens</a>, som de till och med kan använda för att söka verifierbart anonymt.",
        ],
        [
            "summary" => "Jag behöver en faktura. Hur får jag tag på den?",
            "description" => "För detta behöver du bara ditt betalnings-ID. För att begära fakturan öppnar du MetaGer-nyckeladministrationssidan. Här klickar du på menyalternativet \"Beställningar\" och anger ditt betalnings-ID. Nu kan du klicka på knappen \"Begär faktura\" och starta fakturaförfrågan. För fakturan behöver vi ditt fullständiga namn, din e-postadress och din adress.",
        ],
        [
            "summary" => "Jag skulle vilja ladda min MetaGer-nyckel automatiskt. Hur gör jag det?",
            "description" => "För våra medlemmar fylls den nyckel som ingår i medlemskapet automatiskt på varje månad. Mängden token här beror på den medlemsavgift som betalats.",
        ],
        [
            "summary" => "Jag har fått ett kort eller en länk med en presentkod. Vad gör jag med den?",
            "description" => "Vissa organisationer ger bort MetaGer-nycklar med ett fast saldo via kampanjkort eller en länk. Öppna <a href=\":voucherlink\">vår inlösningssida</a>, ange den tryckta koden eller skanna QR-koden på kortet. Du får då direkt en ny MetaGer-nyckel med det skänkta saldot, som är giltigt under en begränsad tid. Varje kod kan bara lösas in en gång.",
        ],
    ],
    "more-questions" => "Har du ytterligare frågor? Då är du välkommen att använda vårt <a href=\":contactlink\" target=\"_blank\">kontaktformulär</a>.",
];
