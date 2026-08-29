<?php

/**
 * Anonyme Token — /hilfe/anonyme-token.
 *
 * Aus dem "anonymous-token"-Zweig von pass/lang/<locale>/help.json.
 * Der Pfad /keys/help/anonymous-token wird dauerhaft hierher weitergeleitet:
 * er steht in bereits versandten Mitglieds-Willkommensmails.
 */

return [
    "heading" => "Anonyme tokens",
    "description" => [
        "heading" => "Hvad er anonyme tokens?",
        "text" => "Hvis du bruger en MetaGer-nøgle, vil du modtage en tilfældigt genereret adgangskode, som din browser sender til os med hver søgeforespørgsel, så vi kan aktivere annoncefri søgning. Hvis du bruger vores <a href=\"/app\" target=\"_blank\">Android-app</a>, eller vores webudvidelse til <a href=\"https://chrome.google.com/webstore/detail/gjfllojpkdnjaiaokblkmjlebiagbphd\" target=\"_blank\" rel=\"noopener\">Chrome</a> og <a href=\"https://addons.mozilla.org/firefox/addon/metager-suche/\" target=\"_blank\" rel=\"noopener\">Firefox</a>, sender din browser os i stedet for adgangskoden en tilfældigt genereret adgangskode (anonymt token) med hver søgeanmodning til godkendelse, som genereres lokalt. Dette sikrer, at hver adgangskode er unik og ikke har nogen forbindelse med den faktiske MetaGer-nøgle eller mellem de enkelte adgangskoder.",
    ],
    "problem" => [
        "heading" => "Hvilket problem er det meningen, at anonyme tokens skal løse?",
        "text" => "Hvis din browser altid sender os det samme password ved hver søgning, ville vi i det mindste teoretisk have mulighed for at etablere en sammenhæng mellem alle søgninger, der er udført med den samme nøgle. Selv hvis vi ikke gør det, vil tillid selvfølgelig stadig være nødvendig for at være sikker på din anonyme søgning. For at vi ikke kun skal love den anonyme søgning, men også kan bevise den, har vi introduceret de anonyme tokens.",
    ],
    "general-function" => [
        "heading" => "Hvordan fungerer det?",
        "texts" => [
            "Så vi vil gerne have engangsadgangskoder genereret direkte fra din slutenhed, som du så sender til os til godkendelse under dine søgninger. Men for hvert anonymt token på din slutenhed skal vi sørge for, at et almindeligt token er blevet trukket fra din MetaGer-nøgle for det, uden (og det er det afgørende) at fortælle os, hvilken MetaGer-nøgle der blev brugt til at generere det anonyme token.",
            "Traditionelt ville vi bruge en eller anden form for kryptografisk signatur til dette formål. I dette tilfælde ville vi underskrive det genererede anonyme token. Når du så sender os det anonyme token sammen med signaturen på et senere tidspunkt, kan vi være sikre på, at det anonyme token er gyldigt. Men for at få signaturen skulle du have sendt os det anonyme token sammen med din rigtige nøgle, hvilket ville ophæve anonymiteten.",
            "Derfor bruger vi i stedet en modificeret form for kryptografisk signatur, den såkaldte <a href=\"https://en.wikipedia.org/wiki/Blind_signature\" target=\"_blank\">blind signature</a>. For at skabe en analogi til det virkelige liv, er det som at sende os dit anonyme token i en kuvert af karbonpapir. I dette eksempel ville vi ikke være i stand til at åbne kuverten, men vi ville være i stand til at underskrive udefra, så vores underskrift ville blive overført til det anonyme token indeni. Når du får konvolutten tilbage, kan du fjerne den og sende os adgangskoden og underskriften tilbage senere. Så kan vi bekræfte, at det rent faktisk er vores underskrift.",
            "Faktisk er denne analogi en smule misvisende, for i den faktiske proces, i det øjeblik du sender os det anonyme token og signaturen, har vi ikke kun aldrig set det anonyme token før, men heller aldrig set selve signaturen. Og alligevel kan vi verificere, at signaturen er genereret af os.",
        ],
    ],
    "meaning" => [
        "heading" => "Hvad betyder det for dine autentificerede søgninger?",
        "texts" => [
            "Ved at bruge den beskrevne algoritme kan både du og vi sikre, at der hver gang bruges en ny tilfældig adgangskode, der ikke er relateret til din MetaGer-nøgle, til dine godkendte søgninger.",
            "Det særlige ved denne algoritme er, at alle komponenter, der sikrer anonymitet, eksekveres lokalt på din enhed. Denne eksekverede kildekode kan ses og verificeres af enhver til enhver tid.",
            "Det bedste af det hele er, at du ikke behøver at konfigurere noget for at bruge anonyme tokens. Det er nok at installere/bruge vores browserudvidelse/Android-app for at få din enhed til at bruge anonyme tokens til alle søgninger.",
        ],
    ],
    "technical-function" => [
        "heading" => "Algoritmen bag den:",
        "texts" => [
            "I en klassisk RSA-signatur ville vi tage det anonyme token <code>m</code>, den hemmelige eksponent <code>d</code>, og den offentlige modulus <code>N</code> af vores private nøgle og oprette signaturen ved hjælp af <code>m^d (mod N)</code>. Vi ønsker dog, at <code>m</code> skal forblive hemmelig.",
            "Derfor opretter din terminal et tilfældigt tal <code>r</code> ved hjælp af en tilfældig talgenerator, som ikke er divisor-relateret til <code>N</code>. Så den største fælles divisor for <code>r</code> og <code>N</code> må være <code>1</code>.",
            "Da <code>r</code> er et tilfældigt tal, følger det, at <code>m'</code> ikke afslører nogen information om det lokalt lagrede anonyme token <code>m</code>.",
            "Vores server modtager nu det obfuskerede anonyme token <code>m'</code> fra din slutenhed sammen med den MetaGer-nøgle, der skal bruges. Vi trækker et token fra nøglen og sender den også obfuskerede signatur <code>s'&Congruent; (m')^d (mod N)</code> tilbage til din slutenhed.",
            "Din terminal kan nu beregne den faktiske gyldige RSA-signatur <code>s</code> for det ukrypterede anonyme token: <code>s&Congruent; s' r^-1 (mod N)</code>. Det virker, fordi for RSA-nøgler gælder <code>r^(e*d)&Congruent; r (mod N)</code>. Og derfor også: <code>s &Congruent; s' * r^-1 &Congruent; (m')^d*r^-1 &Congruent; m^d*r^(e*d)*r^-1 &Congruent; m^d*r*r^-1 &Congruent; m^d (mod N)</code>.",
            "Din slutenhed sender os nu det ukrypterede anonyme token sammen med den tilknyttede signatur til godkendelse under en søgning. Selve nøglen bliver ikke længere sendt til os under søgningen.",
        ],
    ],
];
