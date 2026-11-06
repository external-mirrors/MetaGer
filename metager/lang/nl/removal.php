<?php
return [
    'title' => 'Een zoekresultaat laten verwijderen – MetaGer',
    'heading' => 'Een zoekresultaat laten verwijderen',
    'lead' => 'MetaGer is een metazoekmachine: we hebben geen eigen index, maar tonen wat andere zoekmachines vinden. Een resultaat kan daarom alleen echt worden verwijderd op de plek waar het vandaan komt: bij de website en bij de zoekmachines waarvan MetaGer de resultaten toont. Deze pagina vertelt u waar.',
    'toc' => [
        'label' => 'Op deze pagina',
    ],
    'website' => [
        'heading' => 'Neem contact op met de website zelf',
        'text' => 'Dit is de enige weg die overal werkt. Is de pagina verwijderd of gewijzigd, dan haalt elke zoekmachine haar bij het volgende bezoek weg of werkt haar bij, ook MetaGer, zonder dat u het elke zoekmachine apart hoeft te vragen.',
        'tips' => [
            'De meeste websites noemen een contactadres in hun colofon of privacyverklaring. Vraag daar om de inhoud te verwijderen, te corrigeren of ten minste uw naam weg te halen.',
            'Bij persoonsgegevens moeten websites in de EU antwoorden op een verzoek op grond van art. 17 AVG (recht op gegevenswissing).',
            'Beheert u de website zelf: verwijder de pagina zodat ze antwoordt met ‘404’ of ‘410’, of markeer haar met ‘noindex’. Zoekmachines halen haar dan vanzelf weg.',
        ],
        'sources' => 'Voor resultaten uit :sources is dit de enige weg.',
    ],
    'kind' => [
        'heading' => 'Waar gaat het u om?',
        'intro' => 'Helpt de website niet, of kunt u er niet op wachten, dan hebben de zoekmachines eigen procedures. Welke past, hangt af van wat u wilt bereiken.',
        'personal_data' => [
            'summary' => 'Persoonsgegevens: het recht op vergetelheid',
            'text' => 'Een resultaat voor uw naam verwijst naar informatie over u die onjuist, verouderd, niet relevant of buitensporig is. Op grond van art. 17 AVG kunt u een zoekmachine vragen dit resultaat niet meer te tonen bij zoekopdrachten op uw naam. De zoekmachine weegt uw belang af tegen het belang van het publiek bij de informatie; niet elk verzoek slaagt dus. In de kaarten hieronder staat dit onder ‘Persoonsgegevens’.',
        ],
        'outdated' => [
            'summary' => 'Verouderde inhoud',
            'text' => 'De website heeft de pagina al verwijderd of gewijzigd, maar het resultaat toont nog de oude titel of tekst. Dat lost zich vanzelf op bij het volgende bezoek van de zoekmachine; bij de meeste kunt u ook een eerdere update aanvragen. In de kaarten hieronder staat dit onder ‘Verouderde inhoud’.',
        ],
        'legal' => [
            'summary' => 'Auteursrecht en andere juridische redenen',
            'text' => 'Een resultaat maakt inbreuk op uw auteursrecht of andere rechten. Zoekmachines nemen zulke meldingen aan via hun eigen juridische formulieren; meestal kan alleen de rechthebbende of iemand namens die persoon ze indienen. In de kaarten hieronder staat dit onder ‘Auteursrecht en juridisch’.',
        ],
        'illegal' => [
            'summary' => 'Strafbare inhoud',
            'text' => 'Afbeeldingen van seksueel kindermisbruik, inhoud die schadelijk is voor minderjarigen, aanzetten tot haat en dergelijke: meld dit eerst bij een meldpunt. Een meldpunt treedt op tegen de inhoud zelf, bij de hostingprovider, en werkt samen met de politie. Wilt u daarnaast dat MetaGer zo’n resultaat niet meer toont, stuur ons dan alleen het adres (URL) als tekst. Stuur ons nooit de inhoud zelf, ook geen schermafbeeldingen of bijlagen.',
            'hotlines' => 'Meldpunten:',
        ],
    ],
    'providers' => [
        'heading' => 'Vraag het de zoekmachines die MetaGer toont',
        'text' => 'MetaGer doorzoekt het web niet zelf. We vragen de zoekmachines hieronder op het moment dat u zoekt, tonen wat ze teruggeven en bewaren hun antwoord enkele uren in de cache. Verwijdert een van hen een resultaat, dan ontbreekt het daarna ook in wat MetaGer van die zoekmachine krijgt.',
        'which' => 'Van welke zoekmachine een resultaat komt, staat onder het resultaat op de resultatenpagina van MetaGer. Komt het van meerdere, vraag het dan aan elk daarvan.',
        'shows' => 'Op MetaGer bij:',
        'kinds' => [
            'personal_data' => 'Persoonsgegevens',
            'outdated' => 'Verouderde inhoud',
            'legal' => 'Auteursrecht en juridisch',
        ],
        'no_form' => 'Geen formulier, contact opnemen met :name',
        'contact' => 'Contact opnemen met :name',
        'us' => 'Naar het formulier',
        'google' => 'MetaGer krijgt de resultaten van Google via de dienst Serper. Wat Google uit zijn index verwijdert, ontbreekt daarna ook bij MetaGer.',
        'brave' => 'Brave Search heeft een eigen index. Verzoeken over persoonsgegevens gaan per e-mail naar privacy@brave.com met het onderwerp ‘RTBF request’; de gelinkte pagina vertelt wat de e-mail moet bevatten.',
        'mojeek' => 'Mojeek heeft een eigen index en bewerkt zijn resultaten niet met de hand. Om juridische redenen verwijdert Mojeek pagina’s op verzoek; schrijf via de contactpagina en noem de exacte adressen.',
        'staan' => 'Staan is de zoekinterface van European Search Perspective. Staan neemt verzoeken over persoonsgegevens aan via een eigen formulier; vul elk veld in en voeg de bewijsstukken toe. De verwijdering geldt alleen voor de index van Staan.',
        'polias' => 'Polias heeft een eigen index en geen formulier. Schrijf naar hallo@polias.eu met het adres van de pagina en de reden.',
        'pixabay' => 'Pixabay host de afbeeldingen zelf. Gebruik het meldformulier of de meldknop op de pagina van de afbeelding bij Pixabay.',
        'suma' => 'TootNews wordt beheerd door SUMA-EV, de vereniging achter MetaGer. De berichten zelf staan op Mastodon-servers; vraag het eerst daar en gebruik daarna ons formulier.',
        'website' => 'BASE toont wat de repositories bevatten die het doorzoekt. Verwijdert de repository een record, dan verdwijnt het ook uit BASE.',
    ],
    'us' => [
        'heading' => 'Verwijderen uit onze eigen zoekdiensten',
        'text' => 'SUMA-EV beheert eigen zoekindexen: Nolm en TootNews / TrötNews. Alleen daarvoor nemen we verzoeken aan, en alleen via ons formulier, niet per e-mail. MetaGer hoort daar niet bij: het heeft geen eigen index, en daarom staat op deze pagina waar een verwijdering wel werkt. Zo verloopt een verzoek:',
        'steps' => [
            'U kiest de zoekdienst waar het om gaat en bevestigt uw e-mailadres via een link die we u sturen.',
            'U uploadt uw bewijs en noemt per pagina het adres, de zoektermen en de reden.',
            'We controleren eerst het bewijs en daarna elke pagina. Hebt u recht op verwijdering, bijvoorbeeld op grond van art. 17 AVG bij zoekopdrachten op uw naam, dan verbergen we het resultaat in die zoekdienst. Op de website en in alle andere zoekmachines blijft het staan.',
            'We antwoorden binnen een maand. Is een verzoek bijzonder bewerkelijk, dan kan het tot twee maanden langer duren; dat laten we u dan binnen de eerste maand weten. Verbergen we een resultaat niet, dan noemen we u de redenen.',
        ],
        'include' => 'We hebben de exacte adressen (URL’s) van afzonderlijke pagina’s nodig. Hele websites of ‘alles over mijn naam’ kunnen we niet verwijderen.',
        'identity' => 'Als bewijs hebben we een kopie van uw identiteitsbewijs nodig, en bij een verzoek namens iemand anders ook een volmacht en een kopie van diens identiteitsbewijs. Anders zou iedereen kunnen eisen dat we willekeurige adressen verbergen. Op de kopie mag u alles afschermen wat we voor de vergelijking niet nodig hebben. We bewaren haar versleuteld en verwijderen haar 6 maanden na afsluiting van de zaak, samen met uw overige gegevens.',
        'contact' => 'Naar het formulier',
    ],
];
