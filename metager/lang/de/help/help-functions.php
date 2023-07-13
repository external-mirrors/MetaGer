<?php

return [
    "urls" => [
        'title' => 'URLs ausschließen',
        'explanation' => 'Sie können Suchergebnisse ausschließen, deren Ergebnislinks bestimmte Worte enthalten, indem Sie in ihrer Suche "-url:" verwenden.',
        'example_b' => '<i>meine suche</i> -url:hund',
        'example_a' => 'Beispiel: Sie möchten keine Ergebnisse, bei denen im Ergebnislink das Wort "Hund" auftaucht:',
    ],
    'title' => 'MetaGer - Hilfe',
    "selist" => [
        'title' => 'MetaGer zur Suchmaschinenliste des Browsers hinzufügen',
        'explanation_b' => 'Manche Browser erwarten die Eingabe einer URL; diese lautet "https://metager.de/meta/meta.ger3?eingabe=%s" ohne Gänsefüßchen eintragen. Die URL können Sie selbst erzeugen, wenn Sie mit metager.de nach irgendetwas suchen und dann das, was oben im Adressfeld hinter "eingabe=" steht, mit %s ersetzen. Wenn Sie dann noch Probleme haben sollten, wenden Sie sich bitte an uns: <a href="/kontakt" target="_blank" rel="noopener">Kontaktformular</a>',
        'explanation_a' => 'Versuchen Sie bitte zuerst, das aktuelle Plugin zu installieren. Zum Installieren einfach auf den Link direkt unter dem Suchfeld klicken. Dort sollte Ihr Browser schon erkannt worden sein.',
    ],
    "faq" => [
        "18" => [
            'h' => 'Warum werden !bangs nicht direkt geöffnet?',
            'b' => 'Die !bang-„Weiterleitungen“ sind bei uns ein Teil unserer Quicktips und benötigen einen zusätzlichen „Klick“. Das war für uns eine schwierige Entscheidung, da die !bang dadurch weniger nützlich sind. Jedoch ist es leider nötig, da die Links, auf die weitergeleitet wird, nicht von uns stammen, sondern von einem Drittanbieter, DuckDuckGo.<p>Wir achten stehts darauf, dass unsere Nutzer jederzeit die Kontrolle behalten. Wir schützen daher auf zwei Arten: Zum Einen wird der eingegebene Suchbegriff niemals an DuckDuckGo übertragen, sondern nur das !bang. Zum Anderen bestätigt der Nutzer den Besuch des !bang-Ziels explizit. Leider können wir derzeit aus Personalgründen nicht alle diese !bangs prüfen oder selbst pflegen.',
        ]
    ],
    "suchfunktion" => [
        "title" => "Suchfunktionen"
    ],
    "stopworte" => [
        "title" => 'Stoppworte',
        "3" => "auto neu -bmw",
        "2" => "Beispiel: Sie suchen ein neues Auto, aber auf keinen Fall einen BMW. Ihre Eingabe lautet also:",
        "1" => "Wenn Sie unter den MetaGer-Suchergebnissen solche ausschließen wollen, in denen bestimmte Worte (Ausschlussworte / Stoppworte) vorkommen, dann erreichen Sie das, indem Sie diese Worte mit einem Minus versehen.",
    ],
    "searchinsearch" => [
        "title" => "Suche in der Suche",
        "1" => 'Auf die Funktion der Suche in der Suche kann mit Hilfe des "MEHR"-Knopfes rechts unten im Ergebniskasten zugegriffen werden. Beim Klick auf diesen öffnet sich ein Menü, in dem "Ergebnis speichern" an erster Stelle steht. Mit dieser Option wird das jeweilige Ergebnis in einem separaten Speicher abgelegt. Der Inhalt dieses Speichers wird rechts neben den Ergebnissen unter den Quicktips angezeigt (Auf zu kleinen Bildschirmen werden gespeicherte Ergebnisse aus Platzmangel nicht angezeigt). Dort können Sie die gespeicherten Ergebnisse nach Schlüsselworten filtern oder umsortieren lassen. Mehr Infos zum Thema "Suche in der Suche" gibt es im <a href="http://blog.suma-ev.de/node/225" target="_blank" rel="noopener"> SUMA-Blog</a>.',
    ],
    "mehrwortsuche" => [
        "title" => "Mehrwortsuche",
        "4" => [
            "example" => '"der runde tisch"',
            "text" => "Mit einer Phrasensuche können Sie statt nach einzelnen Wörtern auch nach Wortkombinationen suchen. Setzen Sie dazu einfach diejenigen Wörter, die gemeinsam vorkommen sollen, in Anführungszeichen.",
        ],
        "3" => [
            "example" => '"der" "runde" "tisch"',
            "text" => "Wenn Sie sicher gehen wollen, dass Wörter aus Ihrer Suche auch in den Ergebnissen vorkommen, dann müssen Sie diese in Anführungszeichen setzen.",
        ],
        "2" => "Sollte Ihnen das nicht ausreichen, haben Sie 2 Möglichkeiten, Ihre Suche genauer zu machen:",
        "1" => "Wenn Sie bei MetaGer nach mehr als einem Wort suchen, versuchen wir automatisch, Ihnen Ergebnisse zu liefern, in denen alle Wörter vorkommen, oder die diesen möglichst nahe kommen.",
    ],
    "bang" => [
        "title" => "!bangs",
        "1" => "MetaGer unterstützt in geringem Umfang eine Schreibweise, die oft als „!bang“-Syntax bezeichnet wird.<br>Ein solches „!bang“ beginnt immer mit einem Ausrufezeichen und enthält keine Leerzeichen. Beispiele sind hier „!twitter“ oder „!facebook“.<br>Wird ein !bang, das wir unterstützen, in der Suchanfrage verwendet, erscheint in unseren Quicktips ein Eintrag, über den man die Suche auf Knopfdruck mit dem jeweiligen Dienst (hier Twitter oder Facebook) fortführen kann.",
    ],
    "backarrow" => 'Zurück',
];