<?php
return [
    'text' => [
        '2' => [
            '2' => 'En metasökmotor kombinerar resultaten från flera sökmotorer och utvärderar dem igen enligt sina egna kriterier. Det innebär att metasökmotorn inte har något eget index. Därför använder metasökmotorer inte sökrobotar. De använder andra sökmotorers index.',
            '1' => 'För att förklara vad metasökmotorer är, är det vettigt att först kort förklara ungefär hur indexeringen av vanliga sökmotorer fungerar. Vanliga sökmotorer hämtar sina sökresultat från en databas med webbsidor, som också kallas index. Sökmotorerna använder så kallade "crawlers", som samlar in webbsidor och lägger till dem i indexet (databasen). Sökroboten börjar med en uppsättning webbsidor och öppnar alla webbsidor som är länkade dit. Dessa indexeras, dvs. läggs till i indexet. Sedan öppnar crawlern de webbsidor som är länkade till dessa webbsidor och fortsätter på detta sätt.',
        ],
        '1' => 'MetaGer är transparent. Vår <a href=":sourcecode">källkod</a> är fritt licensierad och offentligt tillgänglig för alla att se. Vi lagrar inte användardata och värdesätter dataskydd och integritet. Därför ger vi anonym åtkomst till sökresultaten. Detta är möjligt genom en anonym proxy och TOR-dold åtkomst. Dessutom har MetaGer en transparent organisationsstruktur, eftersom den stöds av den ideella föreningen <a href=":sumalink">SUMA-EV</a> där vem som helst kan bli medlem.',
        '3' => 'En klar fördel med metasökmotorer är att användaren bara behöver en enda sökfråga för att få tillgång till resultaten från flera sökmotorer. Metasökmotorn matar ut de relevanta resultaten i en återigen sorterad resultatlista. MetaGer är inte en renodlad metasökmotor, eftersom vi också använder egna små index.',
        '4' => 'Vi tar rankningarna från våra källsökmotorer och väger samman dem. Dessa rankningar omvandlas sedan till poäng. Ytterligare poäng tilldelas eller dras av för förekomsten av söktermerna i URL:en och i utdraget, samt för överdriven förekomst av specialtecken (t.ex. andra teckenuppsättningar som kyrilliska). Vi använder också en blockeringslista för att ta bort enskilda sidor från resultatlistan. Vi blockerar webbsidor i visningen om vi är juridiskt skyldiga att göra det. Vi förbehåller oss också rätten att blockera webbsidor med bevisligen felaktig information, webbsidor av extremt dålig kvalitet och andra särskilt tvivelaktiga webbsidor.',
        '5' => 'Om det finns ytterligare frågor eller oklarheter är du välkommen att använda vårt kontaktformulär <a href=":contact"></a> och ställa dina frågor till oss!',
        'compliance' => 'Vi följer förfrågningar från myndigheter om vi är juridiskt skyldiga att göra det och kommer till slutsatsen att vår efterlevnad inte bryter mot grundläggande friheter. Vi tar denna granskning på största allvar. Dessutom lagrar vi så lite personuppgifter som möjligt för att minska risken för att behöva lämna ut uppgifter. I tabellen nedan hittar du uppgifter om de förfrågningar från myndigheter som vi har behandlat under de senaste 5 åren. Ytterligare information kommer inom kort.',
    ],
    'head' => [
        '1' => 'Förklaring om öppenhet',
        '2' => 'MetaGer är transparent',
        '3' => 'Vad är en metasökmotor?',
        '4' => 'Vad är fördelen med en metasökmotor?',
        '5' => 'Hur är vår rangordning uppbyggd?',
        'compliance' => 'Hur svarar MetaGer på förfrågningar från myndigheter?',
    ],
    'table' => [
        'compliance' => [
            'th' => [
                'authinfocomp' => 'Uppfyllda förfrågningar om information',
                'authblockcomp' => 'Uppfyllda önskemål om blockering',
            ],
        ],
    ],
    'alt' => [
        'text' => [
            '1' => 'Visuell representation av två index som kompletterar varandra för att bilda ett meta-index',
        ],
    ],
];
