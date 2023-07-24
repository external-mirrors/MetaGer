<?php
return [
    'text' => [
        '2' => [
            '2' => 'Een metasearch engine combineert de resultaten van verschillende zoekmachines en evalueert deze opnieuw volgens zijn eigen criteria. Dit betekent dat de metasearchmachine geen eigen index heeft. Daarom gebruiken metasearch engines geen crawlers. Ze gebruiken de index van andere zoekmachines.',
            '1' => 'Om uit te leggen wat metasearch engines zijn, is het zinvol om eerst in het kort uit te leggen hoe de indexering van reguliere zoekmachines werkt. Reguliere zoekmachines halen hun zoekresultaten uit een database van webpagina\'s, die ook wel een index wordt genoemd. De zoekmachines gebruiken zogenaamde "crawlers", die webpagina\'s verzamelen en toevoegen aan de index (database). De crawler begint met een set webpagina\'s en opent alle webpagina\'s die daar aan gekoppeld zijn. Deze worden geïndexeerd, d.w.z. toegevoegd aan de index. Vervolgens opent de crawler de webpagina\'s waarnaar deze webpagina\'s linken en gaat zo verder.',
        ],
        '3' => 'Een duidelijk voordeel van metasearch engines is dat de gebruiker slechts één zoekopdracht nodig heeft om toegang te krijgen tot de resultaten van verschillende zoekmachines. De metasearch engine geeft de relevante resultaten weer in een opnieuw gesorteerde lijst met resultaten. MetaGer is geen pure metasearchmachine, omdat we ook kleine eigen indexen gebruiken.',
        '4' => 'We nemen de rankings van onze bronzoekmachines en wegen ze. Deze rankings worden vervolgens omgezet in scores. Er worden extra punten toegekend of afgetrokken voor het voorkomen van de zoektermen in de URL en in de snippet, evenals het overmatig voorkomen van speciale tekens (bijv. andere tekensets zoals Cyrillisch). We gebruiken ook een blokkadelijst om individuele pagina\'s uit de resultatenlijst te verwijderen. We blokkeren webpagina\'s in de weergave als we daartoe wettelijk verplicht zijn. We behouden ons ook het recht voor om webpagina\'s met aantoonbaar onjuiste informatie, webpagina\'s van extreem slechte kwaliteit en andere bijzonder dubieuze webpagina\'s te blokkeren.',
        '5' => 'Als er nog vragen of onduidelijkheden zijn, gebruik dan gerust ons <a href=":contact">contactformulier</a> en stel ons uw vragen!',
        'compliance' => 'We voldoen aan verzoeken van autoriteiten als we daartoe wettelijk verplicht zijn en tot de conclusie komen dat onze naleving geen fundamentele vrijheden schendt. We nemen deze beoordeling zeer serieus. Daarnaast slaan we zo min mogelijk persoonlijke gegevens op om het risico te verkleinen dat we gegevens moeten vrijgeven. In de tabel hieronder vind je gegevens over de verzoeken van autoriteiten die we de afgelopen 5 jaar hebben verwerkt. Meer informatie volgt binnenkort.',
        '1' => 'MetaGer is transparant. Onze <a href=":sourcecode">broncode</a> is vrij gelicenseerd en openbaar beschikbaar voor iedereen. We slaan geen gebruikersgegevens op en hechten veel waarde aan gegevensbescherming en privacy. Daarom verlenen we anonieme toegang tot de zoekresultaten. Dit is mogelijk via een anonieme proxy en TOR-verdekte toegang. Daarnaast heeft MetaGer een transparante organisatiestructuur, omdat het wordt ondersteund door de non-profit vereniging <a href=":sumalink">SUMA-EV</a> waarvan iedereen lid kan worden.',
    ],
    'table' => [
        'compliance' => [
            'th' => [
                'authinfocomp' => 'Verzoeken om informatie ingewilligd',
                'authblockcomp' => 'Vervulde blokkeerverzoeken',
            ],
        ],
    ],
    'alt' => [
        'text' => [
            '1' => 'Visuele weergave van twee indexen die elkaar aanvullen om een meta-index te vormen',
        ],
    ],
    'head' => [
        '1' => 'Transparantieverklaring',
        '2' => 'MetaGer is transparant',
        '3' => 'Wat is een meta-zoekmachine?',
        '4' => 'Wat is het voordeel van een meta-zoekmachine?',
        '5' => 'Hoe is onze ranglijst opgebouwd?',
        'compliance' => 'Hoe reageert MetaGer op verzoeken van autoriteiten?',
    ],
];
