<?php

/**
 * Anonyme Token — /hilfe/anonyme-token.
 *
 * Aus dem "anonymous-token"-Zweig von pass/lang/<locale>/help.json.
 * Der Pfad /keys/help/anonymous-token wird dauerhaft hierher weitergeleitet:
 * er steht in bereits versandten Mitglieds-Willkommensmails.
 */

return [
    "heading" => "Anonyme Token",
    "description" => [
        "heading" => "Was sind anonyme Token?",
        "text" => "Wenn Sie einen MetaGer-Schlüssel verwenden, erhalten Sie ein zufällig generiertes Passwort, das Ihr Browser bei jeder Suchanfrage an uns sendet, damit wir eine werbefreie Suche ermöglichen können. Wenn Sie unsere <a href=\"/app\" target=\"_blank\">Android-App</a> oder unsere Web-Erweiterung für <a href=\"https://chrome.google.com/webstore/detail/gjfllojpkdnjaiaokblkmjlebiagbphd\" target=\"_blank\" rel=\"noopener\">Chrome</a> und <a href=\"https://addons.mozilla.org/firefox/addon/metager-suche/\" target=\"_blank\" rel=\"noopener\">Firefox</a> verwenden, sendet Ihr Browser statt des Passworts bei jeder Suchanfrage zur Authentifizierung ein zufällig generiertes Passwort (anonymes Token) an uns, das lokal generiert wird. Damit ist sichergestellt, dass jedes Passwort einzigartig ist und weder mit dem eigentlichen MetaGer-Schlüssel noch zwischen den einzelnen Passwörtern eine Verbindung besteht.",
    ],
    "problem" => [
        "heading" => "Welches Problem sollen anonyme Token lösen?",
        "text" => "Wenn Ihr Browser uns mit jeder Suchanfrage das immer gleiche Passwort zusendet, hätten wir zumindest theoretisch die Möglichkeit, eine Korrelation zwischen allen mit dem gleichen Schlüssel durchgeführten Suchen herzustellen. Auch wenn wir das natürlich nicht tun, wäre so dennoch Vertrauen notwendig, um sich seiner anonymen Suche sicher zu sein. Damit wir die anonyme Suche nicht nur versprechen müssen, sondern auch beweisen können, haben wir die anonymen Token eingeführt.",
    ],
    "general-function" => [
        "heading" => "Wie funktioniert das?",
        "texts" => [
            "Wir möchten also Einmalpasswörter direkt von Ihrem Endgerät generieren lassen, die Sie uns dann bei Ihren Suchen zur Authentifizierung zusenden. Allerdings müssen wir für jedes anonyme Token auf Ihrem Endgerät sicherstellen, dass dafür ein reguläres Token von Ihrem MetaGer Schlüssel abgezogen wurde, ohne (und das ist der Knackpunkt), dass wir erfahren, welcher MetaGer Schlüssel zur Generierung des anonymen Token verwendet wurde.",
            "Traditionell würde man dazu eine Form der kryptographischen Signatur verwenden. In diesem Fall würden wir den generierten anonymen Token signieren. Wenn Sie uns dann zu einem späteren Zeitpunkt den anonymen Token zusammen mit der Signatur zusenden, können wir sicher sein, dass der anonyme Token gültig ist. Um jedoch die Signatur zu erhalten, hätten Sie uns den anonymen Token zusammen mit Ihrem echten Schlüssel geschickt, was die Anonymität zunichte machen würde.",
            "Daher verwenden wir stattdessen eine modifizierte Form der kryptographischen Signatur, die sogenannte <a href=\"https://en.wikipedia.org/wiki/Blind_signature\" target=\"_blank\">blinde Signatur</a>. Um eine Analogie zum wirklichen Leben zu schaffen, ist es so, als würden Sie uns Ihren anonymen Token in einem Briefumschlag aus Kohlepapier schicken. In diesem Beispiel könnten wir den Briefumschlag nicht öffnen, aber wir könnten von außen unterschreiben, so dass unsere Unterschrift auf den anonymen Token im Inneren übertragen wird. Wenn Sie den Umschlag zurückbekommen, könnten Sie ihn entfernen und uns später das Passwort und die Unterschrift zurückschicken. Wir könnten dann bestätigen, dass es tatsächlich unsere Unterschrift ist.",
            "Tatsächlich hinkt diese Analogie ein wenig, denn im tatsächlichen Verfahren haben wir in dem Moment in dem Sie uns den anonymen Token und die Unterschrift schicken, nicht nur den anonymen Token noch nie zuvor gesehen, sondern auch die Unterschrift selbst noch nie. Und trotzdem können wir verifizieren, dass die Signatur von uns erzeugt wurde.",
        ],
    ],
    "meaning" => [
        "heading" => "Was bedeutet das für Ihre authentifizierten Suchanfragen?",
        "texts" => [
            "Durch die Verwendung des beschriebenen Algorithmus können wir und Sie gleichermaßen sicherstellen, dass für Ihre authentifizierten Suchanfragen von Ihnen jedes Mal ein neues zufälliges Passwort verwendet wird, das in keinem Zusammenhang mit Ihrem MetaGer Schlüssel steht.",
            "Das Besondere an diesem Algorithmus ist dabei, dass alle Komponenten, die die Anonymität gewährleisten, lokal auf Ihrem Gerät ausgeführt werden. Dieser ausgeführte Quellcode kann jederzeit von jedem eingesehen und verifiziert werden.",
            "Und das Beste: Um anonyme Token zu verwenden, müssen Sie nichts weiter konfigurieren. Die einfache Installation/Nutzung unserer Browser-Erweiterung/Android-App reicht vollkommen aus, damit Ihr Endgerät bei allen Suchanfragen anonyme Token verwendet.",
        ],
    ],
    "technical-function" => [
        "texts" => [
            "Bei einer klassischen RSA Signatur würden wir den anonymen Token <code>m</code>, den geheimen Exponenten <code>d</code> und den öffentlichen Modulus <code>N</code> unseres privaten Schlüssels nehmen und die Signatur mittels <code>m^d (mod N)</code> erstellen. Wir wollen aber, dass <code>m</code> geheim bleibt.",
            "Deshalb erstellt Ihr Endgerät eine zufällige Zahl <code>r</code> mit Hilfe eines Zufallszahlengenerators, die teilerfremd zu <code>N</code> ist. Der größte gemeinsame Teiler von <code>r</code> und <code>N</code> muss also <code>1</code> sein.",
            "Weil <code>r</code> eine Zufallszahl ist, folgt daraus, dass <code>m'</code> keinerlei Informationen über den lokal gespeicherten anonymen Token <code>m</code> preisgibt.",
            "Unser Server erhält nun von Ihrem Endgerät den verschleierten anonymen Token <code>m'</code> zusammen mit dem zu verwendenden MetaGer Schlüssel. Wir ziehen einen Token von dem Schlüssel ab und senden die ebenfalls verschleierte Signatur <code>s'&Congruent; (m')^d (mod N)</code> an Ihr Endgerät zurück.",
            "Ihr Endgerät kann nun die tatsächlich gültige RSA Signatur <code>s</code> für den unverschleierten anonymen Token berechnen: <code>s&Congruent; s' r^-1 (mod N)</code>. Das funktioniert, weil für RSA Schlüssel gilt: <code>r^(e*d)&Congruent; r (mod N)</code>. Und deshalb auch: <code>s &Congruent; s' * r^-1 &Congruent; (m')^d*r^-1 &Congruent; m^d*r^(e*d)*r^-1 &Congruent; m^d*r*r^-1 &Congruent; m^d (mod N)</code>",
            "Ihr Endgerät sendet uns nun bei einer Suche den unverschleierten anonymen Token zusammen mit der zugehörigen Signatur zur Authorisierung zu. Der Schlüssel selbst wird bei der Suche nicht mehr an uns gesendet.",
        ],
        "heading" => "Der Algorithmus dahinter:",
    ],
];
