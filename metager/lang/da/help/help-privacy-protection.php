<?php
return [
    'title' => 'MetaGer - Hjælp',
    'backarrow' => 'Tilbage',
    'tor' => [
        'title' => 'TOR skjult tjeneste <a title="For easy help, click here" href="/hilfe/easy-language/privacy-protection#eh-torhidden" ><img class="easy-help-icon lm-only" src="/img/help-questionmark-icon-lm.svg"/><img class="easy-help-icon dm-only" src="/img/help-questionmark-icon-dm.svg"/></a>',
        '1' => 'I mange år har MetaGer skjult og ikke gemt IP-adresser. Disse adresser er dog midlertidigt synlige på MetaGer-serveren, mens en søgning kører: Hvis MetaGer skulle blive kompromitteret, kunne en angriber læse og gemme dine adresser. For at opfylde de højeste sikkerhedskrav driver vi en MetaGer-instans på Tor-netværket: MetaGer TOR Hidden Service - tilgængelig via: <a href="/tor/" target="_blank" rel="noopener">https://metager.de/tor/</a>. For at bruge den skal du bruge en særlig browser, som du kan downloade fra <a href="https://www.torproject.org/" target="_blank" rel="noopener">https://www.torproject.org/</a>.',
        '2' => 'Du kan få adgang til MetaGer i Tor-browseren på: http://metagerv65pwclop2rsfzg4jwowpavpwd6grhhlvdgsswvo6ii4akgyd.onion .',
    ],
    'proxy' => [
        'title' => 'Åbn anonymt (SafeBrowse) <a title="For easy help, click here" href="/hilfe/easy-language/privacy-protection#eh-proxy" ><img class="easy-help-icon lm-only" src="/img/help-questionmark-icon-lm.svg"/><img class="easy-help-icon dm-only" src="/img/help-questionmark-icon-dm.svg"/></a>',
        'intro' => 'Med „Åbn anonymt“ besøger du et websted, uden at det nogensinde ser din enhed. Siden åbnes i en browser, der kører på vores servere: SafeBrowse. Webstedet har kun med denne browser at gøre og får hverken din IP-adresse eller noget at vide om din enhed, din browser eller dine cookies.',
        'use' => [
            'title' => 'Sådan åbner du en side anonymt',
            'text' => [
                'Klik på „Åbn anonymt“ (skjoldikonet) ved et resultat. Siden åbnes i en ny fane.',
                'Hvis du vil åbne alle resultater anonymt, så slå „Åbn resultater anonymt“ til i indstillingerne. Så går hvert link i et resultat gennem SafeBrowse, og med „Åbn direkte“ besøger du en side uden SafeBrowse.',
            ],
        ],
        'session' => [
            'title' => 'Sessionen',
            'text' => [
                'Det første resultat, du åbner anonymt, starter en session. Hvert følgende åbnes i samme session i sin egen fane. Der surfer du videre som normalt: følger links, udfylder formularer, skriver en ny adresse eller et søgeord i adressefeltet. Du forbliver beskyttet imens.',
                'En session koster tokens fra din MetaGer-nøgle; hvor mange for hvor lang tid, vises før start. Så længe du bruger sessionen, forlænges den automatisk. Når du holder op med at bruge den, slutter den af sig selv kort efter, eller du afslutter den med „Luk sessionen“.',
                'Når sessionen slutter, slettes browseren på vores servere med alt, hvad der var i den: besøgte sider, cookies, logins og downloadede filer. Den næste session starter med en ny, tom browser. Gem derfor downloadede filer på din enhed under „Downloads“ inden da.',
            ],
        ],
        'views' => [
            'title' => 'To visninger',
            'text' => [
                '„Side“ viser en kopi af webstedet uden dets scripts. Du læser, scroller, søger og kopierer i den som på enhver side; dine klik og indtastninger går til browseren på vores servere. „Hele browseren“ viser denne browser som et billede med alle dens faner. Der virker alt, også videoer og sider, som „Side“ ikke kan vise. Du skifter mellem de to i bjælken øverst.',
            ],
        ],
        'nojs' => [
            'title' => 'Uden JavaScript',
            'text' => [
                'Også når JavaScript er slået fra i din browser, åbner „Åbn anonymt“ siden i SafeBrowse. Du ser så en kopi af siden, hvor links, knapper og formularer virker. Den genindlæses efter hvert klik, men ændrer sig ikke af sig selv. Videoer, sidens dialoger og upload af filer virker ikke på denne måde. Hvis din browser blokerer cookies, havner de resultater, du åbner fra én resultatside, stadig i samme session, også når du søger videre derfra.',
            ],
        ],
        'limits' => [
            'title' => 'Hvad „Åbn anonymt“ ikke gør',
            'text' => [
                'Webstedet genkender dig ikke på din enhed, men nok på det, du selv fortæller det: hvis du logger ind eller indtaster personlige oplysninger, ved det, hvem du er. Hvis du heller ikke vil vise dig over for MetaGer, så brug MetaGer via Tor (se ovenfor).',
            ],
        ],
    ],
    'content' => [
        'title' => 'Tvivlsomt indhold / beskyttelse af unge <a title="For easy help, click here" href="/help/easy-language/privacy-protection#eh-content" ><img class="easy-help-icon lm-only" src="/img/help-questionmark-icon-lm.svg"/><img class="easy-help-icon dm-only" src="/img/help-questionmark-icon-dm.svg"/></a>',
        'explanation' => [
            '1' => 'Jeg har modtaget "hits", som jeg ikke bare synes er irriterende, men som efter min mening også indeholder ulovligt indhold!',
            '2' => 'Hvis du finder noget på internettet, som du mener er ulovligt eller skadeligt for mindreårige, kan du kontakte <a href="mailto:hotline@jugendschutz.net" target="_blank" rel="noopener">hotline@jugendschutz.net</a> via e-mail eller besøge <a href="http://www.jugendschutz.net/" target="_blank" rel="noopener">www.jugendschutz.net</a> og udfylde den klageformular, der findes der. Det er nyttigt at give en kort beskrivelse af, hvad du anser for at være ulovligt, og hvordan du er stødt på dette indhold. Du kan også rapportere tvivlsomt indhold direkte til os. For at gøre det skal du sende en e-mail til vores ungdomsbeskyttelsesansvarlige (<a href="mailto:jugendschutz@metager.de" target="_blank" rel="noopener">jugendschutz@metager.de</a>).',
        ],
    ],
    'maps' => [
        'title' => 'MetaGer-kort',
        '1' => 'At bevare privatlivets fred i de globale datagiganters tidsalder har også fået os til at udvikle <a href="https://maps.metager.de" target="_blank">https://maps.metager.de</a>: den (så vidt vi ved) eneste ruteplanlægger, der tilbyder fuld funktionalitet via en browser og app uden at gemme brugerens placering. Alt dette er verificerbart, fordi vores software er open source. Til brug af maps.metager.de anbefaler vi vores hurtige app-version. Du kan downloade vores apps fra <a href="/app" target="_blank">her</a> (eller selvfølgelig også fra Play Store).',
        '2' => 'Denne kortfunktion kan også tilgås fra MetaGer-søgningen (og vice versa). Når du har søgt efter en term i MetaGer, vil du se et nyt søgefokus \'Maps\' i øverste højre hjørne. Hvis du klikker på det, kommer du til et tilsvarende kort.',
        '3' => 'Når kortet indlæses, viser det de punkter (POI\'er = Points of Interest), som MetaGer har fundet, og som også er anført i højre kolonne. Når du zoomer, tilpasser denne liste sig til kortudsnittet. Hvis du holder musen over en markør på kortet eller i listen, fremhæves det tilsvarende element. Klik på \'Detaljer\' for at få flere oplysninger om det pågældende punkt fra databasen nedenfor.',
    ],
    'easy-help' => 'Ved at klikke på symbolet <a title="For easy help, click here" href="/hilfe/easy-language/privacy-protection" ><img class="easy-help-icon lm-only" src="/img/help-questionmark-icon-lm.svg"/><img class="easy-help-icon dm-only" src="/img/help-questionmark-icon-dm.svg"/></a> , får du adgang til en forenklet version af hjælpen.',
    'privacy' => [
        'title' => "Anonymitet og datasikkerhed",
        '1' => 'Sporing af cookies, sessions-ID\'er og IP-adresser <a title="For easy help, click here" href="/hilfe/easy-language/privacy-protection#eh-tracking" ><img class="easy-help-icon lm-only" src="/img/help-questionmark-icon-lm.svg"/><img class="easy-help-icon dm-only" src="/img/help-questionmark-icon-dm.svg"/></a>',
        '2' => 'Ingen af disse bruges, gemmes, opbevares eller på anden måde behandles her hos MetaGer (undtagelse: kortvarig opbevaring til beskyttelse mod hacking og botangreb). Fordi vi anser dette emne for ekstremt vigtigt, har vi også skabt måder at hjælpe dig med at opnå det højeste niveau af sikkerhed: MetaGer TOR Hidden Service og vores anonyme browser.',
        '3' => "Du kan finde flere oplysninger nedenfor. Funktionerne er tilgængelige under 'Services' i navigationslinjen.",
    ],
];
