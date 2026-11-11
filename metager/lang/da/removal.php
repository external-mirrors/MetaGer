<?php
return [
    'title' => 'Få et søgeresultat fjernet – MetaGer',
    'meta_description' => 'Sådan får du et søgeresultat fjernet: retten til at blive glemt, forældet indhold, ophavsret. Hvor din anmodning virker: Google, Brave og andre.',
    'heading' => 'Få et søgeresultat fjernet',
    'lead' => 'MetaGer er en metasøgemaskine: Vi har ikke vores eget indeks, men viser, hvad andre søgemaskiner finder. Et resultat kan derfor kun fjernes rigtigt dér, hvor det kommer fra: på hjemmesiden og hos de søgemaskiner, hvis resultater MetaGer viser. Denne side fortæller dig hvor.',
    'toc' => [
        'label' => 'På denne side',
    ],
    'website' => [
        'heading' => 'Kontakt selve hjemmesiden',
        'text' => 'Det er den eneste vej, der virker overalt. Når siden er slettet eller ændret, fjerner eller opdaterer alle søgemaskiner den ved deres næste besøg, også MetaGer, uden at du behøver at spørge hver enkelt.',
        'tips' => [
            'De fleste hjemmesider oplyser en kontaktadresse på kontaktsiden eller i privatlivspolitikken. Bed dem om at slette indholdet, rette det eller i det mindste fjerne dit navn.',
            'Når det gælder personoplysninger, skal hjemmesider i EU besvare en anmodning efter GDPR art. 17 (retten til sletning).',
            'Hvis du selv driver hjemmesiden: Slet siden, så den svarer med ”404” eller ”410”, eller markér den med ”noindex”. Søgemaskinerne fjerner den så af sig selv.',
        ],
        'sources' => 'For resultater fra :sources er det den eneste vej.',
    ],
    'kind' => [
        'heading' => 'Hvad drejer det sig om?',
        'intro' => 'Hvis hjemmesiden ikke hjælper, eller du ikke kan vente på den, har søgemaskinerne deres egne procedurer. Hvilken der passer, afhænger af, hvad du vil opnå.',
        'personal_data' => [
            'summary' => 'Personoplysninger: retten til at blive glemt',
            'text' => 'Et resultat for dit navn fører til oplysninger om dig, der er urigtige, forældede, irrelevante eller overdrevne. Efter GDPR art. 17 kan du bede en søgemaskine om ikke længere at vise resultatet ved søgninger på dit navn. Søgemaskinen afvejer din interesse mod offentlighedens interesse i oplysningerne, så ikke alle anmodninger lykkes. I kortene nedenfor står det under ”Personoplysninger”.',
        ],
        'outdated' => [
            'summary' => 'Forældet indhold',
            'text' => 'Hjemmesiden har allerede slettet eller ændret siden, men resultatet viser stadig den gamle titel eller tekst. Det løser sig selv ved søgemaskinens næste besøg; hos de fleste kan du også bede om en tidligere opdatering. I kortene nedenfor står det under ”Forældet indhold”.',
        ],
        'legal' => [
            'summary' => 'Ophavsret og andre juridiske grunde',
            'text' => 'Et resultat krænker din ophavsret eller andre rettigheder. Søgemaskiner modtager den slags henvendelser via deres egne juridiske formularer; som regel kan kun rettighedshaveren eller en, der handler på dennes vegne, indsende dem. I kortene nedenfor står det under ”Ophavsret og jura”.',
        ],
        'illegal' => [
            'summary' => 'Strafbart indhold',
            'text' => 'Fremstillinger af seksuelt misbrug af børn, indhold, der er skadeligt for mindreårige, tilskyndelse til had og lignende: Anmeld det først til en rapporteringstjeneste. Den går efter selve indholdet hos hostingudbyderen og samarbejder med politiet. Hvis du også vil have, at MetaGer ikke længere viser et sådant resultat, så send os kun adressen (URL’en) som tekst i en e-mail. Send os aldrig selve indholdet, heller ikke skærmbilleder eller vedhæftede filer.',
            'hotlines' => 'Rapporteringstjenester:',
        ],
    ],
    'providers' => [
        'heading' => 'Spørg de søgemaskiner, MetaGer viser',
        'text' => 'MetaGer gennemsøger ikke selv nettet. Vi spørger søgemaskinerne nedenfor i det øjeblik, du søger, viser det, de leverer, og gemmer deres svar midlertidigt i nogle få timer. Når en af dem fjerner et resultat, mangler det derefter også i det, MetaGer får fra den.',
        'which' => 'Hvilken søgemaskine et resultat kommer fra, står under resultatet på MetaGers resultatside. Kommer det fra flere, så spørg hver af dem.',
        'shows' => 'På MetaGer under:',
        'kinds' => [
            'personal_data' => 'Personoplysninger',
            'outdated' => 'Forældet indhold',
            'legal' => 'Ophavsret og jura',
        ],
        'no_form' => 'Ingen formular, kontakt :name',
        'contact' => 'Kontakt :name',
        'servers' => 'De Mastodon-servere, som TootNews søger i',
        'google' => 'MetaGer får Googles resultater gennem tjenesten Serper. Det, Google fjerner fra sit indeks, mangler derefter også på MetaGer.',
        'brave' => 'Brave Search driver sit eget indeks. Anmodninger om personoplysninger sendes pr. e-mail til privacy@brave.com med emnet ”RTBF request”; den linkede side fortæller, hvad e-mailen skal indeholde.',
        'mojeek' => 'Mojeek driver sit eget indeks og redigerer ikke sine resultater manuelt. Af juridiske grunde fjerner Mojeek sider efter anmodning; skriv via kontaktsiden, og angiv de præcise adresser.',
        'staan' => 'Staan er søgegrænsefladen fra European Search Perspective. Staan modtager anmodninger om personoplysninger via sin egen formular; udfyld alle felter, og vedhæft dokumentationen. Fjernelsen gælder kun Staans indeks.',
        'polias' => 'Polias driver sit eget indeks og har ingen formular. Skriv til hallo@polias.eu med sidens adresse og begrundelsen.',
        'pixabay' => 'Pixabay leverer selv billederne. Brug anmeldelsesformularen eller anmeldelsesknappen på billedets side på Pixabay.',
        'suma' => 'TootNews drives af SUMA-EV, foreningen bag MetaGer, og søger i opslag på nogle få Mastodon-servere. Ved hvert resultat står, hvilken server det kommer fra; bed dem, der driver serveren, om at slette eller skjule opslaget.',
        'nolm' => 'Nolm er SUMA-EV’s egen søgemaskine, adskilt fra MetaGer. Nolm søger i Open Web Index. Anmod om fjernelse via Open Web Index’ formular til fjernelse: Det, der fjernes dér, forsvinder også fra Nolm.',
        'website' => 'BASE viser, hvad de arkiver, det gennemsøger, indeholder. Når arkivet fjerner en post, forsvinder den også fra BASE.',
    ],
    'questions' => [
        'text' => 'Har du flere spørgsmål om at få et søgeresultat fjernet?',
        'link' => 'Skriv en e-mail til os',
    ],
    'trademarks' => [
        'text' => 'Navnene på andre udbyderes søgemaskiner er varemærker tilhørende deres respektive ejere. Vi bruger dem kun til at vise, hvor du kan anmode om fjernelse.',
        'ref' => 'Varemærkebemærkning',
    ],
];
