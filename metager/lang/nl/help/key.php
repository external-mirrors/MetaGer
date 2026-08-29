<?php

/**
 * Fragen zum MetaGer-Schlüssel — /hilfe/schluessel.
 *
 * Aus dem "faq"-Zweig von pass/lang/<locale>/help.json des Keymanagers.
 */

return [
    "heading" => "Vragen over de MetaGer-sleutel",
    "faqs" => [
        [
            "summary" => "Hoe werkt de MetaGer-sleutel?",
            "description" => "Met een MetaGer-sleutel zoek je reclamevrij. Je ontvangt tokens waarvan één zoekopdracht per zoekopdracht wordt afgetrokken. Wanneer je een MetaGer-sleutel gebruikt, worden alle functies die MetaGer beschermen tegen geautomatiseerde oproepen uitgeschakeld. Dit betekent dat je geen captcha-verzoeken te zien krijgt en dat je IP-adres voor een beperkte tijd niet wordt bijgehouden. Simpel gezegd maakt dit MetaGer sneller, betrouwbaarder en veiliger.",
        ],
        [
            "summary" => "Hoe werkt het anonieme token?",
            "description" => "Je kunt het anonieme token gebruiken met onze browserextensie of app. Hierdoor kun je nog veiliger zoeken met MetaGer. Wanneer je het anonieme token gebruikt, wordt een deel van je tegoed, in de vorm van willekeurige wachtwoorden, opgeslagen op je apparaat. Via een <a href=\":tokenlink\">complex cryptografisch proces</a> wordt het zelfs voor ons onmogelijk om je uitgevoerde zoekopdrachten met elkaar of met jouw sleutel te associëren.",
        ],
        [
            "summary" => "Hoe gebruik ik de MetaGer-sleutel?",
            "description" => "De MetaGer sleutel wordt automatisch ingesteld en gebruikt in de browser. Je hoeft dus verder niets te doen. Als je de MetaGer-sleutel op extra apparaten wilt gebruiken, zijn er verschillende manieren om de MetaGer-sleutel in te stellen:",
            "steps" => [
                [
                    "heading" => "URL kopiëren",
                    "description" => "Wanneer je op de MetaGer sleutelbeheerpagina bent, is er een optie om een URL te kopiëren. Met deze URL kunnen alle instellingen van MetaGer en de MetaGer-sleutel worden opgeslagen op een ander apparaat.",
                ],
                [
                    "heading" => "Bestand opslaan",
                    "description" => "Wanneer je op de MetaGer sleutelbeheerpagina bent, is er een optie om een bestand op te slaan. Dit slaat je MetaGer sleutel op als een bestand. Je kunt dit bestand dan gebruiken op een ander apparaat om daar in te loggen met je sleutel.",
                ],
                [
                    "heading" => "QR-code scannen",
                    "description" => "Je kunt ook de QR-code scannen die wordt weergegeven op de administratiepagina om je aan te melden op een ander apparaat.",
                ],
                [
                    "heading" => "MetaGer-sleutel handmatig invoeren",
                    "description" => "Je kunt de sleutel natuurlijk ook handmatig invoeren op een ander apparaat.",
                ],
            ],
        ],
        [
            "summary" => "Ik moet mijn sleutel regelmatig invoeren. Wat kan ik doen?",
            "description" => "We instrueren je browser om de sleutel permanent op te slaan zodra deze is gegenereerd of ingelogd. Afhankelijk van je browserconfiguratie heb je deze misschien ingesteld om regelmatig cookies en websitegegevens te verwijderen, waardoor je natuurlijk ook wordt afgemeld bij MetaGer. Je hebt de volgende opties:",
            "steps" => [
                [
                    "heading" => "Een uitzondering toevoegen",
                    "description" => "In de instellingen van Firefox kun je MetaGer op een witte lijst zetten voor een uitzondering van het verwijderen van cookies & websitegegevens waardoor je ingelogd blijft.",
                ],
                [
                    "heading" => "Installeer onze browserextensie",
                    "description" => "Onze browserextensie voor <a href=\"https://addons.mozilla.org/firefox/addon/metager-suche/\" target=\"_blank\" rel=\"noopener\">Firefox</a> en <a href=\"https://chrome.google.com/webstore/detail/gjfllojpkdnjaiaokblkmjlebiagbphd\" target=\"_blank\" rel=\"noopener\">Chrome</a> kan je zoekinstellingen opslaan, inclusief je sleutel, zonder cookies te gebruiken, zodat je alle browserdata kunt verwijderen zonder uitgelogd te worden bij MetaGer.",
                ],
                [
                    "heading" => "Inloggen zonder de sleutel van 36 tekens in te voeren",
                    "description" => "Als je een wachtwoordmanager gebruikt, kun je de sleutel daarin opslaan zodat je automatisch wordt aangemeld. Als alternatief bieden we een <a href=\":keylink\">instellingen-URL</a> om op te slaan, bijvoorbeeld als bladwijzer. Bij het openen van de instellingen-URL log je in zonder de sleutel handmatig in te voeren.",
                ],
            ],
        ],
        [
            "summary" => "Ik ben ontevreden over de MetaGer sleutel. Wat kan ik doen?",
            "description" => "In dit geval kunt u binnen 30 dagen na aankoop een terugbetaling aanvragen voor ongebruikte tokens. Hiervoor heb je je betalings-ID nodig. Om een terugbetaling aan te vragen, open je de sleutelbeheerpagina van MetaGer. Klik daar op het menu-item \"Bestellingen\" en voer je betalings-ID in. Daarna kun je op de knop \"Restitutie aanvragen\" klikken en het restitutieverzoek versturen.",
        ],
        [
            "summary" => "Hoe kan ik volledig anoniem zoeken?",
            "description" => "Jouw privacy en anonimiteit zijn erg belangrijk voor ons. Daarom bieden we anonieme betaalmethoden (contant geld). We bieden ook het gebruik van <a href=\":tokenlink\">anonieme tokens</a>, die ze zelfs kunnen gebruiken om verifieerbaar anoniem te zoeken.",
        ],
        [
            "summary" => "Ik heb een factuur nodig. Hoe krijg ik die?",
            "description" => "Hiervoor heb je alleen je betalings-ID nodig. Om de factuur aan te vragen, open je de beheerpagina van de MetaGer-sleutel. Hier klik je op het menu-item \"Bestellingen\" en voer je je betalings-ID in. Nu kun je op de knop \"Factuur aanvragen\" klikken en de factuuraanvraag starten. Voor de factuur hebben we je volledige naam, je e-mailadres en je adres nodig.",
        ],
        [
            "summary" => "Ik wil mijn MetaGer-sleutel automatisch opladen. Hoe doe ik dat?",
            "description" => "Voor onze leden wordt de sleutel die bij het lidmaatschap hoort maandelijks automatisch aangevuld. De hoeveelheid token is afhankelijk van het betaalde lidmaatschapsgeld.",
        ],
        [
            "summary" => "Ik heb een kaart of een link met een vouchercode gekregen. Wat doe ik daarmee?",
            "description" => "Sommige organisaties geven MetaGer-sleutels met een vast tegoed weg via actiekaarten of een link. Open <a href=\":voucherlink\">onze inwisselpagina</a>, voer de gedrukte code in of scan de QR-code op de kaart. U krijgt dan meteen een nieuwe MetaGer-sleutel met het geschonken tegoed, dat een beperkte tijd geldig is. Elke code kan maar één keer worden ingewisseld.",
        ],
    ],
    "more-questions" => "Heb je nog vragen? Gebruik dan gerust ons <a href=\":contactlink\" target=\"_blank\">contactformulier</a>.",
];
