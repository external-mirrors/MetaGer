<?php

/**
 * Fragen zum MetaGer-Schlüssel — /hilfe/schluessel.
 *
 * Aus dem "faq"-Zweig von pass/lang/<locale>/help.json des Keymanagers.
 */

return [
    "heading" => "Fragen zum MetaGer-Schlüssel",
    "faqs" => [
        [
            "summary" => "Wie funktioniert der MetaGer Schlüssel?",
            "description" => "Mit einem MetaGer-Schlüssel suchen Sie werbefrei. Sie erhalten Token, von dem pro Suche eine Suche abgezogen wird. Wenn Sie einen MetaGer-Schlüssel verwenden, werden alle Funktionen, die MetaGer vor automatisierten Aufrufen schützen, deaktiviert. Das heißt, dass Sie keine Captcha-Anfragen sehen werden und Ihre IP-Adresse auch nicht für begrenzte Zeit vorgehalten wird. Vereinfacht gesagt wird MetaGer dadurch schneller, zuverlässiger und sicherer.",
        ],
        [
            "summary" => "Wie funktioniert das anonyme Token?",
            "description" => "Sie können den anonymen Token mit unserer Browsererweiterung oder App verwenden. Dadurch können Sie mit MetaGer noch sicherer suchen. Bei der Verwendung von anonymen Token wird ein Teil Ihres Guthabens in Form von Zufallspasswörtern auf Ihrem Gerät gespeichert. Durch ein <a href=\":tokenlink\">komplexes kryptographisches Verfahren</a> wird es selbst für uns unmöglich, Ihre durchgeführten Suchen miteinander oder mit Ihrem Schlüssel in Verbindung zu bringen.",
        ],
        [
            "summary" => "Wie verwende ich den MetaGer Schlüssel ?",
            "description" => "Der MetaGer Schlüssel wird automatisch im Browser eingerichtet und verwendet. Sie müssen also nichts weiter tun. Wenn Sie den MetaGer Schlüssel auf weiteren Geräten nutzen möchten, gibt es mehrere Möglichkeiten, den MetaGer-Schlüssel einzurichten:",
            "steps" => [
                [
                    "heading" => "URL kopieren",
                    "description" => "Wenn Sie auf der Verwaltungsseite des MetaGer Schlüssels sind, gibt es die Möglichkeit eine URL zu kopieren. Mit dieser URL lassen sich alle Einstellungen von MetaGer, sowie der MetaGer Schlüssel auf einem weiteren Gerät speichern.",
                ],
                [
                    "heading" => "Datei sichern",
                    "description" => "Wenn Sie auf der Verwaltungsseite des MetaGer Schlüssels sind, gibt es die Möglichkeit eine Datei zu sichern. Damit speichern Sie Ihren MetaGer-Schlüssel als Datei ab. Diese Datei können Sie dann auf einem anderen Gerät verwenden, um sich dort mit Ihrem Schlüssel einzuloggen.",
                ],
                [
                    "heading" => "QR Code scannen",
                    "description" => "Alternativ können Sie außerdem den QR Code, der auf der Verwaltungsseite angezeigt wird, scannen um sich bei einem weiteren Gerät einzuloggen.",
                ],
                [
                    "heading" => "MetaGer Schlüssel manuell eingeben",
                    "description" => "Sie können natürlich auch den Schlüssel manuell auf einem weiteren Gerät eingeben.",
                ],
            ],
        ],
        [
            "summary" => "Ich muss meinen Schlüssel regelmäßig eingeben. Was kann ich tun?",
            "description" => "Wir weisen Ihren Browser an, den einmal generierten oder eingeloggten Schlüssel dauerhaft zu speichern. Je nach Konfiguration Ihres Browsers kann es sein, dass Sie ihn so eingestellt haben, dass er regelmäßig Cookies und Websitedaten löscht, was Sie natürlich auch bei MetaGer abmeldet. Sie haben die folgenden Möglichkeiten:",
            "steps" => [
                [
                    "heading" => "Eine Ausnahme hinzufügen",
                    "description" => "In den Firefox-Einstellungen können Sie MetaGer auf eine Whitelist setzen, damit Cookies und Websitedaten nicht gelöscht werden und Sie weiterhin eingeloggt bleiben.",
                ],
                [
                    "heading" => "Installieren Sie unsere Browsererweiterung",
                    "description" => "Unsere Browsererweiterung für <a href=\"https://addons.mozilla.org/firefox/addon/metager-suche/\" target=\"_blank\" rel=\"noopener\">Firefox</a> und <a href=\"https://chrome.google.com/webstore/detail/gjfllojpkdnjaiaokblkmjlebiagbphd\" target=\"_blank\" rel=\"noopener\">Chrome</a> kann Ihre Sucheinstellungen einschließlich Ihres Schlüssels speichern, ohne Cookies zu verwenden, so dass Sie alle Browserdaten löschen können, ohne von MetaGer abgemeldet zu werden.",
                ],
                [
                    "heading" => "Anmeldung ohne Eingabe des 36-stelligen Schlüssels",
                    "description" => "Wenn Sie einen Passwort-Manager verwenden, können Sie den Schlüssel darin speichern, damit Sie automatisch eingeloggt werden können. Alternativ bieten wir eine <a href=\":keylink\">Einstellungen-URL</a> an, die Sie z. B. als Lesezeichen speichern können. Wenn Sie die Einstellungs-URL öffnen, melden Sie sich an, ohne den Schlüssel manuell eingeben zu müssen.",
                ],
            ],
        ],
        [
            "summary" => "Ich bin mit dem MetaGer Schlüssel unzufrieden. Was kann ich tun?",
            "description" => "In diesem Fall können Sie innerhalb von 30 Tagen nach dem Kauf eine Rückerstattung für nicht verwendete Token beantragen. Hierfür benötigen Sie Ihre Zahlungs-ID. Um eine Rückerstattung zu beantragen, öffnen Sie die MetaGer-Schlüsselverwaltungsseite. Klicken Sie dort auf den Menüpunkt \"Bestellungen\" und geben Sie Ihre Zahlungs-ID ein. Danach können Sie auf die Schaltfläche \"Rückerstattung anfordern\" klicken und den Rückerstattungsantrag abschicken.",
        ],
        [
            "summary" => "Wie kann ich völlig anonym suchen?",
            "description" => "Ihre Privatsphäre und Anonymität sind für uns sehr wichtig. Deshalb bieten wir anonyme Zahlungsmöglichkeiten (Bargeld) an. Wir bieten auch die Verwendung von <a href=\":tokenlink\">anonymen Token</a> an, die sie sogar verwenden können, um nachweislich anonym zu suchen.",
        ],
        [
            "summary" => "Ich brauche eine Rechnung. Wie bekomme ich sie?",
            "description" => "Hierfür benötigen Sie lediglich Ihre Zahlungs-ID. Um die Rechnung anzufordern, öffnen Sie die MetaGer Schlüsselverwaltungsseite. Hier klicken Sie auf den Menüpunkt \"Aufträge\" und geben Ihre Zahlungs-ID ein. Nun können Sie auf den Button \"Rechnung anfordern\" klicken und die Rechnungsanforderung starten. Für die Rechnung benötigen wir Ihren vollständigen Namen, Ihre E-Mail Adresse und Ihre Anschrift.",
        ],
        [
            "summary" => "Ich möchte meinen MetaGer-Schlüssel automatisch aufladen lassen. Wie kann ich das tun?",
            "description" => "Für unsere Mitglieder wird der in der Mitgliedschaft enthaltene Schlüssel automatisch monatlich aufgefüllt. Die Höhe des Tokens hängt dabei von der Höhe des Mitgliedsbeitrags ab.",
        ],
        [
            "summary" => "Ich habe eine Karte oder einen Link mit einem Gutschein-Code erhalten. Was mache ich damit?",
            "description" => "Manche Organisationen verschenken MetaGer-Schlüssel mit einem festen Guthaben über Aktionskarten oder einen Link. Öffnen Sie dazu <a href=\":voucherlink\">unsere Einlöseseite</a>, geben Sie den aufgedruckten Code ein oder scannen Sie den QR-Code auf der Karte. Sie erhalten dann sofort einen neuen MetaGer-Schlüssel mit dem geschenkten Guthaben, das für eine begrenzte Zeit gültig ist. Jeder Code lässt sich nur einmal einlösen.",
        ],
    ],
    "more-questions" => "Haben Sie weitere Fragen? Dann verwenden Sie gerne unser <a href=\":contactlink\" target=\"_blank\">Kontaktformular</a>.",
];
