<?php

/**
 * MetaGer für Firmen und Organisationen — /firmen.
 *
 * Die Seite, auf die jeder Hinweis für Geschäftskunden zeigt, und der einzige
 * Weg in eine Firmenmitgliedschaft, der erklärt, was sie ist. Vorher gab es
 * dafür nichts: das Beitrittsformular kennt seit jeher einen Zweig „Als Firma
 * beitreten?“, aber keine Seite sagte, was eine Firma davon hat — und die eine
 * bestehende Abmachung mit einer Universität ist eine Zeile in
 * `config/metager/metager.php`, kein Angebot.
 *
 * Beträge stehen hier bewusst nicht. Sie kommen aus
 * {@see \App\Support\MembershipFee}, aus derselben Quelle wie die Validierung
 * des Formulars — eine Seite, die einen Beitrag verspricht, den das Formular
 * ablehnt, ist schlimmer als gar keine Seite.
 *
 * Nur auf Deutsch, wie jeder Hinweis auf die Mitgliedschaft
 * ({@see \App\Support\MembershipOffer}): das Formular dahinter, die Satzung und
 * die Beitragsordnung sind es auch.
 */

return [
    "title" => "MetaGer für Firmen und Organisationen",

    "intro" => "Werbefreie, anonyme Suche an jedem Arbeitsplatz — eingerichtet als Standardsuchmaschine, ohne dass Ihre Mitarbeitenden dafür etwas eingeben oder einrichten müssen. Abgerechnet wird das nicht nach Verbrauch, sondern über eine Mitgliedschaft in unserem gemeinnützigen Trägerverein.",

    "benefits" => [
        "heading" => "Was Ihre Organisation bekommt",
        "items" => [
            [
                "heading" => "Werbefreie Ergebnisse",
                "text" => "Keine Anzeigen, keine als Ergebnis getarnte Werbung, keine Sortierung nach dem, was ein Werbekunde bezahlt hat. Was oben steht, steht oben, weil es zur Suchanfrage passt.",
            ],
            [
                "heading" => "Keine Profile Ihrer Mitarbeitenden",
                "text" => "Wir legen keine Nutzerprofile an und werten Suchanfragen nicht personenbezogen aus. Was dabei überhaupt anfällt, steht vollständig in unserer <a href=\":linkprivacy\">Datenschutzerklärung</a>.",
            ],
            [
                "heading" => "Server in Deutschland, Verein in Deutschland",
                "text" => "MetaGer läuft auf eigener Hardware in Deutschland, betrieben vom SUMA-EV, einem gemeinnützigen Verein mit Sitz in Hannover. Es gibt keinen Konzern dahinter und keine Datenweitergabe an einen.",
            ],
            [
                "heading" => "Ein Beitrag statt einer Verbrauchsabrechnung",
                "text" => "Sie zahlen einen monatlichen Mitgliedsbeitrag und bekommen dafür Suchguthaben im Gegenwert dieses Beitrags, das jeden Monat neu aufgefüllt wird. Keine Rechnung, die mit der Nutzung schwankt, und nichts, was Ihre Buchhaltung monatlich prüfen müsste.",
            ],
        ],
    ],

    "setup" => [
        "heading" => "So läuft die Einrichtung",
        "steps" => [
            [
                "heading" => "Sie beantragen die Mitgliedschaft",
                "text" => "Über das Beitrittsformular, als Firma. Mehr als Name, Größenklasse und eine E-Mail-Adresse brauchen wir dafür nicht.",
            ],
            [
                "heading" => "Wir melden uns bei Ihrer IT",
                "text" => "Wir stimmen mit den Menschen, die Ihre Geräte verwalten, ab, wie MetaGer bei Ihnen ausgerollt wird — über die Browserverwaltung, die Geräteverwaltung oder das Image, mit dem Ihre Rechner aufgesetzt werden.",
            ],
            [
                "heading" => "MetaGer ist die Standardsuche",
                "text" => "Danach sucht jeder Arbeitsplatz werbefrei, aus der Adressleiste heraus wie vorher. Für die Menschen an den Geräten ändert sich nichts außer den Ergebnissen.",
            ],
        ],
        /**
         * Der ehrliche Teil.
         *
         * Der Zugang hängt an einem Schlüssel, und ein Schlüssel, der in einer
         * Browsereinstellung steht, ist auslesbar. Deshalb gibt es hier keinen
         * Selbstbedienungs-Download: das ist keine Ausrede für eine fehlende
         * Funktion, sondern der Grund, warum die Einrichtung über Ihre IT läuft.
         */
        "hint" => [
            "heading" => "Ihre Mitarbeitenden bekommen keinen Schlüssel in die Hand",
            "text" => "Der Zugang zu einer werbefreien Suche hängt bei MetaGer an einem Schlüssel. Bei einer Firmenmitgliedschaft gehört dieser Schlüssel Ihrer Organisation: er wird zentral hinterlegt, niemand muss ihn eingeben, und niemand bekommt ihn zu sehen. Deshalb finden Sie hier auch keine Datei zum Herunterladen — wir richten das gemeinsam mit Ihrer IT so ein, dass der Schlüssel dort bleibt, wo er hingehört.",
        ],
    ],

    "fee" => [
        "heading" => "Was es kostet",
        "text" => "Der Mitgliedsbeitrag richtet sich nach der Größe Ihrer Organisation. Maßgeblich ist die <a href=\":linkfeeorder\">Beitragsordnung</a> des SUMA-EV; die folgenden Beträge sind die Mindestbeiträge, nach oben ist jeder Betrag möglich.",
        "columns" => [
            "employees" => "Mitarbeitende",
            "amount" => "ab … im Monat",
        ],
        "brackets" => [
            "1-19" => "bis 19",
            "20-199" => "20 bis 199",
            ">200" => "ab 200",
        ],
        "unit" => "€ / Monat",
        "credit" => "Im Beitrag enthalten ist Suchguthaben im Gegenwert des Beitrags, das monatlich aufgefüllt wird. Was eine Suche kostet, steht auf der <a href=\":linkprice\">Preisseite</a>.",
        "charity" => "Der SUMA-EV ist vom Finanzamt Hannover Nord als gemeinnützig anerkannt. Ihre Beiträge können somit steuerlich geltend gemacht werden; eine Zuwendungsbestätigung stellen wir aus.",
    ],

    "education" => [
        "heading" => "Schulen, Hochschulen und Behörden",
        "text" => "Für öffentliche Einrichtungen ist die Mitgliedschaft oft der einfachere Weg als ein Beschaffungsvorgang: ein Vereinsbeitrag ist keine Auftragsvergabe, und weil bei uns keine personenbezogenen Suchdaten anfallen, ist auch die Prüfung durch Ihre Datenschutzbeauftragten überschaubar. Eine Universität lässt ihre Angehörigen bereits über uns suchen.",
        "cta" => "Wenn Sie für eine Schule oder eine Behörde fragen, schreiben Sie uns — wir kennen die Formulare, die Sie brauchen.",
    ],

    "actions" => [
        "join" => "Firmenmitgliedschaft beantragen",
        "contact" => "Erst Fragen stellen",
    ],
];
