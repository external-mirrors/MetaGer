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
        "Detta är vad din MetaGer-nyckel kostar",
        "Den viktigaste sammanfattningen",
    ],
    "texts" => [
        "För varje annonsfri webbsökning på MetaGer med standardinställningar kommer du att debiteras <b>1 token</b>. Du kan när som helst fylla på din nyckel med ett av dessa tokenpaket.",
    ],
    "short-info" => [
        [
            "heading" => "Tokens är giltiga i 2 år",
            "text" => "Dina köpta token är utformade för att vara giltiga tills de är förbrukade. Det finns ingen stående order.",
        ],
        [
            "heading" => "30 dagars pengarna-tillbaka-garanti",
            "text" => "Om du inte är nöjd med din nyckel har du 30 dagar på dig efter köpet att returnera den oanvända krediten.",
        ],
        [
            "heading" => "Nyckeln installeras automatiskt och används i webbläsaren",
            "text" => "Du behöver inte göra något annat för att använda din MetaGer-nyckel i sökningen. När du har laddat den konfigureras den automatiskt i din webbläsare och du får information om hur du enkelt konfigurerar den på ytterligare enheter.",
        ],
        [
            "heading" => "Ingen spårning",
            "text" => "Använd vår <a href=\":linkapp\">Android-app</a> eller vårt webbläsartillägg och var bevisligen anonym med hjälp av <a href=\":linktokens\">anonymous tokens</a>.",
        ],
    ],
    "pricing" => [
        "heading" => "Så här är våra priser sammansatta",
        "texts" => [
            "Merparten av våra intäkter går direkt till de söktjänster som du använder. Vi vill erbjuda ett hållbart koncept, vilket innebär att de efterfrågade sökmotorerna inte lider någon ekonomisk skada genom att tillhandahålla anonyma och annonsfria sökresultat för MetaGer. Dessutom tillkommer en andel för att täcka våra personal- och serverkostnader, och naturligtvis ingår avgifterna för betaltjänstleverantörer och skatter i priserna.",
            "Genom att välja vilka söktjänster som ska efterfrågas kan du alltså inte bara fastställa dina egna kostnader, utan också samtidigt bestämma vilka projekt du vill stödja. Därav också den tokenbaserade faktureringen.",
        ],
    ],
    "payment-methods" => [
        "heading" => "Betalningsmetoder",
        "texts" => [
            "MetaGer-nycklar har utformats av oss på ett sådant sätt att de inte kräver några personuppgifter. Ändå, senast under genomförandet av en betalning, krävs vanligtvis vissa uppgifter. Det kan vara IBAN för det betalande kontot eller e-postadressen till det PayPal-konto som används. SUMA-EV behandlar inte dessa uppgifter själv och lagrar dem inte. Beroende på betalningsmetod gör dock betaltjänstleverantören det.",
            "Därför är våra betalningsmetoder konfigurerade på ett sådant sätt att så lite som möjligt, och i vissa fall till och med inga användaruppgifter alls, behöver samlas in.",
        ],
        "anonymous" => "Anonyma betalningsmetoder",
        "more" => "Andra betalningsmetoder",
    ],
    /**
     * Die Namen der Zahlungsarten. Standen im "checkout"-Namensraum des
     * Keymanagers, der dort bleibt — hierher kopiert, weil diese Seite die
     * einzige war, die sie außerhalb des Bezahlvorgangs gebraucht hat.
     */
    "methods" => [
        "cash" => "Kontanter",
        "prepay" => "Banköverföring",
        "card" => "Kredit- eller betalkort",
    ],
];
