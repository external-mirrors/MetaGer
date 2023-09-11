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
        'title' => 'MetaGer zur Suchmaschinenliste des Browsers hinzufügen <a title="Zur einfachen Hilfe" href="/hilfe/easy-language/functions#selist" ><img class="easy-help-icon lm-only" src="/img/help-questionmark-icon-lm.svg"/><img class="easy-help-icon dm-only" src="/img/help-questionmark-icon-dm.svg"/></a>',
        'explanation_b' => 'Manche Browser erwarten die Eingabe einer URL; diese lautet "https://metager.de/meta/meta.ger3?eingabe=%s" ohne Gänsefüßchen eintragen. Die URL können Sie selbst erzeugen, wenn Sie mit metager.de nach irgendetwas suchen und dann das, was oben im Adressfeld hinter "eingabe=" steht, mit %s ersetzen. Wenn Sie dann noch Probleme haben sollten, wenden Sie sich bitte an uns: <a href="/kontakt" target="_blank" rel="noopener">Kontaktformular</a>',
        'explanation_a' => 'Versuchen Sie bitte zuerst, das aktuelle Plugin zu installieren. Zum Installieren einfach auf den Link direkt unter dem Suchfeld klicken. Dort sollte Ihr Browser schon erkannt worden sein.',
    ],
    
    "suchfunktion" => [
        "title" => "Suchfunktionen"
    ],
    "stopworte" => [
        "title" => 'Stoppworte <a title="Zur einfachen Hilfe" href="/hilfe/easy-language/functions#stopwordsearch" ><img class="easy-help-icon lm-only" src="/img/help-questionmark-icon-lm.svg"/><img class="easy-help-icon dm-only" src="/img/help-questionmark-icon-dm.svg"/></a>',
        "3" => "auto neu -bmw",
        "2" => "Beispiel: Sie suchen ein neues Auto, aber auf keinen Fall einen BMW. Ihre Eingabe lautet also:",
        "1" => "Wenn Sie unter den MetaGer-Suchergebnissen solche ausschließen wollen, in denen bestimmte Worte (Ausschlussworte / Stoppworte) vorkommen, dann erreichen Sie das, indem Sie diese Worte mit einem Minus versehen.",
    ],
    "key"    => [
        "title" => 'MetaGer Schlüssel hinzufügen <a title="Zur einfachen Hilfe" href="/hilfe/easy-language/functions#keyexplain" ><img class="easy-help-icon lm-only" src="/img/help-questionmark-icon-lm.svg"/><img class="easy-help-icon dm-only" src="/img/help-questionmark-icon-dm.svg"/></a>',
        "1" => 'Der MetaGer Schlüssel wird automatisch im Browser eingerichtet und verwendet. Sie müssen also nichts weiter tun. Wenn Sie den MetaGer Schlüssel auf weiteren Geräten nutzen möchten, gibt es mehrere Möglichkeiten, den MetaGer-Schlüssel einzurichten:',
        "2"=>'Login Code <br>Auf der Verwaltungsseite des MetaGer Schlüssels können Sie den Login-Code verwenden, um Ihren Schlüssel zu einem weiteren Gerät hinzuzufügen. Dafür geben Sie den sechsstelligen Zahlencode ganz einfach beim Login ein. Der Login Code ist nur einmalig nutzbar und nur so lange gültig, wie das Fenster geöffnet ist.',
        "3"=>'URL kopieren <br>Wenn Sie auf der Verwaltungsseite des MetaGer Schlüssels sind, gibt es die Möglichkeit eine URL zu kopieren. Mit dieser URL lassen sich alle Einstellungen von MetaGer, sowie der MetaGer Schlüssel auf einem weiteren Gerät speichern.',
        '4'=>'Datei sichern <br>Wenn Sie auf der Verwaltungsseite des MetaGer Schlüssels sind, gibt es die Möglichkeit eine Datei zu sichern. Damit speichern Sie Ihren MetaGer-Schlüssel als Datei ab. Diese Datei können Sie dann auf einem anderen Gerät verwenden, um sich dort mit Ihrem Schlüssel einzuloggen.',
        '5'=>'QR Code scannen <br>Alternativ können Sie außerdem den QR Code, der auf der Verwaltungsseite angezeigt wird, scannen um sich bei einem weiteren Gerät einzuloggen',
        '6'=>'MetaGer Schlüssel manuell eingeben <br>Sie können natürlich auch den Schlüssel manuell auf einem weiteren Gerät eingeben.',
        'colors'=> [
            'title'=>'Farbiger MetaGer Schlüssel',
            '1'=>'Um auf einem Blick erkennen zu können, ob Sie werbefrei Suchen, haben wir unserem Schlüssel-Symbol Farben verpasst. Im Folgenden finden Sie die Erläuterungen für die entsprechenden Farben:',
            'grey'=>'Grau: Sie haben keinen Schlüssel eingerichtet. Sie nutzen die kostenlose Suche.',
            'red'=>'Rot: Wenn Ihr Schlüsselsymbol rot ist, ist dieser Schlüssel leer. Sie haben alle werbefreien Suchen aufgebraucht. Den Schlüssel können Sie auf der Verwaltungsseite des Schlüssels aufladen.',
            'green'=>'Grün: Wenn Ihr Schlüsselsymbol grün ist, dann verwenden Sie einen aufgeladenen Schlüssel.',
            'yellow'=>'Gelb: Sollten Sie einen gelben Schlüssel sehen, dann haben Sie noch ein Guthaben von 30 Token. Ihre Suchen sind bald aufgebraucht. Es wird empfohlen den Schlüssel bald aufzuladen.',
        ],
    ],
    "mehrwortsuche" => [
        "title" => 'Mehrwortsuche <a title="Zur einfachen Hilfe" href="/hilfe/easy-language/functions#severalwords" ><img class="easy-help-icon lm-only" src="/img/help-questionmark-icon-lm.svg"/><img class="easy-help-icon dm-only" src="/img/help-questionmark-icon-dm.svg"/></a>',
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
    "exactsearch" =>[
        "title" => 'Exakte Suche <a title="Zur einfachen Hilfe" href="/hilfe/easy-language/functions#exactsearch" ><img class="easy-help-icon lm-only" src="/img/help-questionmark-icon-lm.svg"/><img class="easy-help-icon dm-only" src="/img/help-questionmark-icon-dm.svg"/></a>',
        "1" =>"Wenn Sie in den MetaGer-Suchergebnissen ein bestimmtes Wort finden möchten, können Sie dieses Wort mit einem Plus versehen. Bei der Verwendung von einem Plus und Anführungszeichen wird eine Phrase exakt so wie Sie es eingegeben haben, gesucht.",
        "2" =>"Beispiel: S",
        "3" =>'Beispiel: ',
        "example" => [
            "1" => "+Beispielwort",
            "2" => '+"Beispiel Phrase"',
        ],
    ],
    "bang"  => [
        "title" => '!bangs <a title="Zur einfachen Hilfe" href="/hilfe/easy-language/functions#bangs" ><img class="easy-help-icon lm-only" src="/img/help-questionmark-icon-lm.svg"/><img class="easy-help-icon dm-only" src="/img/help-questionmark-icon-dm.svg"/></a>',
        "1" => "MetaGer unterstützt in geringem Umfang eine Schreibweise, die oft als „!bang“-Syntax bezeichnet wird.<br>Ein solches „!bang“ beginnt immer mit einem Ausrufezeichen und enthält keine Leerzeichen. Beispiele sind hier „!twitter“ oder „!facebook“.<br>Wird ein !bang, das wir unterstützen, in der Suchanfrage verwendet, erscheint in unseren Quicktips ein Eintrag, über den man die Suche auf Knopfdruck mit dem jeweiligen Dienst (hier Twitter oder Facebook) fortführen kann.",
        "2" => 'Warum werden !bangs nicht direkt geöffnet?',
        "3" => 'Die !bang-„Weiterleitungen“ sind bei uns ein Teil unserer Quicktips und benötigen einen zusätzlichen „Klick“. Das war für uns eine schwierige Entscheidung, da die !bang dadurch weniger nützlich sind. Jedoch ist es leider nötig, da die Links, auf die weitergeleitet wird, nicht von uns stammen, sondern von einem Drittanbieter, DuckDuckGo.<p>Wir achten stehts darauf, dass unsere Nutzer jederzeit die Kontrolle behalten. Wir schützen daher auf zwei Arten: Zum Einen wird der eingegebene Suchbegriff niemals an DuckDuckGo übertragen, sondern nur das !bang. Zum Anderen bestätigt der Nutzer den Besuch des !bang-Ziels explizit. Leider können wir derzeit aus Personalgründen nicht alle diese !bangs prüfen oder selbst pflegen.',
    ],
    "backarrow" => 'Zurück',
    "easy-help"=> 'Durch Klicken auf das Symbol <a title="Zur einfachen Hilfe" href="/hilfe/easy-language/functions#bangs" ><img class="easy-help-icon lm-only" src="/img/help-questionmark-icon-lm.svg"/><img class="easy-help-icon dm-only" src="/img/help-questionmark-icon-dm.svg"/></a> kommen Sie zu einer einfacheren Version der Hilfe.',
];