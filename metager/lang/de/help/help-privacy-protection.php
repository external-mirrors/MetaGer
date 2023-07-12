<?php

return [
    "title" => 'MetaGer - Hilfe',
    "backarrow" => 'Zurück',
    'datenschutz' => [
        "title" => "Anonymität und Datensicherheit",
        "1" => 'Tracking-Cookies, Session-IDs und IP-Adressen',
        "2" => 'Nichts von alldem wird hier bei MetaGer verwendet, gespeichert, aufgehoben oder sonst irgendwie verarbeitet (Ausnahme: Kurzfristige Speicherung gegen Hacking- und Bot-Attacken). Weil wir diese Thematik für extrem wichtig halten, haben wir auch Möglichkeiten geschaffen, die Ihnen helfen können, hier ein Höchstmaß an Sicherheit zu erreichen: den MetaGer-TOR-Hidden-Service und unseren anonymisierenden Proxyserver.',
        "3" => "Mehr Informationen finden Sie weiter unten. Die Funktionen sind unter \"Dienste\" in der Navigationsleiste erreichbar.",
    ],

    'tor' => [
        "title" => "Tor-Hidden-Service",
        "1" => "Bei MetaGer werden schon seit vielen Jahren die IP-Adressen ausgeblendet und nicht gespeichert. Nichtsdestotrotz sind diese Adressen auf dem MetaGer-Server zeitweise, während eine Suche läuft, sichtbar: wenn MetaGer also einmal kompromittiert sein sollte, dann könnte dieser Angreifer Ihre Adressen mitlesen und speichern. Um dem höchsten Sicherheitsbedürfnis entgegenzukommen, unterhalten wir eine MetaGer-Instanz im Tor-Netzwerk: den MetaGer-TOR-hidden-Service - erreichbar über: <a href=\"/tor/\" target=\"_blank\" rel=\"noopener\">https://metager.de/tor/</a>. Für die Benutzung benötigen Sie einen speziellen Browser, den Sie auf <a href=\"https://www.torproject.org/\" target=\"_blank\" rel=\"noopener\">https://www.torproject.org/</a> herunter laden können.",
        "2" => "MetaGer erreichen Sie im Tor-Browser dann unter: http://metagerv65pwclop2rsfzg4jwowpavpwd6grhhlvdgsswvo6ii4akgyd.onion .",
    ],

    'proxy' => [
        "title" => "Anonymisierender MetaGer-Proxyserver",
        "1" => "Um ihn zu verwenden, müssen Sie auf der MetaGer-Ergebnisseite nur auf \"ANONYM ÖFFNEN\" am unteren Rand des Ergebnisses klicken. Dann wird Ihre Anfrage an die Zielwebseite über unseren anonymisierenden Proxy-Server geleitet und Ihre persönlichen Daten bleiben weiterhin völlig geschützt. Wichtig: wenn Sie ab dieser Stelle den Links auf den Seiten folgen, bleiben Sie durch den Proxy geschützt. Sie können aber oben im Adressfeld keine neue Adresse ansteuern. In diesem Fall verlieren Sie den Schutz. Ob Sie noch geschützt sind, sehen Sie ebenfalls im Adressfeld. Es zeigt: https://proxy.suma-ev.de/?url=hier steht die eigentlich Adresse.",
    ],

    'maps' => [
        "title" => "MetaGer Maps",
        "1" => 'Die Sicherung der Privatsphäre im Zeitalter der globalen Datenkraken hat uns auch bewogen, <a href="https://maps.metager.de" target="_blank">https://maps.metager.de</a> zu entwickeln: Der (unseres Wissens) einzige Routenplaner, der die vollen Funktionalitäten via Browser und App bietet - ohne dass Nutzer-Standorte gespeichert werden.  All dies ist nachprüfbar, denn unsere Softwaren sind Open-Source. Für die Nutzung von maps.metager.de empfehlen wir unsere schnelle App-Version. Unsere Apps können Sie unter <a href="/app" target="_blank">App beziehen</a> downloaden (oder natürlich auch über den Play-Store).',
        "2" => "Diese Kartenfunktion kann auch von der MetaGer-Suche aufgerufen werden (und umgekehrt). Sobald Sie bei MetaGer nach einem Begriff gesucht haben, sehen Sie oben rechts einen neuen Suchfokus \"Maps\".  Beim Klick darauf gelangen Sie zu einer dazugehörigen Karte. .",
        "3" => "Die Karte zeigt nach dem Aufrufen die auch in der Spalte rechts wiedergegebenen von MetaGer gefundenen Punkte (POIs = Points of Interest). Beim Zoomen passt sich diese Liste an den Kartenausschnitt an.  Wenn Sie die Maus über eine Markierung in der Karte oder in der Liste halten, wird das jeweilige Gegenstück hervorgehoben.  Klicken Sie auf \"Details\", um genauere Informationen zu diesem Punkt aus der darunter liegenden Datenbank zu erhalten.",
    ],

    'content' => [
        'title' => 'Fragwürdige Inhalte / Jugendschutz',
        'explanation' => [
            '1' => 'Ich habe "Treffer" erhalten, die finde ich nicht nur ärgerlich, sondern die enthalten meiner Meinung nach illegale Inhalte!',
            '2' => 'Wenn Sie im Internet etwas finden, das Sie für illegal oder jugendgefährdend halten, dann können Sie sich per Mail an <a href="mailto:hotline@jugendschutz.net" target="_blank" rel="noopener">hotline@jugendschutz.net</a> wenden oder Sie gehen auf <a href="http://www.jugendschutz.net/" target="_blank" rel="noopener">www.jugendschutz.net</a> und füllen das dort zu findende Beschwerdeformular aus. Sinnvoll ist ein kurzer Hinweis, was Sie konkret für unzulässig halten und wie Sie auf dieses Angebot gestoßen sind. Direkt an uns können Sie fragwürdige Inhalte auch melden. Schreiben Sie dazu eine Mail an unseren Jugendschutzbeauftragten (<a href="mailto:jugendschutz@metager.de" target="_blank" rel="noopener">jugendschutz@metager.de</a>).',
        ],
    ],
];