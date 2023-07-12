<?php

return [
    'head' => [
        '1' => 'Transparenzerklärung',
        '2' => 'MetaGer ist transparent',
        '3' => 'Was ist eine Metasuchmaschine überhaupt?',
        '4' => 'Was ist der Vorteil einer Metasuchmaschine?',
        '5' => 'Wie setzt sich unser Ranking zusammen?',
        'compliance' => 'Wie reagiert MetaGer auf Anfragen von Behörden?',
    ],
    'text' => [
        '1' => 'MetaGer ist transparent. Unser <a href=":sourcecode">Quellcode</a> ist <a href=":license">frei lizensiert</a> und für alle öffentlich einsehbar. Wir verzichten auf die Speicherung von Nutzerdaten und legen Wert auf Datenschutz sowie Privatsphäre. Deshalb gewähren wir anonymen Zugang zu den Suchergebnissen. Dies ist durch einen anonymen Proxy und TOR-hidden-Zugang möglich. Hinzu kommt eine transparente Organisationsstruktur, da MetaGer von dem gemeinnützigen Verein <a href=":sumalink">SUMA-EV</a> getragen wird, in dem jeder Mitglied werden kann.',
        '2' => [
            '1' => 'Um zu erklären, was Metasuchmaschinen sind, ergibt es Sinn, vorerst kurz grob zu erklären wie die Indexierung von normalen Suchmaschinen funktioniert. Diese Suchmaschinen beziehen ihre Suchergebnisse aus einer Datenbank von Webseiten, die man auch Index nennt. Die Suchmaschinen betreiben sogenannte „Crawler“, die Webseiten sammeln und diese zum Index (Datenbestand) hinzufügen. Der Crawler startet mit einer Menge von Webseiten und öffnet alle dort verlinkten Webseiten. Diese werden indexiert, also dem Index hinzugefügt. Anschließend öffnet der Crawler die auf diesen Webseiten verlinkten Webseiten und fährt so fort.',
            '2' => 'Eine Metasuchmaschine vereint die Ergebnisse mehrerer Suchmaschinen unter sich und wertet diese erneut nach eigenem Schema aus. Das heißt, dass die Metasuchmaschine keinen eigenen Index hat. Deshalb verwenden Metasuchmaschinen auch keine Crawler. Sie nutzen den Index von anderen Suchmaschinen.',
        ],
        '3' => 'Ein klarer Vorteil von Metasuchmaschinen ist es, dass der Nutzer nur eine einzige Suchanfrage braucht, um an die Ergebnisse mehrerer Suchmaschinen zu kommen. Die Metasuchmaschine gibt die relevanten Ergebnisse in einer nochmals sortierten Ergebnisliste aus. MetaGer ist keine reine Metasuchmaschine, da wir auch kleine eigene Indexe verwenden.',
        '4' => 'Wir übernehmen das Ranking unserer Quell-Suchmaschinen und gewichten diese. Diese Bewertungen werden dann in Punktzahlen umgewandelt. Außerdem wird das Vorkommen der Suchbegriffe in der URL und im Snippet, sowie das übermäßige Vorkommen von besonderen Zeichen (andere Schriftzeichen wie Kyrillisch) berücksichtigt. Zudem verwenden wir noch eine Sperrliste, um einzelne Seiten aus der Ergebnisliste zu entfernen. Wir sperren Webseiten in der Anzeige, wenn wir rechtlich dazu verpflichtet sind. Außerdem behalten wir uns vor, Webseiten mit nachweislich fehlerhaften Informationen, Webseiten von extrem schlechter Qualität und andere besonders zweifelhafte Webseiten zu sperren.',
        '5' => 'Sollte es weitere Fragen oder Unklarheiten geben, können Sie gerne unser <a href=":contact">Kontaktformular</a> nutzen und uns Ihre Fragen stellen!',
        'compliance' => 'Wir erfüllen Anfragen von Behörden, wenn wir rechtlich dazu verpflichtet sind und zu dem Ergebnis kommen, dass eine Umsetzung nicht gegen freiheitliche Grundrechte verstößt. Diese Prüfung nehmen wir sehr ernst. Außerdem speichern wir so wenig personenbeziehbare Daten wie möglich, um das Risiko zu verringern, Daten herausgeben zu müssen. In der nachfolgenden Tabelle finden Sie Daten zu den von uns bearbeiteten behördlichen Anfragen der letzten 5 Jahre. Weitere Informationen folgen in Kürze.',
    ],


    'table' => [
        'compliance' => [
            'th' => [
                'authinfocomp' => 'Erfüllte Auskunftsanfragen',
                'authblockcomp' => 'Erfüllte Sperranfragen',
            ],
        ],
    ],

    'alt' => [
        'text' => [
            '1' => 'Visuelle Darstellung von zwei Indexen die sich zu einem Meta-Index ergänzen'
        ],
    ],

];