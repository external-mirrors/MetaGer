<?php

/**
 * Was ein MetaGer-Schlüssel kostet — /preise.
 *
 * Aus pass/lang/<locale>/cost.json des Keymanagers übernommen, wo diese Seite
 * bis zum Umzug lag. Die Preiszahlen selbst stehen bewusst nicht hier: sie
 * kommen über App\Landing\KeyPrice vom Keymanager, weil der Checkout sie
 * ausgibt.
 */

return [
    "headings" => [
        "Das kostet Ihr MetaGer Schlüssel",
        "Das Wichtigste zusammengefasst",
    ],
    "texts" => [
        "Für jede werbefreie Websuche auf MetaGer mit Standardeinstellungen wird Ihnen <b>1 Token</b> berechnet. Sie können Ihren Schlüssel jederzeit mit einem dieser Token-Pakete aufladen.",
    ],
    "short-info" => [
        [
            "heading" => "Token bleiben 2 Jahre lang gültig",
            "text" => "Ihre gekauften Token sind darauf ausgelegt so lange gültig zu bleiben, bis sie verbraucht wurden. Es gibt kein Abo.",
        ],
        [
            "heading" => "30 Tage Geld Zurück Garantie",
            "text" => "Sollten Sie mit Ihrem Schlüssel unzufrieden sein, haben Sie nach dem Kauf 30 Tage Zeit, das nicht verbrauchte Guthaben wieder zurück zu geben.",
        ],
        [
            "heading" => "Schlüssel wird automatisch im Browser eingerichtet und verwendet",
            "text" => "Um Ihren MetaGer Schlüssel bei der Suche zu verwenden, brauchen Sie nichts weiter tun. Nach dem Aufladen ist er automatisch in Ihrem Browser eingerichtet und Sie erhalten Informationen zur einfachen Einrichtung auf weiteren Geräten.",
        ],
        [
            "heading" => "Trackingfrei",
            "text" => "Verwenden Sie unsere <a href=\":linkapp\">Android App</a>, oder unsere Browsererweiterung und seien Sie unter Verwendung von <a href=\":linktokens\">anonymen Token</a> beweisbar anonym unterwegs.",
        ],
    ],
    "pricing" => [
        "heading" => "So setzen sich unsere Preise zusammen",
        "texts" => [
            "Der größte Teil unserer Einnahmen fließt direkt weiter an die von Ihnen abgefragten Suchdienste. Wir möchten ein nachhaltiges Konzept anbieten, das beinhaltet, dass den abgefragten Suchmaschinen durch die Bereitstellung anonymer und werbefreier Suchergebnisse für MetaGer kein finanzieller Schaden entsteht. Hinzu kommt ein Anteil zur Deckung unserer Personal und Serverkosten und selbstverständlich sind die Gebühren für Zahlungsdienstleister und Steuern in den Preisen enthalten.",
            "So können Sie mit der Auswahl der abzufragenden Suchdienste nicht nur Ihre eigenen Kosten festlegen, sondern auch gleichzeitig entscheiden, welche Projekte Sie unterstützen möchten. Deshalb auch die Token basierte Abrechnung.",
        ],
    ],
    "payment-methods" => [
        "heading" => "Zahlungsmethoden",
        "texts" => [
            "MetaGer Schlüssel wurden von uns so konzipiert, dass Sie per Design ohne personenbeziehbare Daten auskommen. Nichtsdestotrotz fallen spätestens bei der Durchführung einer Zahlung meist welche an. Sei es nun die IBAN des zahlenden Kontos, oder die E-Mail Adresse des verwendeten PayPal Kontos. Der SUMA-EV verarbeitet diese Daten nicht selbst und speichert sie auch nicht ab. Allerdings tut es je nach Zahlungsmethode der Zahlungsdienstleister.",
            "Deshalb sind unsere Zahlungsmethoden so konfiguriert, dass möglichst wenig und teilweise sogar gar keine Nutzerdaten erfasst werden müssen.",
        ],
        "anonymous" => "Anonyme Zahlungsmethoden",
        "more" => "Weitere Zahlungsmethoden",
    ],
    /**
     * Die Namen der Zahlungsarten. Standen im "checkout"-Namensraum des
     * Keymanagers, der dort bleibt — hierher kopiert, weil diese Seite die
     * einzige war, die sie außerhalb des Bezahlvorgangs gebraucht hat.
     */
    "methods" => [
        "cash" => "Bargeld",
        "prepay" => "Überweisung",
        "card" => "Kredit- / Debitkarte",
    ],
];
