<?php
return [
    'title' => 'MetaGer - Hulp',
    'backarrow' => ' Terug',
    'suchfunktion' => [
        'title' => 'Zoekfuncties',
    ],
    'stopworte' => [
        'title' => 'Afzonderlijke woorden uitsluiten',
        '1' => 'Als je woorden binnen het zoekresultaat wilt uitsluiten, moet je een "-" voor dat woord zetten',
        '2' => 'Voorbeeld: Je bent op zoek naar een nieuwe auto, maar geen BMW. Dan moet uw zoekopdracht <div class="well well-sm">nieuwe auto -bmw zijn.</div>',
        '3' => 'auto nieuw -bmw',
    ],
    'mehrwortsuche' => [
        'title' => 'Zoeken naar meer dan één woord',
        '1' => 'Zonder aanhalingstekens krijg je resultaten die één of enkele woorden van je zoekterm bevatten. Gebruik aanhalingstekens voor het zoeken naar exacte zinnen, citaten....',
        '2' => 'Voorbeeld: zoeken op Shakespears <div class="well well-sm">to be or not to be</div> levert veel resultaten op, maar de exacte zin wordt alleen gevonden met <div class="well well-sm">"to be or nor to be".</div>',
        '3' => [
            'example' => '"rondetafel" "beslissing"',
            'text' => 'Gebruik aanhalingstekens om je zoekwoorden in de resultatenlijst te krijgen.',
        ],
        '4' => [
            'example' => '"besluit rond de tafel"',
            'text' => 'Zet woorden of zinnen tussen aanhalingstekens om exacte combinaties te zoeken.',
        ],
    ],
    'urls' => [
        'explanation' => 'Gebruik "-url:" om zoekresultaten met opgegeven woorden uit te sluiten.',
        'example_b' => 'Typ <i>mijn zoekwoorden</i> -url:hond',
        'example_a' => 'Voorbeeld: Je wilt het woord "hond" niet in de resultaten:',
        'title' => 'URL\'s uitsluiten',
    ],
    'bang' => [
        'title' => 'knallen',
        '1' => 'MetaGer gebruikt een speciale spelling genaamd "!bang syntax". Een !bang begint met de "!" en bevat geen spaties ("!twitter", "!facebook" bijvoorbeeld). Als je een !bang gebruikt die door MetaGer wordt ondersteund, zie je een nieuw item in de "Quicktips". We verwijzen je dan naar de gespecificeerde service (klik op de knop).',
    ],
    'faq' => [
        '18' => [
            'h' => 'Waarom worden de !bangs niet direct geopend?',
            'b' => 'De !bang -"redirections" maken deel uit van onze sneltips en ze hebben een extra klik nodig. We moesten kiezen tussen gebruiksgemak en controle over de gegevens. We vinden het nodig om te laten zien dat de links eigendom zijn van derden (DuckDuckGo). Er is dus een tweezijdige bescherming: aan de ene kant geven we je zoekwoorden niet door, maar alleen de !bang naar DuckDuckGo. Anderzijds bevestigt de gebruiker de !bang-doel expliciet. We hebben niet de middelen om al deze !bangs te onderhouden, het spijt ons.',
        ],
    ],
    'searchinsearch' => [
        'title' => 'Zoeken in zoeken',
        '1' => 'Het resultaat wordt opgeslagen in een nieuwe TAB aan de rechterkant van het scherm. Deze heet "Opgeslagen resultaten". Je kunt hier afzonderlijke resultaten van meerdere zoekopdrachten opslaan. De TAB blijft bewaard. Als je deze TAB invoert, krijg je je persoonlijke resultatenlijst met hulpmiddelen om de resultaten te filteren en sorteren. Klik op een andere TAB om terug te gaan voor verdere zoekopdrachten. Dit is niet mogelijk als het scherm te klein is. Meer informatie (tot nu toe alleen in het Duits): <a href="http://blog.suma-ev.de/node/225" target="_blank" rel="noopener"> SUMA blog</a>.',
    ],
    'selist' => [
        'title' => 'Ik wil metager.de toevoegen aan de zoekmachinelijst van mijn browser.',
        'explanation_b' => 'Sommige browsers hebben een URL nodig. Gebruik "https://metager.org/meta/meta.ger3?eingabe=%s" zonder qoutation marks. Als er nog steeds problemen zijn, schrijf dan <a href="/en/kontakt" target="_blank" rel="noopener">een e-mail.</a>',
        'explanation_a' => 'Probeer eerst de nieuwste beschikbare plugin te installeren. Gebruik de link onder het zoekveld, het heeft een automatische browserdetectie.',
    ],
];
