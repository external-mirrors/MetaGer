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
        "Dit is wat je MetaGer-sleutel kost",
        "De belangrijkste samengevat",
    ],
    "texts" => [
        "Voor elke advertentievrije zoekopdracht op MetaGer met standaardinstellingen wordt <b>1 token in rekening gebracht</b>. Je kunt je sleutel op elk moment opwaarderen met een van deze tokenpakketten.",
    ],
    "short-info" => [
        [
            "heading" => "Tokens blijven 2 jaar geldig",
            "text" => "Je gekochte token blijven geldig tot ze op zijn. Er is geen doorlopende opdracht.",
        ],
        [
            "heading" => "30 dagen geld terug garantie",
            "text" => "Als je niet tevreden bent met je sleutel, heb je 30 dagen na aankoop om het ongebruikte tegoed terug te sturen.",
        ],
        [
            "heading" => "Sleutel wordt automatisch ingesteld en gebruikt in de browser",
            "text" => "Je hoeft verder niets te doen om je MetaGer sleutel te gebruiken bij het zoeken. Nadat je hem hebt opgeladen, wordt hij automatisch ingesteld in je browser en ontvang je informatie over hoe je hem gemakkelijk kunt instellen op andere apparaten.",
        ],
        [
            "heading" => "Geen tracking",
            "text" => "Gebruik onze <a href=\":linkapp\">Android app</a>, of onze browser extensie en wees bewijsbaar anoniem met behulp van <a href=\":linktokens\">anonieme tokens</a>.",
        ],
    ],
    "pricing" => [
        "heading" => "Zo zijn onze prijzen samengesteld",
        "texts" => [
            "Het grootste deel van onze inkomsten vloeit rechtstreeks naar de zoekdiensten die je raadpleegt. We willen een duurzaam concept aanbieden, wat inhoudt dat de zoekmachines die worden bevraagd geen financiële schade lijden door het leveren van anonieme en advertentievrije zoekresultaten voor MetaGer. Daarnaast is er een deel om onze personeels- en serverkosten te dekken, en natuurlijk zijn de kosten voor betalingsproviders en belastingen inbegrepen in de prijzen.",
            "Door de zoekservices te selecteren die moeten worden opgevraagd, kunt u dus niet alleen uw eigen kosten bepalen, maar ook tegelijkertijd beslissen welke projecten u wilt ondersteunen. Vandaar ook de token-gebaseerde facturering.",
        ],
    ],
    "payment-methods" => [
        "heading" => "Betaalmethoden",
        "texts" => [
            "De sleutels van MetaGer zijn door ons zo ontworpen dat ze geen persoonlijke gegevens vereisen. Desalniettemin, uiterlijk tijdens de uitvoering van een betaling, zijn er meestal enkele gegevens vereist. Bijvoorbeeld het IBAN van de betaalrekening of het e-mailadres van de gebruikte PayPal-rekening. SUMA-EV verwerkt deze gegevens niet zelf en slaat ze niet op. Afhankelijk van de betalingsmethode doet de betalingsdienstaanbieder dat wel.",
            "Daarom zijn onze betaalmethoden zo geconfigureerd dat er zo weinig mogelijk, en in sommige gevallen zelfs helemaal geen gebruikersgegevens, verzameld hoeven te worden.",
        ],
        "anonymous" => "Anonieme betalingsmethoden",
        "more" => "Andere betalingsmethoden",
    ],
    /**
     * Die Namen der Zahlungsarten. Standen im "checkout"-Namensraum des
     * Keymanagers, der dort bleibt — hierher kopiert, weil diese Seite die
     * einzige war, die sie außerhalb des Bezahlvorgangs gebraucht hat.
     */
    "methods" => [
        "cash" => "Contant",
        "prepay" => "Overschrijving",
        "card" => "Creditcard / bankpas",
    ],
];
