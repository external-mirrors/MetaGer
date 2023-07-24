<?php
return [
    'head' => [
        '1' => 'Gennemsigtighedserklæring',
        '2' => 'MetaGer er gennemsigtig',
        '4' => 'Hvad er fordelen ved en metasøgemaskine?',
        '5' => 'Hvordan er vores rangliste sammensat?',
        '3' => 'Hvad er en metasøgemaskine?',
        'compliance' => 'Hvordan reagerer MetaGer på anmodninger fra myndighederne?',
    ],
    'text' => [
        '1' => 'MetaGer er gennemsigtig. Vores <a href=":sourcecode">kildekode</a> er frit licenseret og offentligt tilgængelig, så alle kan se den. Vi gemmer ikke brugerdata og værdsætter databeskyttelse og privatlivets fred. Derfor giver vi anonym adgang til søgeresultaterne. Dette er muligt gennem en anonym proxy og TOR-skjult adgang. Derudover har MetaGer en gennemsigtig organisationsstruktur, da den understøttes af non-profit-foreningen <a href=":sumalink">SUMA-EV</a>, som alle kan blive medlem af.',
        '2' => [
            '1' => 'For at forklare, hvad metasøgemaskiner er, giver det mening først kort at forklare, hvordan indekseringen af almindelige søgemaskiner fungerer. Almindelige søgemaskiner får deres søgeresultater fra en database med websider, som også kaldes et indeks. Søgemaskinerne bruger såkaldte "crawlere", som indsamler websider og tilføjer dem til indekset (databasen). Crawleren starter med et sæt websider og åbner alle de websider, der er linket til. Disse indekseres, dvs. føjes til indekset. Derefter åbner crawleren de websider, der er linket til på disse websider, og fortsætter på denne måde.',
            '2' => 'En metasøgemaskine kombinerer resultaterne fra flere søgemaskiner og evaluerer dem igen efter sine egne kriterier. Det betyder, at metasøgemaskinen ikke har sit eget indeks. Derfor bruger metasøgemaskiner ikke crawlere. De bruger indekset fra andre søgemaskiner.',
        ],
        '3' => 'En klar fordel ved metasøgemaskiner er, at brugeren kun behøver en enkelt søgeforespørgsel for at få adgang til resultaterne fra flere søgemaskiner. Metasøgemaskinen udsender de relevante resultater i en endnu en gang sorteret liste over resultater. MetaGer er ikke en ren metasøgemaskine, da vi også bruger vores egne små indeks.',
        '4' => 'Vi tager placeringerne fra vores kildesøgemaskiner og vejer dem. Disse placeringer konverteres derefter til point. Yderligere point tildeles eller fratrækkes for forekomsten af søgetermer i URL\'en og i uddraget, samt overdreven forekomst af specialtegn (f.eks. andre tegnsæt som kyrillisk). Vi bruger også en blokeringsliste til at fjerne individuelle sider fra resultatlisten. Vi blokerer websider i visningen, hvis vi er juridisk forpligtet til at gøre det. Vi forbeholder os også ret til at blokere websider med påviseligt forkerte oplysninger, websider af ekstremt dårlig kvalitet og andre særligt tvivlsomme websider.',
        '5' => 'Hvis der er yderligere spørgsmål eller uklarheder, er du velkommen til at bruge vores <a href=":contact">kontaktformular</a> og stille os dine spørgsmål!',
        'compliance' => 'Vi efterkommer anmodninger fra myndigheder, hvis vi er juridisk forpligtede til det og konkluderer, at vores efterlevelse ikke krænker grundlæggende frihedsrettigheder. Vi tager denne gennemgang meget alvorligt. Derudover gemmer vi så få personlige data som muligt for at reducere risikoen for at skulle frigive data. I tabellen nedenfor finder du data om de anmodninger fra myndigheder, vi har behandlet i løbet af de sidste 5 år. Yderligere oplysninger følger snarest.',
    ],
    'table' => [
        'compliance' => [
            'th' => [
                'authinfocomp' => 'Opfyldte anmodninger om information',
                'authblockcomp' => 'Opfyldte anmodninger om blokering',
            ],
        ],
    ],
    'alt' => [
        'text' => [
            '1' => 'Visuel repræsentation af to indeks, der supplerer hinanden for at danne et meta-indeks',
        ],
    ],
];
