<?php

return [
    'title' => 'MetaGer - Hilfe',
    "backarrow" => 'Zurück',
    "suchfunktion"  => [
        "title" => "Such-Funktionen",
    ],
    "stopworte"  => [
        "title" => 'Stopp-Worte',
        "1" => "Stopp-Worte sind Wörter die man nicht sehen will. <br> Wenn Sie ein Wort nicht sehen wollen dann machen Sie das so:",
        "2" => "Beispiel: <br> Sie wollen nach einem neuen Auto suchen. <br> Sie wollen das Wort <strong>BMW</strong> nicht sehen. <br> Also schreiben Sie:",
        "3" => "auto neu -bmw",
        "4" => "Sie schreiben ein Minus vor das Wort. <br>Dann zeigt man das Wort in den Ergebnissen nicht mehr an.",
    ],
    "mehrwortsuche"  => [
        "title" => "Mehr-Wort-Suche",
        "1" => "Die Mehr-Wort-Suche hat 2 Arten.",
        "2" => "Ein Wort soll in den Ergebnissen da sein. <br> Dann schreiben Sie das in Anführungs-Striche. <br> Das sieht so aus:",
        "3"  => [
            "0" => "Beispiel: <br> Sie suchen nach <strong>der runde Tisch</strong>. <br> Sie wollen das Wort <strong>rund</strong> in den Ergebnissen finden. <br> Also schreiben Sie das Wort so:",
            "example" => 'der "runde" tisch',
        ],
        "4" => 'Es gibt noch eine weitere Art von der Mehr-Wort-Suche. <br> Sie können auch ganze Sätze suchen. <br> Sie wollen einen Satz in genau der Reihen-Folge sehen. <br> Dann macht man das so:',
        "5"  => [
            "0" => "Beispiel: <br> Sie suchen nach <strong>der runde Tisch</strong>.<br> Sie wollen es in genau der Reihen-Folge sehen. <br> Das schreibt man dann so:",
            "example" => '"der runde tisch"',
        ],
    ],
    "bang"  => [
        "title" => "!bangs",
        "1" => "MetaGer unterstützt eine Schreib-Weise die !bang heißt. <br> Wenn man das benutzen will sieht das so aus: <br> <strong>!twitter</strong> oder <strong>!facebook</strong><br> Beispiel:<br> Sie möchten auf Twitter nach Katzen suchen. <br> Also geben Sie das so ein:",
        "example" => "!twitter katze",
        "2" => "Damit zeigt man bei dem Suchen rechts ein Feld an. <br> So sieht das Feld aus:",
        "3" => "Man kann auf den blauen Knopf drücken. <br> Dann öffnet sich die Web-Seite von Twitter mit der Suche nach Katzen. <br> Diese Funktion funktioniert bei kleinen Bild-Schirmen wie Handys nicht.",
    ],
    "searchinsearch"  => [
        "title" => "Suche in der Suche",
        "1" => 'Wenn Sie mehrere Ergebnisse speichern wollen geht das so: <br> Sie klicken bei dem Ergebnis auf <strong>MEHR</strong>.<br> Das sieht so aus:',
        "2" => 'Dann öffnet sich ein weiteres Menü. <br> Da klicken Sie auf <strong>ERGEBNIS SPEICHERN</strong>. <br> Das sieht so aus:', 
        "3" => 'Das können Sie bei allen Ergebnissen machen die Sie speichern wollen.<br> Ergebnisse löscht man aus der Liste, wenn man auf die Müll-Tonne drückt. <br> Die gespeicherten Ergebnisse sieht man dann hier:',   
        "4" => 'Diese Funktion funktioniert bei kleinen Bild-Schirmen wie Handys nicht.',   
    ],
    "selist"  => [
        "title"  => [
            '0' => 'MetaGer zur Such-Maschinen-Liste des Browsers hinzufügen',
            '1' => 'MetaGer installieren',
        ],
        "explanation"  => [
            '1' => 'Auf der Start-Seite gibt es ein Feld <strong>MetaGer installieren</strong>.<br> Das Feld ist unter dem Such-Feld. <br> So sieht das Feld <strong>MetaGer installieren</strong> aus:<br>',
            '2' => 'Manchmal muss man auch eine URL eingeben. <br> Diese sieht so aus: <br>https://metager.de/meta/meta.ger3?eingabe=%s <br> Wenn Sie Probleme haben, wenden Sie sich an uns mit dem <a href="/kontakt" target="_blank" rel="noopener">Kontakt-Formular</a>.',
        ],
    ],
];