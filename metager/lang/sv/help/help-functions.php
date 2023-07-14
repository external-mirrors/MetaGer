<?php
return [
    'title' => 'MetaGer - Hjälp',
    'backarrow' => ' Tillbaka',
    'suchfunktion' => [
        'title' => 'Sökfunktioner',
    ],
    'stopworte' => [
        'title' => 'Utesluta enstaka ord',
        '1' => 'Om du vill utesluta ord i sökresultatet måste du sätta ett "-" framför ordet',
        '2' => 'Exempel: Du letar efter en ny bil, men ingen BMW. Då ska din sökning vara <div class="well well-sm">ny bil -bmw</div>',
        '3' => 'bil ny -bmw',
    ],
    'mehrwortsuche' => [
        'title' => 'Söker efter mer än ett ord',
        '1' => 'Utan citationstecken får du resultat som innehåller ett eller några av orden i din sökning. Använd citattecken för att söka efter exakta fraser, citat....',
        '2' => 'Exempel: sökning efter Shakespears <div class="well well-sm">to be or not to be</div> kommer att ge många resultat, men den exakta frasen kommer endast att hittas med <div class="well well-sm">"to be or nor to be"</div>',
        '3' => [
            'example' => '"rundabordssamtal" "beslut"',
            'text' => 'Använd citationstecken för att vara säker på att få med dina sökord i resultatlistan.',
        ],
        '4' => [
            'example' => '"beslut vid rundabordssamtal"',
            'text' => 'Sätt ord eller fraser inom citattecken för att söka efter exakta kombinationer.',
        ],
    ],
    'urls' => [
        'title' => 'Utesluta URL-adresser',
        'explanation' => 'Använd "-url:" för att utesluta sökresultat som innehåller angivna ord.',
        'example_b' => 'Skriv <i>mina sökord</i> -url:dog',
        'example_a' => 'Exempel: Du vill inte ha ordet "hund" i resultaten:',
    ],
    'bang' => [
        '1' => 'MetaGer använder en speciell stavning som kallas "!bang syntax". En !bang börjar med "!" och innehåller inga blanktecken ("!twitter", "!facebook" till exempel). Om du använder en !bang som stöds av MetaGer kommer du att se en ny post i "Snabbtips". Vi hänvisar sedan till den angivna tjänsten (klicka på knappen).',
        'title' => '!smällar',
    ],
    'faq' => [
        '18' => [
            'h' => 'Varför öppnas inte !bangs direkt?',
            'b' => '!bang -\"redirections\" är en del av våra snabbtips och de behöver ett extra klick. Vi var tvungna att välja mellan enkel användning och kontroll över data. Vi anser att det är nödvändigt att visa att länkarna tillhör tredje part (DuckDuckGo). Så det finns ett tvåvägsskydd: för det första överför vi inte dina sökord utan bara !bang till DuckDuckGo. Å andra sidan bekräftar användaren uttryckligen !bang-målet. Vi har inte resurser att underhålla alla dessa !bangs, vi är ledsna.',
        ],
    ],
    'searchinsearch' => [
        'title' => 'Sök i sök',
        '1' => 'Resultatet sparas i en ny TAB som visas på höger sida av skärmen. Den kallas "Sparade resultat". Här kan du spara enstaka resultat från flera sökningar. TAB:et finns kvar. Om du går in i detta TAB får du din personliga resultatlista med verktyg för att filtrera och sortera resultaten. Klicka på ett annat TAB för att gå tillbaka för ytterligare sökningar. Du kommer inte att ha detta om skärmen är för liten. Mer info (endast på tyska än så länge): <a href="http://blog.suma-ev.de/node/225" target="_blank" rel="noopener"> SUMA blogg</a>.',
    ],
    'selist' => [
        'title' => 'Jag vill lägga till metager.de i listan över sökmotorer i min webbläsare.',
        'explanation_b' => 'Vissa webbläsare behöver en URL. Använd "https://metager.org/meta/meta.ger3?eingabe=%s" utan qoutationstecken. Om det fortfarande finns problem, vänligen <a href="/en/kontakt" target="_blank" rel="noopener">skriv ett e-postmeddelande.</a>',
        'explanation_a' => 'Försök först att installera det senaste tillgängliga pluginet. Använd bara länken under sökfältet, den har en automatisk webbläsardetektering.',
    ],
];
