<?php
return [
    'suchfunktion' => [
        'title' => 'Søgefunktioner',
    ],
    'stopworte' => [
        'title' => 'Ekskluder enkelte ord',
        '1' => 'Hvis du vil udelukke ord i søgeresultatet, skal du sætte et "-" foran det pågældende ord.',
        '2' => 'Et eksempel: Du leder efter en ny bil, men ingen BMW. Så skal din søgning være <div class="well well-sm">new car -bmw</div>',
        '3' => 'bil ny -bmw',
    ],
    'title' => 'MetaGer - Hjælp',
    'backarrow' => ' Tilbage',
    'mehrwortsuche' => [
        'title' => 'Søgning efter mere end ét ord',
        '1' => 'Uden anførselstegn vil du få resultater, der indeholder et eller nogle af ordene i din søgning. Brug anførselstegn til søgning efter eksakte sætninger, citater....',
        '2' => 'Eksempel: søgning efter Shakespears <div class="well well-sm">to be or not to be</div> vil give mange resultater, men den nøjagtige sætning vil kun blive fundet ved hjælp af <div class="well well-sm">"to be or nor to be"</div>',
        '3' => [
            'example' => '"rundbordssamtale" "beslutning"',
            'text' => 'Brug venligst anførselstegn for at være sikker på at få dine søgeord med i resultatlisten.',
        ],
        '4' => [
            'example' => '"Rundbordsbeslutning"',
            'text' => 'Sæt ord eller sætninger i citationstegn for at søge efter nøjagtige kombinationer.',
        ],
    ],
    'urls' => [
        'title' => 'Ekskluder URL\'er',
        'explanation' => 'Brug "-url:" til at udelukke søgeresultater, der indeholder bestemte ord.',
        'example_b' => 'Skriv <i>mine søgeord</i> -url:dog',
        'example_a' => 'Et eksempel: Du vil ikke have ordet "hund" i resultaterne:',
    ],
    'bang' => [
        'title' => '!bangs',
        '1' => 'MetaGer bruger en lidt speciel stavemåde kaldet "!bang syntax". Et !bang starter med "!" og indeholder ikke blanktegn ("!twitter", "!facebook" for eksempel). Hvis du bruger et !bang, der understøttes af MetaGer, vil du se en ny post i "Quicktips". Vi dirigerer dig derefter til den angivne tjeneste (klik på knappen).',
    ],
    'faq' => [
        '18' => [
            'h' => 'Hvorfor åbnes !bangs ikke direkte?',
            'b' => '!bang -\"redirections\" er en del af vores quicktips, og de kræver et ekstra klik. Vi var nødt til at vælge mellem brugervenlighed og kontrol over data. Vi finder det nødvendigt at vise, at linkene tilhører tredjeparter (DuckDuckGo). Så der er en tovejsbeskyttelse: For det første overfører vi ikke dine søgeord, men kun !bang til DuckDuckGo. På den anden side bekræfter brugeren !bang-målet eksplicit. Vi har ikke ressourcerne til at vedligeholde alle disse !bangs, det beklager vi.',
        ],
    ],
    'searchinsearch' => [
        'title' => 'Søg i søgning',
        '1' => 'Resultatet gemmes i et nyt TAB, der vises i højre side af skærmen. Det kaldes "Gemte resultater". Her kan du gemme enkeltresultater fra flere søgninger. TAB\'et bliver ved med at eksistere. Når du går ind i dette TAB, får du din personlige resultatliste med værktøjer til at filtrere og sortere resultaterne. Klik på et andet TAB for at gå tilbage til yderligere søgninger. Dette har du ikke, hvis skærmen er for lille. Mere info (indtil videre kun på tysk): <a href="http://blog.suma-ev.de/node/225" target="_blank" rel="noopener"> SUMA blog</a>.',
    ],
    'selist' => [
        'title' => 'Jeg vil gerne tilføje metager.de til listen over søgemaskiner i min browser.',
        'explanation_b' => 'Nogle browsere har brug for en URL. Brug venligst "https://metager.org/meta/meta.ger3?eingabe=%s" uden bogstavtegn. Hvis der stadig er problemer, bedes du skrive en e-mail til <a href="/en/kontakt" target="_blank" rel="noopener">.</a>',
        'explanation_a' => 'Prøv først at installere det nyeste tilgængelige plugin. Bare brug linket under søgefeltet, det har en automatisk browserdetektion.',
    ],
];
