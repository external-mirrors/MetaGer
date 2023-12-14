<?php
return [
    'stopworte' => [
        '2' => 'Et eksempel: Du leder efter en ny bil, men ingen BMW. Så skal din søgning være <div class="well well-sm">new car -bmw</div>',
    ],
    'title' => 'MetaGer - Hjælp',
    'backarrow' => 'Tilbage',
    'mehrwortsuche' => [
        '3' => [
            'example' => '"rundbordssamtale" "beslutning"',
        ],
    ],
    'urls' => [
        'title' => 'Ekskluder URL\'er',
        'explanation' => 'Du kan ekskludere søgeresultater, der indeholder specifikke ord i deres resultatlinks, ved at bruge "-url:" i din søgning.',
        'example_b' => '<i>min søgning</i> -url:dog',
        'example_a' => 'Eksempel: Du vil ekskludere resultater, hvor ordet "hund" optræder i resultatlinket:',
    ],
    'bang' => [
        'title' => 'Indstillinger <a title="For easy help, click here" href="/hilfe/easy-language/mainpages#help-settings" ><img class="easy-help-icon lm-only" src="/img/help-questionmark-icon-lm.svg"/><img class="easy-help-icon dm-only" src="/img/help-questionmark-icon-dm.svg"/></a>',
        '1' => 'MetaGer understøtter i begrænset omfang en skrivestil, der ofte kaldes \'!bang\'-syntaks.<br>Et \'!bang\' starter altid med et udråbstegn og indeholder ikke mellemrum. Eksempler er \'!twitter\' eller \'!facebook\'.<br>Når en understøttet !bang bruges i søgeforespørgslen, vises en post i vores hurtige tips, så du kan fortsætte søgningen med den respektive tjeneste (Twitter eller Facebook) ved at trykke på en knap.',
        '2' => 'Hvorfor åbnes !bangs ikke direkte?',
        '3' => '!bang-"omdirigeringerne" er en del af vores hurtige tips og kræver et ekstra "klik." Det var en svær beslutning for os, da det gør !bangs mindre brugbare. Men det er desværre nødvendigt, fordi de links, der omdirigeres til, ikke kommer fra os, men fra en tredjepart, DuckDuckGo.<p>Vi sørger altid for, at vores brugere til enhver tid bevarer kontrollen. Derfor beskytter vi på to måder: For det første overføres det indtastede søgeord aldrig til DuckDuckGo, kun !bang. For det andet bekræfter brugeren udtrykkeligt besøget på !bang-målet. Desværre kan vi af personalemæssige årsager i øjeblikket ikke selv kontrollere eller vedligeholde alle disse !bangs.',
    ],
    'searchinsearch' => [
        '1' => 'Resultatet gemmes i et nyt TAB, der vises i højre side af skærmen. Det kaldes "Gemte resultater". Her kan du gemme enkeltresultater fra flere søgninger. TAB\'et bliver ved med at eksistere. Når du går ind i dette TAB, får du din personlige resultatliste med værktøjer til at filtrere og sortere resultaterne. Klik på et andet TAB for at gå tilbage til yderligere søgninger. Dette har du ikke, hvis skærmen er for lille. Mere info (indtil videre kun på tysk): <a href="http://blog.suma-ev.de/node/225" target="_blank" rel="noopener"> SUMA blog</a>.',
    ],
    'selist' => [
        'title' => 'Tilføj MetaGer til din browsers søgemaskineliste <a title="For easy help, click here" href="/hilfe/easy-language/functions#eh-selist"><img class="easy-help-icon lm-only" src="/img/help-questionmark-icon-lm.svg"/><img class="easy-help-icon dm-only" src="/img/help-questionmark-icon-dm.svg"/></a>',
        'explanation_b' => 'Nogle browsere kræver, at du indtaster en URL; det skal være "https://metager.de/meta/meta.ger3?input=%s" uden anførselstegn. Du kan selv generere URL\'en ved at søge efter noget på metager.de og derefter erstatte det, der står bag "input=" i adresselinjen, med %s. Hvis du stadig har problemer, bedes du kontakte os: <a href="/kontalt" target="_blank" rel="noopener">Kontaktformular</a>',
        'explanation_a' => 'Prøv først at installere det aktuelle plugin. For at installere skal du blot klikke på linket lige under søgefeltet. Din browser burde allerede være blevet registreret der.',
    ],
    'easy-help' => 'Ved at klikke på symbolet <a title="For easy help, click here" href="/hilfe/easy-language/services" ><img class="easy-help-icon lm-only" src="/img/help-questionmark-icon-lm.svg"/><img class="easy-help-icon dm-only" src="/img/help-questionmark-icon-dm.svg"/></a> , får du adgang til en forenklet version af hjælpen.',
    'stopwords' => [
        'title' => 'Stopord <a title="For easy help, click here" href="/hilfe/easy-language/functions#eh-stopwordsearch"><img class="easy-help-icon lm-only" src="/img/help-questionmark-icon-lm.svg"/><img class="easy-help-icon dm-only" src="/img/help-questionmark-icon-dm.svg"/></a>',
        '3' => "bil ny -bmw",
        '2' => "Et eksempel: Du er på udkig efter en ny bil, men bestemt ikke en BMW. Dit input ville være:",
        '1' => "Hvis du vil udelukke søgeresultater i MetaGer, der indeholder bestemte ord (udelukkelsesord / stopord), kan du gøre det ved at sætte et minustegn foran disse ord.",
    ],
    'key' => [
        'title' => 'Tilføj MetaGer-nøgle <a title="For easy help, click here" href="/hilfe/easy-language/functions#eh-keyexplain"><img class="easy-help-icon lm-only" src="/img/help-questionmark-icon-lm.svg"/><img class="easy-help-icon dm-only" src="/img/help-questionmark-icon-dm.svg"/></a>',
        '1' => 'MetaGer-nøglen oprettes automatisk i din browser og bruges. Du behøver ikke at gøre noget andet. Hvis du vil bruge MetaGer-nøglen på andre enheder, er der flere måder at konfigurere MetaGer-nøglen på:',
        '2' => 'Login-kode <br>På <a href = "/keys/key/enter">administrationssiden</a> for MetaGer-nøglen kan du bruge login-koden til at tilføje din nøgle til en anden enhed. Du skal blot indtaste den sekscifrede talkode, når du logger ind. Login-koden kan kun bruges én gang og er kun gyldig, så længe vinduet er åbent.',
        '3' => 'Kopier URL <br>Når du er på <a href = "/keys/key/enter">administrationssiden</a> for MetaGer-nøglen, er der mulighed for at kopiere en URL. Denne URL kan bruges til at gemme alle MetaGer-indstillinger, inklusive MetaGer-nøglen, på en anden enhed.',
        '4' => 'Gem fil <br>Når du er på <a href = "/keys/key/enter">administrationssiden</a> for MetaGer-nøglen, er der mulighed for at gemme en fil. Dette gemmer din MetaGer-nøgle som en fil. Du kan derefter bruge denne fil på en anden enhed til at logge ind med din nøgle.',
        '5' => 'Scan QR-kode <br>Alternativt kan du også scanne den QR-kode, der vises på <a href = "/keys/key/enter">administrationssiden</a> for at logge ind med en anden enhed.',
        '6' => 'Indtast MetaGer-nøglen manuelt <br>Du kan også indtaste nøglen manuelt på en anden enhed.',
        'colors' => [
            'title' => 'Farvet MetaGer-nøgle',
            '1' => 'For nemt at kunne se, om du søger uden reklamer, har vi givet vores nøglesymboler farver. Nedenfor er forklaringer på de tilsvarende farver:',
            'grey' => 'Grå: Du har ikke oprettet en nøgle. Du bruger den gratis søgning.',
            'red' => 'Rød: Hvis dit nøglesymbol er rødt, betyder det, at denne nøgle er tom. Du har opbrugt alle reklamefri søgninger. Du kan genoplade nøglen på siden til administration af nøgler.',
            'green' => 'Grøn: Hvis dit nøglesymbol er grønt, bruger du en opladet nøgle.',
            'yellow' => 'Gul: Hvis du ser en gul nøgle, har du stadig en saldo på 30 poletter. Dine søgninger er ved at løbe ud. Det anbefales at genoplade nøglen snart.',
        ],
    ],
    'multiwordsearch' => [
        'title' => 'Søgning på flere ord <a title="For easy help, click here" href="/hilfe/easy-language/functions#eh-severalwords"><img class="easy-help-icon lm-only" src="/img/help-questionmark-icon-lm.svg"/><img class="easy-help-icon dm-only" src="/img/help-questionmark-icon-dm.svg"/></a>',
        '3' => [
            'example' => '"det" "runde" "bord"',
            'text' => "Hvis du vil sikre dig, at ord fra din søgning også vises i resultaterne, skal du sætte dem i anførselstegn.",
        ],
        '2' => "Hvis det ikke er nok for dig, har du to muligheder for at gøre din søgning mere præcis:",
        '1' => "Når du søger efter mere end ét ord i MetaGer, forsøger vi automatisk at give resultater, hvor alle ordene optræder eller kommer så tæt på som muligt.",
        '4' => [
            'example' => '"det runde bord"',
            'text' => "Med en sætningssøgning kan du søge efter ordkombinationer i stedet for enkelte ord. Du skal blot sætte de ord, der skal optræde sammen, i citationstegn.",
        ],
    ],
    'exactsearch' => [
        'title' => 'Præcis søgning <a title="For easy help, click here" href="/hilfe/easy-language/functions#exactsearch"><img class="easy-help-icon lm-only" src="/img/help-questionmark-icon-lm.svg"/><img class="easy-help-icon dm-only" src="/img/help-questionmark-icon-dm.svg"/></a>',
        '1' => "Hvis du vil finde et bestemt ord i MetaGers søgeresultater, kan du sætte et plustegn foran ordet. Når du bruger et plustegn og anførselstegn, søges en sætning nøjagtigt, som du indtastede den.",
        '2' => "Eksempel: S",
        '3' => 'Eksempel: ',
        'example' => [
            '1' => "+eksempelord",
            '2' => '+"eksempel på sætning"',
        ],
    ],
    'searchfunction' => [
        'title' => "Søgefunktioner",
    ],
];
