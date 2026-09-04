<?php

/**
 * MetaGer für Firmen und Organisationen — /firmen.
 *
 * Die Seite, auf die jeder Hinweis für Organisationen zeigt, und der einzige
 * Ort, der erklärt, was eine Mitgliedschaft für eine Organisation bedeutet.
 * Vorher gab es dafür nichts: das Beitrittsformular kennt seit jeher einen
 * Zweig „Als Firma beitreten?“, aber keine Seite sagte, worum es dabei geht —
 * und die eine bestehende Abmachung mit einer Universität ist eine Zeile in
 * `config/metager/metager.php`.
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

    "intro" => "Der SUMA-EV entwickelt und betreibt MetaGer, um freien Wissenszugang und eine unabhängige Suchmaschinentechnologie zu fördern. Auch Firmen, Schulen und Behörden können Mitglied werden und diese Arbeit tragen. Mitglieder suchen auf MetaGer werbefrei — an jedem Arbeitsplatz, ohne dass die Menschen an den Geräten etwas eingeben oder einrichten müssen.",

    "benefits" => [
        "heading" => "Was eine Mitgliedschaft bedeutet",
        "items" => [
            [
                "heading" => "Werbefreie Ergebnisse",
                "text" => "Mitglieder suchen auf MetaGer werbefrei: keine Anzeigen, keine als Ergebnis getarnte Werbung, keine Sortierung nach dem, was ein Werbekunde bezahlt hat. Was oben steht, steht oben, weil es zur Suchanfrage passt.",
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
                "heading" => "Sie tragen die Suchmaschine, statt sie zu bezahlen",
                "text" => "Ihr Beitrag fließt in die Entwicklung und den Betrieb von MetaGer und in die übrige Arbeit des Vereins. Für Ihre Buchhaltung ist er ein Mitgliedsbeitrag und keine Rechnung, die jeden Monat anders aussieht.",
            ],
        ],
    ],

    "setup" => [
        "heading" => "So läuft die Einrichtung",
        "steps" => [
            [
                "heading" => "Sie stellen einen Aufnahmeantrag",
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
            "heading" => "Ihre Mitarbeitenden sehen den Schlüssel nicht",
            "text" => "Die werbefreie Suche hängt bei MetaGer an einem Schlüssel. Bei einer Firmenmitgliedschaft gehört dieser Schlüssel Ihrer Organisation: er wird zentral hinterlegt, niemand muss ihn eingeben, und niemand bekommt ihn zu sehen. Deshalb finden Sie hier auch keine Datei zum Herunterladen — wir richten das gemeinsam mit Ihrer IT so ein, dass der Schlüssel dort bleibt, wo er hingehört.",
        ],
    ],

    "fee" => [
        "heading" => "Der Mitgliedsbeitrag",
        "text" => "Er richtet sich nach der Größe Ihrer Organisation. Maßgeblich ist die <a href=\":linkfeeorder\">Beitragsordnung</a> des SUMA-EV; die folgenden Beträge sind die Mindestbeiträge, nach oben ist jeder Betrag möglich.",
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
        "credit" => "Mitglieder suchen ohne weitere Kosten; der Schlüssel Ihrer Organisation wird dazu monatlich aufgefüllt. Wie sich der Aufwand einer Suche zusammensetzt und wohin er fließt, steht auf der <a href=\":linkprice\">Preisseite</a>.",
        "charity" => "Der SUMA-EV ist vom Finanzamt Hannover Nord als gemeinnützig anerkannt. Ihre Beiträge können somit steuerlich geltend gemacht werden; eine Zuwendungsbestätigung stellen wir aus.",
    ],

    "education" => [
        "heading" => "Schulen, Hochschulen und Behörden",
        "text" => "Für öffentliche Einrichtungen ist die Mitgliedschaft oft der einfachere Weg als ein Beschaffungsvorgang: ein Vereinsbeitrag ist keine Auftragsvergabe, und weil bei uns keine personenbezogenen Suchdaten anfallen, ist auch die Prüfung durch Ihre Datenschutzbeauftragten überschaubar. Der Vereinszweck passt zudem zu Ihrem: Medienkompetenz und freier Wissenszugang. Eine Universität lässt ihre Angehörigen bereits über uns suchen.",
        "cta" => "Wenn Sie für eine Schule oder eine Behörde fragen, schreiben Sie uns — wir kennen die Formulare, die Sie brauchen.",
    ],

    /**
     * Die Hinweise, die von anderen Seiten hierher zeigen.
     *
     * Sie stehen hier und nicht in `sidebar.php`, `price.php` oder
     * `account.php`, obwohl sie dort gerendert werden: diese Dateien gibt es in
     * zwölf Sprachen, und {@see \Tests\Feature\AccountTranslationsTest} sowie
     * {@see \Tests\Feature\KeyPagesTranslationsTest} rechnen ihre Schlüssel
     * gegeneinander auf. Ein deutscher Zusatz darin wäre ein fehlender
     * Schlüssel in elf anderen Sprachen — für einen Text, den es in elf anderen
     * Sprachen gar nicht geben soll. (Genau daran ist die erste Fassung
     * gescheitert: `sidebar.navBusiness`.)
     */
    "hints" => [
        "sidebar" => "MetaGer für Firmen",
        "price" => [
            "heading" => "Sie fragen für eine Organisation?",
            "text" => "Firmen, Schulen und Behörden können Mitglied im gemeinnützigen Trägerverein werden. Mitglieder suchen ohne weitere Kosten, und MetaGer lässt sich auf allen Geräten als Standardsuche einrichten, ohne dass jemand etwas eingeben muss.",
            "action" => "MetaGer für Firmen",
        ],
        "account" => [
            "heading" => "Sie laden für eine Organisation auf?",
            "text" => "Dann ist eine Mitgliedschaft im Trägerverein der ruhigere Weg: Mitglieder laden nicht auf, ihr Schlüssel füllt sich monatlich von selbst — und MetaGer lässt sich als Standardsuche auf allen Geräten einrichten.",
            "action" => "MetaGer für Firmen",
        ],
        "form" => "Was eine Mitgliedschaft für eine Organisation bedeutet und wie MetaGer bei Ihnen zur Standardsuche wird, steht auf <a href=\":linkbusiness\">MetaGer für Firmen</a>.",
    ],

    "actions" => [
        "join" => "Aufnahmeantrag für Ihre Organisation",
        "contact" => "Erst Fragen stellen",
    ],
];
