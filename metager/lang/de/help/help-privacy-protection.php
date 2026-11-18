<?php
return [
    'title' => 'MetaGer - Hilfe',
    'backarrow' => 'Zurück',
    'easy-help' => 'Durch Klicken auf das Symbol <a title="Zur einfachen Hilfe" href="/hilfe/easy-language/privacy-protection" ><img class="easy-help-icon lm-only" src="/img/help-questionmark-icon-lm.svg"/><img class="easy-help-icon dm-only" src="/img/help-questionmark-icon-dm.svg"/></a> kommen Sie zu einer einfacheren Version der Hilfe.',
    'privacy' => [
        'title' => "Anonymität und Datensicherheit",
        '1' => 'Tracking-Cookies, Session-IDs und IP-Adressen <a title="Zur einfachen Hilfe" href="/hilfe/easy-language/privacy-protection#eh-tracking" ><img class="easy-help-icon lm-only" src="/img/help-questionmark-icon-lm.svg"/><img class="easy-help-icon dm-only" src="/img/help-questionmark-icon-dm.svg"/></a>',
        '2' => 'Nichts von alldem wird hier bei MetaGer verwendet, gespeichert, aufgehoben oder sonst irgendwie verarbeitet (Ausnahme: Kurzfristige Speicherung gegen Hacking- und Bot-Attacken). Weil wir diese Thematik für extrem wichtig halten, haben wir auch Möglichkeiten geschaffen, die Ihnen helfen können, hier ein Höchstmaß an Sicherheit zu erreichen: den MetaGer-TOR-Hidden-Service und unseren anonymen Browser.',
        '3' => "Mehr Informationen finden Sie weiter unten. Die Funktionen sind unter \"Dienste\" in der Navigationsleiste erreichbar.",
    ],
    'tor' => [
        'title' => 'Tor-Hidden-Service <a title="Zur einfachen Hilfe" href="/hilfe/easy-language/privacy-protection#eh-torhidden" ><img class="easy-help-icon lm-only" src="/img/help-questionmark-icon-lm.svg"/><img class="easy-help-icon dm-only" src="/img/help-questionmark-icon-dm.svg"/></a>',
        '1' => "Bei MetaGer werden schon seit vielen Jahren die IP-Adressen ausgeblendet und nicht gespeichert. Nichtsdestotrotz sind diese Adressen auf dem MetaGer-Server zeitweise, während eine Suche läuft, sichtbar: wenn MetaGer also einmal kompromittiert sein sollte, dann könnte ein Angreifer Ihre Adressen mitlesen und speichern. Um dem höchsten Sicherheitsbedürfnis entgegenzukommen, unterhalten wir eine MetaGer-Instanz im Tor-Netzwerk, die über: <a href=\"/tor/\" target=\"_blank\" rel=\"noopener\">https://metager.de/tor/</a> erreichbar ist. Für die Benutzung benötigen Sie einen speziellen Browser, den Sie auf <a href=\"https://www.torproject.org/\" target=\"_blank\" rel=\"noopener\">https://www.torproject.org/</a> herunter laden können.",
        '2' => "Du kannst MetaGer im Tor-Browser aufrufen unter: http://metagerv65pwclop2rsfzg4jwowpavpwd6grhhlvdgsswvo6ii4akgyd.onion.",
    ],
    'proxy' => [
        'title' => 'Anonym öffnen (SafeBrowse) <a title="Zur einfachen Hilfe" href="/hilfe/easy-language/privacy-protection#eh-proxy" ><img class="easy-help-icon lm-only" src="/img/help-questionmark-icon-lm.svg"/><img class="easy-help-icon dm-only" src="/img/help-questionmark-icon-dm.svg"/></a>',
        'intro' => 'Mit „Anonym öffnen“ besuchen Sie eine Webseite, ohne dass sie Ihr Gerät zu sehen bekommt. Die Seite wird in einem Browser geöffnet, der auf unseren Servern läuft: SafeBrowse. Die Webseite hat es nur mit diesem Browser zu tun und erfährt weder Ihre IP-Adresse noch etwas über Ihr Gerät, Ihren Browser oder Ihre Cookies.',
        'use' => [
            'title' => 'So öffnen Sie eine Seite anonym',
            'text' => [
                'Klicken Sie bei einem Ergebnis auf „Anonym öffnen“ (das Schild-Symbol). Die Seite öffnet sich in einem neuen Tab.',
                'Wenn Sie jedes Ergebnis anonym öffnen möchten, schalten Sie in den Einstellungen „Ergebnisse anonym öffnen“ ein. Dann führt jeder Link eines Ergebnisses über SafeBrowse, und mit „Direkt öffnen“ besuchen Sie eine Seite ohne SafeBrowse.',
            ],
        ],
        'session' => [
            'title' => 'Die Sitzung',
            'text' => [
                'Das erste anonym geöffnete Ergebnis startet eine Sitzung. Jedes weitere öffnet sich in derselben Sitzung in einem eigenen Tab. Darin surfen Sie ganz normal weiter: Links folgen, Formulare ausfüllen, eine neue Adresse oder einen Suchbegriff ins Adressfeld eingeben. Sie bleiben dabei geschützt.',
                'Eine Sitzung kostet Token von Ihrem MetaGer-Schlüssel; wie viele für welche Zeit, steht vor dem Start. Solange Sie die Sitzung benutzen, wird sie automatisch verlängert. Benutzen Sie sie nicht mehr, endet sie kurz darauf von selbst, oder Sie beenden sie mit „Sitzung beenden“.',
                'Wenn die Sitzung endet, wird der Browser auf unseren Servern mit allem gelöscht, was darin war: besuchte Seiten, Cookies, Anmeldungen und heruntergeladene Dateien. Die nächste Sitzung beginnt mit einem neuen, leeren Browser. Heruntergeladene Dateien speichern Sie deshalb vorher über „Downloads“ auf Ihrem Gerät.',
            ],
        ],
        'views' => [
            'title' => 'Zwei Ansichten',
            'text' => [
                '„Seite“ zeigt eine Kopie der Webseite ohne deren Skripte. Sie lesen, scrollen, suchen und kopieren darin wie auf jeder Seite; Ihre Klicks und Eingaben gehen an den Browser auf unseren Servern. „Ganzer Browser“ zeigt diesen Browser als Bild, mit allen seinen Tabs. Dort funktioniert alles, auch Videos und Seiten, die „Seite“ nicht darstellen kann. Zwischen beiden wechseln Sie in der Leiste oben.',
            ],
        ],
        'nojs' => [
            'title' => 'Ohne JavaScript',
            'text' => [
                'Auch wenn JavaScript in Ihrem Browser ausgeschaltet ist, öffnet „Anonym öffnen“ die Seite in SafeBrowse. Sie sehen dann eine Kopie der Seite, in der Links, Knöpfe und Formulare funktionieren. Sie wird nach jedem Klick neu geladen, verändert sich aber nicht von selbst. Videos, Dialoge der Seite und das Hochladen von Dateien gehen so nicht. Blockiert Ihr Browser Cookies, landen die Ergebnisse, die Sie von einer Ergebnisseite aus öffnen, trotzdem in derselben Sitzung, auch wenn Sie von dort aus weitersuchen.',
            ],
        ],
        'limits' => [
            'title' => 'Was „Anonym öffnen“ nicht leistet',
            'text' => [
                'Die Webseite erkennt Sie nicht an Ihrem Gerät, wohl aber an dem, was Sie ihr selbst mitteilen: Wenn Sie sich anmelden oder persönliche Daten eingeben, weiß sie, wer Sie sind. Wer sich auch gegenüber MetaGer nicht zeigen möchte, nutzt MetaGer über Tor (siehe oben).',
            ],
        ],
    ],
    'maps' => [
        'title' => "MetaGer Maps",
        '1' => 'Der Schutz der Privatsphäre im Zeitalter globaler Datengiganten hat uns auch dazu veranlasst, <a href="https://maps.metager.de" target="_blank">https://maps.metager.de</a> zu entwickeln: der (unseres Wissens) einzige Routenplaner, der die volle Funktionalität über einen Browser und eine App bietet, ohne den Standort des Nutzers zu speichern. Das alles ist überprüfbar, denn unsere Software ist Open Source. Für die Nutzung von maps.metager.de empfehlen wir unsere schnelle App-Version. Sie können unsere Apps unter <a href="/app" target="_blank">hier</a> (oder natürlich auch im Play Store) herunterladen.',
        '2' => "Diese Kartenfunktion kann auch über die MetaGer-Suche aufgerufen werden (und umgekehrt). Wenn Sie in MetaGer nach einem Begriff gesucht haben, sehen Sie in der oberen rechten Ecke einen neuen Suchfokus \"Karten\". Wenn Sie diesen anklicken, gelangen Sie zu einer entsprechenden Karte.",
        '3' => "Die Karte zeigt beim Laden die von MetaGer gefundenen Punkte (POIs = Points of Interest) an, die auch in der rechten Spalte aufgelistet sind. Beim Zoomen passt sich diese Liste dem Kartenausschnitt an. Wenn Sie mit der Maus über einen Marker auf der Karte oder in der Liste fahren, wird der entsprechende Punkt hervorgehoben. Klicken Sie auf \"Details\", um weitere Informationen zu diesem Punkt aus der untenstehenden Datenbank zu erhalten.",
    ],
    'content' => [
        'title' => 'Fragwürdige Inhalte / Jugendschutz <a title="Zur einfachen Hilfe" href="/hilfe/easy-language/privacy-protection#eh-content" ><img class="easy-help-icon lm-only" src="/img/help-questionmark-icon-lm.svg"/><img class="easy-help-icon dm-only" src="/img/help-questionmark-icon-dm.svg"/></a>',
        'explanation' => [
            '1' => 'Ich habe "Treffer" erhalten, die ich nicht nur als lästig empfinde, sondern die meiner Meinung nach auch illegale Inhalte enthalten!',
            '2' => 'Wenn Sie im Internet etwas finden, das Sie für illegal oder jugendgefährdend halten, können Sie sich per E-Mail an <a href="mailto:hotline@jugendschutz.net" target="_blank" rel="noopener">hotline@jugendschutz.net</a> wenden oder <a href="http://www.jugendschutz.net/" target="_blank" rel="noopener">www.jugendschutz.net</a> besuchen und das dort verfügbare Beschwerdeformular ausfüllen. Es ist hilfreich, eine kurze Notiz darüber zu machen, was Sie für unzulässig halten und wie Sie auf diesen Inhalt gestoßen sind. Sie können fragwürdige Inhalte auch direkt an uns melden. Schicken Sie dazu eine E-Mail an unseren Jugendschutzbeauftragten (<a href="mailto:jugendschutz@metager.de" target="_blank" rel="noopener">jugendschutz@metager.de</a>).',
        ],
    ],
];
