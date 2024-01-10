<?php
return [
    'title' => 'MetaGer - Hjälp',
    'backarrow' => 'Tillbaka',
    'mehrwortsuche' => [
        '2' => 'Exempel: sökning efter Shakespears <div class="well well-sm">to be or not to be</div> kommer att ge många resultat, men den exakta frasen kommer endast att hittas med <div class="well well-sm">"to be or nor to be"</div>',
    ],
    'urls' => [
        'title' => 'Utesluta URL-adresser',
        'explanation' => 'Du kan utesluta sökresultat som innehåller specifika ord i sina resultatlänkar genom att använda "-url:" i din sökning.',
        'example_b' => '<i>min sökning</i> -url:dog',
        'example_a' => 'Exempel: Du vill exkludera resultat där ordet "hund" förekommer i resultatlänken:',
    ],
    'bang' => [
        '1' => 'MetaGer stöder i begränsad utsträckning en skrivstil som ofta kallas \'!bang\' syntax.<br>En \'!bang\' börjar alltid med ett utropstecken och innehåller inga mellanslag. Exempel är \'!twitter\' eller \'!facebook\'.<br>När en stödd !bang används i sökfrågan, visas en post i våra snabbtips, så att du kan fortsätta sökningen med respektive tjänst (Twitter eller Facebook) genom att trycka på en knapp.',
        'title' => 'MetaGer Kartor <a title="For easy help, click here" href="/hilfe/easy-language/services#eh-maps" ><img class="easy-help-icon lm-only" src="/img/help-questionmark-icon-lm.svg"/><img class="easy-help-icon dm-only" src="/img/help-questionmark-icon-dm.svg"/></a>',
        '2' => 'Varför öppnas inte !bangs direkt?',
        '3' => '!bang-"omdirigeringarna" är en del av våra snabbtips och kräver ytterligare ett "klick." Detta var ett svårt beslut för oss, eftersom det gör !bangs mindre användbara. Men det är tyvärr nödvändigt eftersom de länkar som omdirigeringen sker till inte kommer från oss utan från en tredje part, DuckDuckGo.<p>Vi ser alltid till att våra användare behåller kontrollen i alla lägen. Därför skyddar vi på två sätt: För det första överförs aldrig den inmatade söktermen till DuckDuckGo, utan endast !bang. För det andra bekräftar användaren uttryckligen besöket på !bang-målet. Tyvärr kan vi på grund av personalskäl för närvarande inte kontrollera eller underhålla alla dessa !bangs själva.',
    ],
    'selist' => [
        'title' => 'Lägg till MetaGer i din webbläsares lista över sökmotorer <a title="For easy help, click here" href="/hilfe/easy-language/functions#eh-selist"><img class="easy-help-icon lm-only" src="/img/help-questionmark-icon-lm.svg"/><img class="easy-help-icon dm-only" src="/img/help-questionmark-icon-dm.svg"/></a>',
        'explanation_b' => 'Vissa webbläsare kräver att du anger en URL; den ska vara "https://metager.de/meta/meta.ger3?input=%s" utan citattecken. Du kan generera URL:en själv genom att söka efter något på metager.de och sedan ersätta det som står bakom "input=" i adressfältet med %s. Om du fortfarande har några problem, vänligen kontakta oss: <a href="/kontalt" target="_blank" rel="noopener">Kontaktformulär</a>',
        'explanation_a' => 'Försök först att installera det aktuella pluginet. För att installera klickar du bara på länken direkt under sökrutan. Din webbläsare bör redan ha upptäckts där.',
    ],
    'searchfunction' => [
        'title' => "Sökfunktioner",
    ],
    'stopwords' => [
        '3' => "bil ny -bmw",
        '2' => "Exempel: Du letar efter en ny bil, men definitivt inte en BMW. Din input skulle vara:",
        'title' => 'Stoppord <a title="For easy help, click here" href="/hilfe/easy-language/functions#eh-stopwordsearch"><img class="easy-help-icon lm-only" src="/img/help-questionmark-icon-lm.svg"/><img class="easy-help-icon dm-only" src="/img/help-questionmark-icon-dm.svg"/></a>',
        '1' => "Om du vill utesluta sökresultat i MetaGer som innehåller specifika ord (uteslutningsord / stoppord), kan du göra det genom att prefixa dessa ord med ett minustecken.",
    ],
    'multiwordsearch' => [
        '4' => [
            'example' => '"det runda bordet"',
            'text' => "Med en frassökning kan du söka efter ordkombinationer istället för enskilda ord. Ange helt enkelt de ord som ska visas tillsammans inom citationstecken.",
        ],
        'title' => 'Flerordssökning <a title="For easy help, click here" href="/hilfe/easy-language/functions#eh-severalwords"><img class="easy-help-icon lm-only" src="/img/help-questionmark-icon-lm.svg"/><img class="easy-help-icon dm-only" src="/img/help-questionmark-icon-dm.svg"/></a>',
        '3' => [
            'example' => '"den" "runda" "bord"',
            'text' => "Om du vill se till att ord från din sökning också visas i resultaten måste du sätta dem inom citattecken.",
        ],
        '2' => "Om detta inte räcker för dig har du två alternativ för att göra din sökning mer exakt:",
        '1' => "När du söker efter mer än ett ord i MetaGer försöker vi automatiskt ge resultat där alla ord visas eller kommer så nära som möjligt.",
    ],
    'key' => [
        'title' => 'Lägg till MetaGer-nyckel <a title="For easy help, click here" href="/hilfe/easy-language/functions#eh-keyexplain"><img class="easy-help-icon lm-only" src="/img/help-questionmark-icon-lm.svg"/><img class="easy-help-icon dm-only" src="/img/help-questionmark-icon-dm.svg"/></a>',
        '1' => 'MetaGer-nyckeln installeras automatiskt i din webbläsare och används. Du behöver inte göra något annat. Om du vill använda MetaGer-nyckeln på andra enheter finns det flera sätt att konfigurera MetaGer-nyckeln:',
        '2' => 'Inloggningskod <br>På <a href = "/keys/key/enter">hanteringssidan</a> för MetaGer-nyckeln kan du använda inloggningskoden för att lägga till din nyckel till en annan enhet. Ange helt enkelt den sexsiffriga koden när du loggar in. Inloggningskoden kan bara användas en gång och är bara giltig så länge fönstret är öppet.',
        '3' => 'Kopiera URL <br> När du är på <a href = "/keys/key/enter">hanteringssidan</a> för MetaGer-nyckeln finns det ett alternativ att kopiera en URL. Denna URL kan användas för att spara alla MetaGer-inställningar, inklusive MetaGer-nyckeln, på en annan enhet.',
        '4' => 'Spara fil <br> När du är på <a href = "/keys/key/enter">hanteringssidan</a> för MetaGer-nyckeln, finns det ett alternativ för att spara en fil. Detta sparar din MetaGer-nyckel som en fil. Du kan sedan använda den här filen på en annan enhet för att logga in med din nyckel.',
        '5' => 'Skanna QR-kod <br>Alternativt kan du också skanna QR-koden som visas på <a href = "/keys/key/enter">hanteringssidan</a> för att logga in med en annan enhet.',
        '6' => 'Ange MetaGer-nyckel manuellt <br>Du kan också ange nyckeln manuellt på en annan enhet.',
        'colors' => [
            'title' => 'Färgad MetaGer-nyckel',
            '1' => 'För att du enkelt ska kunna se om du söker annonsfritt har vi gett våra nyckelsymboler färger. Nedan finns förklaringar till motsvarande färger:',
            'grey' => 'Grå: Du har inte ställt in en nyckel. Du använder den fria sökningen.',
            'red' => 'Röd: Om din nyckelsymbol är röd betyder det att nyckeln är tom. Du har använt upp alla annonsfria sökningar. Du kan ladda nyckeln på sidan för nyckelhantering.',
            'green' => 'Grön: Om nyckelsymbolen är grön använder du en laddad nyckel.',
            'yellow' => 'Gul: Om du ser en gul nyckel har du fortfarande ett saldo på 30 tokens. Dina sökningar håller på att ta slut. Vi rekommenderar att du laddar upp nyckeln snart.',
        ],
    ],
    'exactsearch' => [
        'title' => 'Exakt sökning <a title="For easy help, click here" href="/hilfe/easy-language/functions#exactsearch"><img class="easy-help-icon lm-only" src="/img/help-questionmark-icon-lm.svg"/><img class="easy-help-icon dm-only" src="/img/help-questionmark-icon-dm.svg"/></a>',
        '1' => "Om du vill hitta ett visst ord i MetaGers sökresultat kan du sätta ett plustecken framför ordet. När du använder ett plustecken och citattecken söks en fras exakt som du angav den.",
        '2' => "Exempel: S",
        '3' => 'Exempel: ',
        'example' => [
            '1' => "+exempelord",
            '2' => '+"exempel på fras"',
        ],
    ],
    'easy-help' => 'Genom att klicka på symbolen <a title="For easy help, click here" href="/hilfe/easy-language/services" ><img class="easy-help-icon lm-only" src="/img/help-questionmark-icon-lm.svg"/><img class="easy-help-icon dm-only" src="/img/help-questionmark-icon-dm.svg"/></a> , kommer du till en förenklad version av hjälpen.',
];
