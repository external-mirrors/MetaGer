<?php

/**
 * Fragen zum MetaGer-Schlüssel — /hilfe/schluessel.
 *
 * Aus dem "faq"-Zweig von pass/lang/<locale>/help.json des Keymanagers.
 */

return [
    "heading" => "Spørgsmål om MetaGer-nøglen",
    "faqs" => [
        [
            "summary" => "Hvordan fungerer MetaGer-nøglen?",
            "description" => "Med en MetaGer-nøgle søger du reklamefrit. Du modtager tokens, hvorfra der trækkes en søgning pr. søgning. Når du bruger en MetaGer-nøgle, er alle funktioner, der beskytter MetaGer mod automatiserede opkald, deaktiveret. Det betyder, at du ikke vil se captcha-anmodninger, og at din IP-adresse ikke vil blive gemt i en begrænset periode. Kort sagt, det vil gøre MetaGer hurtigere, mere pålidelig og mere sikker.",
        ],
        [
            "summary" => "Hvordan fungerer det anonyme token?",
            "description" => "Du kan bruge det anonyme token med vores browserudvidelse eller app. Dette giver dig mulighed for at søge endnu mere sikkert med MetaGer. Når du bruger anonymt token, vil en del af din kredit, i form af tilfældige adgangskoder, blive gemt på din enhed. Gennem en <a href=\":tokenlink\">kompleks kryptografisk proces</a> bliver det umuligt for os selv at forbinde dine udførte søgninger med hinanden eller med din nøgle.",
        ],
        [
            "summary" => "Hvordan bruger jeg MetaGer-nøglen?",
            "description" => "MetaGer-nøglen oprettes og bruges automatisk i browseren. Så du behøver ikke at gøre noget andet. Hvis du vil bruge MetaGer-nøglen på flere enheder, er der flere måder at konfigurere MetaGer-nøglen på:",
            "steps" => [
                [
                    "heading" => "Kopier URL",
                    "description" => "Når du er på MetaGer-nøgleadministrationssiden, er der mulighed for at kopiere en URL. Med denne URL kan alle indstillinger for MetaGer samt MetaGer-nøglen gemmes på en anden enhed.",
                ],
                [
                    "heading" => "Gem fil",
                    "description" => "Når du er på siden til administration af MetaGer-nøgler, er der mulighed for at gemme en fil. Dette gemmer din MetaGer-nøgle som en fil. Du kan derefter bruge denne fil på en anden enhed til at logge ind der med din nøgle.",
                ],
                [
                    "heading" => "Scan QR-kode",
                    "description" => "Alternativt kan du også scanne QR-koden, der vises på administrationssiden, for at logge ind på en anden enhed.",
                ],
                [
                    "heading" => "Indtast MetaGer-nøglen manuelt",
                    "description" => "Du kan selvfølgelig også indtaste nøglen manuelt på en anden enhed.",
                ],
            ],
        ],
        [
            "summary" => "Jeg er nødt til at indtaste min nøgle regelmæssigt. Hvad kan jeg gøre ved det?",
            "description" => "Vi instruerer din browser i at gemme nøglen permanent, når den er genereret eller logget ind. Afhængigt af din browserkonfiguration kan du have sat den op til regelmæssigt at slette cookies og webstedsdata, hvilket naturligvis også vil logge dig ud af MetaGer. Du har følgende muligheder:",
            "steps" => [
                [
                    "heading" => "Tilføj en undtagelse",
                    "description" => "I Firefox-indstillingerne kan du sætte MetaGer på en hvidliste for at få dispensation til at slette cookies og webstedsdata, så du kan blive ved med at være logget ind.",
                ],
                [
                    "heading" => "Installer vores browserudvidelse",
                    "description" => "Vores browserudvidelse til <a href=\"https://addons.mozilla.org/firefox/addon/metager-suche/\" target=\"_blank\" rel=\"noopener\">Firefox</a> og <a href=\"https://chrome.google.com/webstore/detail/gjfllojpkdnjaiaokblkmjlebiagbphd\" target=\"_blank\" rel=\"noopener\">Chrome</a> kan gemme dine søgeindstillinger, herunder din nøgle, uden brug af cookies, så du kan slette alle browserdata uden at være logget ud af MetaGer.",
                ],
                [
                    "description" => "Hvis du bruger en password manager, kan du gemme nøglen i den, så du kan blive logget ind automatisk. Alternativt tilbyder vi en <a href=\":keylink\">settings URL</a>, der kan gemmes, f.eks. som et bogmærke. Når du åbner indstillingsadressen, logger du ind uden at indtaste nøglen manuelt.",
                    "heading" => "Log ind uden at indtaste nøglen med 36 tegn",
                ],
            ],
        ],
        [
            "summary" => "Jeg er utilfreds med MetaGer-nøglen. Hvad kan jeg gøre ved det?",
            "description" => "I dette tilfælde kan du anmode om tilbagebetaling af ubrugte tokens inden for 30 dage efter købet. For at gøre dette skal du bruge dit betalings-ID. For at anmode om tilbagebetaling skal du åbne MetaGer-nøglehåndteringssiden. Der skal du klikke på menupunktet \"Ordrer\" og indtaste dit betalings-ID. Derefter kan du klikke på knappen \"Anmod om tilbagebetaling\" og sende anmodningen om tilbagebetaling.",
        ],
        [
            "summary" => "Hvordan søger jeg helt anonymt?",
            "description" => "Dit privatliv og din anonymitet er meget vigtige for os. Derfor tilbyder vi anonyme betalingsmetoder (kontanter). Vi tilbyder også brugen af <a href=\":tokenlink\">anonyme tokens</a>, som de endda kan bruge til at søge verificerbart anonymt.",
        ],
        [
            "summary" => "Jeg har brug for en faktura. Hvordan får jeg den?",
            "description" => "Til dette har du kun brug for dit betalings-ID. For at anmode om fakturaen skal du åbne MetaGer-nøglens administrationsside. Her klikker du på menupunktet \"Ordrer\" og indtaster dit betalings-ID. Nu kan du klikke på knappen \"Anmod om faktura\" og starte anmodningen om faktura. Til fakturaen har vi brug for dit fulde navn, din e-mailadresse og din adresse.",
        ],
        [
            "summary" => "Jeg vil gerne oplade min MetaGer-nøgle automatisk. Hvordan gør jeg det?",
            "description" => "For vores medlemmer bliver den nøgle, der er inkluderet i medlemskabet, automatisk fyldt op hver måned. Mængden af token afhænger af det betalte medlemsgebyr.",
        ],
        [
            "summary" => "Jeg har modtaget et kort eller et link med en gavekode. Hvad gør jeg med det?",
            "description" => "Nogle organisationer forærer MetaGer-nøgler med et fast indestående via kampagnekort eller et link. Åbn <a href=\":voucherlink\">vores indløsningsside</a>, indtast den trykte kode, eller scan QR-koden på kortet. Du får så straks en ny MetaGer-nøgle med det forærede indestående, som er gyldigt i en begrænset periode. Hver kode kan kun indløses én gang.",
        ],
    ],
    "more-questions" => "Har du yderligere spørgsmål? Så er du velkommen til at bruge vores <a href=\":contactlink\" target=\"_blank\">kontaktformular</a>.",
];
