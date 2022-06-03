<?php

return [
    'title' => 'MetaGer - Hilfe',
    "backarrow" => ' Zurück',
    "suchfunktion.title" => "Suchfunktionen",
    "stopworte.title" => 'Stoppworte',
    "stopworte.1" => "Stopp-Worte sind Wörter die man nicht sehen will. <br> Wenn Sie ein Wort in den Ergebnissen nicht sehen wollen dann machen Sie das so:",
    "stopworte.2" => "Beispiel: <br> Sie wollen nach einem neuen Auto suchen. <br> Sie wollen nicht das Wort BMW sehen. <br> Also schreiben Sie:",
    "stopworte.3" => "auto neu -bmw",
    "stopworte.4" => "Sie schreiben ein Minus vor das Wort. <br>Dann wird das Wort in den Ergebnissen nicht angezeigt.",

    "mehrwortsuche.title" => "Mehrwortsuche",
    "mehrwortsuche.1" => "Die Mehrwortsuche hat 2 Arten.",
    "mehrwortsuche.2" => "Ein Wort soll in den Ergebnissen da sein. <br> Dann schreiben Sie das in Anführungs-Zeichen. <br> Das sieht so aus:",
    "mehrwortsuche.3" => "Beispiel: <br> Sie suchen nach <strong>der runde Tisch</strong>. <br> Sie wollen das Wort <strong>rund</strong> in den Ergebnissen finden. <br> Also schreiben Sie das Wort so:",
    "mehrwortsuche.3.example" => 'der "runde" tisch',
    "mehrwortsuche.4" => 'Es gibt noch eine weitere Art von der Mehrwortsuche. <br> Sie können auch ganze Sätze suchen. <br> Wenn Sie einen Satz in genau der Reihen-Folge sehen wollen macht man das so:',
    "mehrwortsuche.5" => "Beispiel: <br> Sie suchen nach <strong>der runde Tisch</strong>.<br> Sie wollen es in genau der Reihen-Folge sehen. <br> Das schreibt man dann so:",
    "mehrwortsuche.5.example" => '"der runde tisch"',


    'urls.title' => 'URLs ausschließen',
    'urls.explanation' => 'Sie können Suchergebnisse ausschließen, deren Ergebnislinks bestimmte Worte enthalten, indem Sie in ihrer Suche "-url:" verwenden.',
    'urls.example.1' => 'Beispiel: Sie möchten keine Ergebnisse, bei denen im Ergebnislink das Wort "Hund" auftaucht:',
    'urls.example.2' => '<i>meine suche</i> -url:hund',

    "bang.title" => "!bangs",
    "bang.1" => "MetaGer unterstützt in geringem Umfang eine Schreibweise, die oft als „!bang“-Syntax bezeichnet wird.<br>Ein solches „!bang“ beginnt immer mit einem Ausrufezeichen und enthält keine Leerzeichen. Beispiele sind hier „!twitter“ oder „!facebook“.<br>Wird ein !bang, das wir unterstützen, in der Suchanfrage verwendet, erscheint in unseren Quicktips ein Eintrag, über den man die Suche auf Knopfdruck mit dem jeweiligen Dienst (hier Twitter oder Facebook) fortführen kann.",
    'faq.18.h' => 'Warum werden !bangs nicht direkt geöffnet?',
    'faq.18.b' => 'Die !bang-„Weiterleitungen“ sind bei uns ein Teil unserer Quicktips und benötigen einen zusätzlichen „Klick“. Das war für uns eine schwierige Entscheidung, da die !bang dadurch weniger nützlich sind. Jedoch ist es leider nötig, da die Links, auf die weitergeleitet wird, nicht von uns stammen, sondern von einem Drittanbieter, DuckDuckGo.<p>Wir achten stehts darauf, dass unsere Nutzer jederzeit die Kontrolle behalten. Wir schützen daher auf zwei Arten: Zum Einen wird der eingegebene Suchbegriff niemals an DuckDuckGo übertragen, sondern nur das !bang. Zum Anderen bestätigt der Nutzer den Besuch des !bang-Ziels explizit. Leider können wir derzeit aus Personalgründen nicht alle diese !bangs prüfen oder selbst pflegen.',

    "searchinsearch.title" => "Suche in der Suche",
    "searchinsearch.1" => 'Auf die Funktion der Suche in der Suche kann mit Hilfe des "MEHR"-Knopfes rechts unten im Ergebniskasten zugegriffen werden. Beim Klick auf diesen öffnet sich ein Menü, in dem "Ergebnis speichern" an erster Stelle steht. Mit dieser Option wird das jeweilige Ergebnis in einem separaten Speicher abgelegt. Der Inhalt dieses Speichers wird rechts neben den Ergebnissen unter den Quicktips angezeigt (Auf zu kleinen Bildschirmen werden gespeicherte Ergebnisse aus Platzmangel nicht angezeigt). Dort können Sie die gespeicherten Ergebnisse nach Schlüsselworten filtern oder umsortieren lassen. Mehr Infos zum Thema "Suche in der Suche" gibt es im <a href="http://blog.suma-ev.de/node/225" target="_blank" rel="noopener"> SUMA-Blog</a>.',
    

    'selist.title' => 'MetaGer zur Suchmaschinenliste des Browsers hinzufügen',
    'selist.explanation.1' => 'Versuchen Sie bitte zuerst, das aktuelle Plugin zu installieren. Zum Installieren einfach auf den Link direkt unter dem Suchfeld klicken. Dort sollte Ihr Browser schon erkannt worden sein.',
    'selist.explanation.2' => 'Manche Browser erwarten die Eingabe einer URL; diese lautet "https://metager.de/meta/meta.ger3?eingabe=%s" ohne Gänsefüßchen eintragen. Die URL können Sie selbst erzeugen, wenn Sie mit metager.de nach irgendetwas suchen und dann das, was oben im Adressfeld hinter "eingabe=" steht, mit %s ersetzen. Wenn Sie dann noch Probleme haben sollten, wenden Sie sich bitte an uns: <a href="/kontakt" target="_blank" rel="noopener">Kontaktformular</a>',
    
];