<?php
return [
    'title' => 'MetaGer - Hulp',
    'backarrow' => 'Terug',
    'tor' => [
        'title' => 'Verborgen TOR-service <a title="For easy help, click here" href="/hilfe/easy-language/privacy-protection#eh-torhidden" ><img class="easy-help-icon lm-only" src="/img/help-questionmark-icon-lm.svg"/><img class="easy-help-icon dm-only" src="/img/help-questionmark-icon-dm.svg"/></a>',
        '1' => 'Al vele jaren verbergt MetaGer IP-adressen en slaat ze niet op. Deze adressen zijn echter tijdelijk zichtbaar op de MetaGer server terwijl een zoekopdracht wordt uitgevoerd: als MetaGer in gevaar zou komen, zou een aanvaller je adressen kunnen lezen en opslaan. Om aan de hoogste veiligheidseisen te voldoen, hebben we een MetaGer-instantie op het Tor-netwerk: de MetaGer TOR Hidden Service - toegankelijk via: <a href="/tor/" target="_blank" rel="noopener">https://metager.de/tor/</a>. Om het te gebruiken heb je een speciale browser nodig, die je kunt downloaden van <a href="https://www.torproject.org/" target="_blank" rel="noopener">https://www.torproject.org/</a>.',
        '2' => 'Je kunt toegang krijgen tot MetaGer in de Tor browser op: http://metagerv65pwclop2rsfzg4jwowpavpwd6grhhlvdgsswvo6ii4akgyd.onion .',
    ],
    'proxy' => [
        'title' => 'Anoniem openen (SafeBrowse) <a title="For easy help, click here" href="/hilfe/easy-language/privacy-protection#eh-proxy" ><img class="easy-help-icon lm-only" src="/img/help-questionmark-icon-lm.svg"/><img class="easy-help-icon dm-only" src="/img/help-questionmark-icon-dm.svg"/></a>',
        'intro' => 'Met „Anoniem openen” bezoekt u een website zonder dat die ooit uw apparaat te zien krijgt. De pagina wordt geopend in een browser die op onze servers draait: SafeBrowse. De website heeft alleen met die browser te maken en komt uw IP-adres niet te weten, en ook niets over uw apparaat, uw browser of uw cookies.',
        'use' => [
            'title' => 'Zo opent u een pagina anoniem',
            'text' => [
                'Klik bij een resultaat op „Anoniem openen” (het schildpictogram). De pagina opent in een nieuw tabblad.',
                'Wilt u elk resultaat anoniem openen, zet dan in de instellingen „Resultaten anoniem openen” aan. Dan loopt elke link van een resultaat via SafeBrowse, en met „Direct openen” bezoekt u een pagina zonder SafeBrowse.',
            ],
        ],
        'session' => [
            'title' => 'De sessie',
            'text' => [
                'Het eerste resultaat dat u anoniem opent, start een sessie. Elk volgend resultaat opent in dezelfde sessie, in een eigen tabblad. Daarin surft u gewoon verder: links volgen, formulieren invullen, een nieuw adres of een zoekterm in de adresbalk typen. U blijft daarbij beschermd.',
                'Een sessie kost tokens van uw MetaGer-sleutel; hoeveel voor welke tijd, ziet u vóór de start. Zolang u de sessie gebruikt, wordt die automatisch verlengd. Gebruikt u haar niet meer, dan eindigt ze kort daarna vanzelf, of u beëindigt haar met „Sessie afsluiten”.',
                'Als de sessie eindigt, wordt de browser op onze servers gewist met alles wat erin zat: bezochte pagina’s, cookies, aanmeldingen en gedownloade bestanden. De volgende sessie begint met een nieuwe, lege browser. Sla gedownloade bestanden daarom eerst via „Downloads” op uw apparaat op.',
            ],
        ],
        'views' => [
            'title' => 'Twee weergaven',
            'text' => [
                '„Pagina” toont een kopie van de website zonder haar scripts. U leest, scrolt, zoekt en kopieert erin zoals op elke pagina; uw klikken en invoer gaan naar de browser op onze servers. „Volledige browser” toont die browser als afbeelding, met al zijn tabbladen. Daar werkt alles, ook video’s en pagina’s die „Pagina” niet kan tonen. U wisselt tussen beide in de balk bovenaan.',
            ],
        ],
        'nojs' => [
            'title' => 'Zonder JavaScript',
            'text' => [
                'Ook als JavaScript in uw browser is uitgeschakeld, opent „Anoniem openen” de pagina in SafeBrowse. U ziet dan een kopie van de pagina waarin links, knoppen en formulieren werken. Ze wordt na elke klik opnieuw geladen, maar verandert niet vanzelf. Video’s, dialoogvensters van de pagina en het uploaden van bestanden werken zo niet. Blokkeert uw browser cookies, dan komen de resultaten die u vanaf één resultatenpagina opent toch in dezelfde sessie terecht, ook als u van daaruit verder zoekt.',
            ],
        ],
        'limits' => [
            'title' => 'Wat „Anoniem openen” niet doet',
            'text' => [
                'De website herkent u niet aan uw apparaat, maar wel aan wat u haar zelf vertelt: als u zich aanmeldt of persoonlijke gegevens invoert, weet ze wie u bent. Wilt u zich ook tegenover MetaGer niet laten zien, gebruik MetaGer dan via Tor (zie hierboven).',
            ],
        ],
    ],
    'content' => [
        'title' => 'Bedenkelijke inhoud / Jeugdbescherming <a title="For easy help, click here" href="/help/easy-language/privacy-protection#eh-content" ><img class="easy-help-icon lm-only" src="/img/help-questionmark-icon-lm.svg"/><img class="easy-help-icon dm-only" src="/img/help-questionmark-icon-dm.svg"/></a>',
        'explanation' => [
            '1' => 'Ik heb "hits" ontvangen die ik niet alleen vervelend vind, maar die naar mijn mening ook illegale inhoud bevatten!',
            '2' => 'Als je iets op internet vindt dat je illegaal of schadelijk voor minderjarigen vindt, kun je per e-mail contact opnemen met <a href="mailto:hotline@jugendschutz.net" target="_blank" rel="noopener">hotline@jugendschutz.net</a> of ga naar <a href="http://www.jugendschutz.net/" target="_blank" rel="noopener">www.jugendschutz.net</a> en vul het klachtenformulier in dat daar beschikbaar is. Het is handig om kort aan te geven wat je ontoelaatbaar vindt en hoe je aan deze inhoud bent gekomen. Je kunt twijfelachtige inhoud ook rechtstreeks aan ons melden. Stuur hiervoor een e-mail naar onze jeugdbeschermingsmedewerker (<a href="mailto:jugendschutz@metager.de" target="_blank" rel="noopener">jugendschutz@metager.de</a>).',
        ],
    ],
    'maps' => [
        'title' => 'MetaGer Kaarten',
        '1' => 'Het behoud van privacy in het tijdperk van wereldwijde datagiganten heeft er ook toe geleid dat we <a href="https://maps.metager.de" target="_blank">https://maps.metager.de</a> hebben ontwikkeld: de (voor zover wij weten) enige routeplanner die volledige functionaliteit biedt via een browser en app zonder locaties van gebruikers op te slaan. Dit is allemaal controleerbaar omdat onze software open source is. Voor het gebruik van maps.metager.de raden we onze snelle app-versie aan. Je kunt onze apps downloaden van <a href="/app" target="_blank">hier</a> (of natuurlijk ook van de Play Store).',
        '2' => 'Deze kaartfunctie is ook toegankelijk vanuit de MetaGer zoekfunctie (en omgekeerd). Zodra je hebt gezocht naar een term in MetaGer, zie je een nieuwe zoekfocus \'Kaarten\' in de rechterbovenhoek. Door erop te klikken ga je naar de bijbehorende kaart.',
        '3' => 'Bij het laden toont de kaart de punten (POI\'s = Points of Interest) gevonden door MetaGer, die ook in de rechterkolom staan. Bij het zoomen past deze lijst zich aan de kaartsectie aan. Als je met je muis over een marker op de kaart of in de lijst beweegt, wordt het overeenkomstige item gemarkeerd. Klik op \'Details\' voor meer informatie over dat punt uit de onderstaande database.',
    ],
    'easy-help' => 'Door op het symbool <a title="For easy help, click here" href="/hilfe/easy-language/privacy-protection" ><img class="easy-help-icon lm-only" src="/img/help-questionmark-icon-lm.svg"/><img class="easy-help-icon dm-only" src="/img/help-questionmark-icon-dm.svg"/></a> te klikken, krijg je toegang tot een vereenvoudigde versie van de Help.',
    'privacy' => [
        'title' => "Anonimiteit en gegevensbeveiliging",
        '1' => 'Traceercookies, sessie-ID\'s en IP-adressen <a title="For easy help, click here" href="/hilfe/easy-language/privacy-protection#eh-tracking" ><img class="easy-help-icon lm-only" src="/img/help-questionmark-icon-lm.svg"/><img class="easy-help-icon dm-only" src="/img/help-questionmark-icon-dm.svg"/></a>',
        '2' => 'Niets hiervan wordt gebruikt, opgeslagen, bewaard of anderszins verwerkt hier bij MetaGer (uitzondering: kortstondige opslag voor bescherming tegen hacken en botaanvallen). Omdat we dit onderwerp erg belangrijk vinden, hebben we ook manieren gecreëerd om je te helpen het hoogste niveau van beveiliging te bereiken: de MetaGer TOR Hidden Service en onze anonieme browser.',
        '3' => "Hieronder vind je meer informatie. De functies zijn toegankelijk onder 'Services' in de navigatiebalk.",
    ],
];
