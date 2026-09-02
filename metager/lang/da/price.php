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
        "Dette er, hvad din MetaGer-nøgle koster",
        "Den vigtigste opsummering",
    ],
    "texts" => [
        "For hver reklamefri websøgning på MetaGer med standardindstillinger vil du blive opkrævet <b>1 token</b>. Du kan til enhver tid fylde din nøgle op med en af disse token-pakker.",
    ],
    "short-info" => [
        [
            "heading" => "Tokens er gyldige i 2 år",
            "text" => "Dine købte token er designet til at forblive gyldige, indtil de er brugt op. Der er ingen stående ordre.",
        ],
        [
            "heading" => "30 dages pengene-tilbage-garanti",
            "text" => "Hvis du er utilfreds med din nøgle, har du 30 dage efter købet til at returnere den ubrugte kredit.",
        ],
        [
            "heading" => "Nøglen oprettes og bruges automatisk i browseren.",
            "text" => "Du behøver ikke at gøre andet for at bruge din MetaGer-nøgle i søgningen. Når du har opladet den, bliver den automatisk sat op i din browser, og du vil modtage information om, hvordan du nemt sætter den op på flere enheder.",
        ],
        [
            "heading" => "Ingen sporing",
            "text" => "Brug vores <a href=\":linkapp\">Android-app</a> eller vores browserudvidelse, og vær beviseligt anonym ved hjælp af <a href=\":linktokens\">anonyme tokens</a>.",
        ],
    ],
    "pricing" => [
        "heading" => "Sådan er vores priser sammensat",
        "texts" => [
            "Størstedelen af vores indtægter går direkte videre til de søgetjenester, du søger på. Vi ønsker at tilbyde et bæredygtigt koncept, som indebærer, at de søgemaskiner, der forespørges på, ikke lider nogen økonomisk skade ved at levere anonyme og reklamefri søgeresultater til MetaGer. Derudover er der en andel til at dække vores personale- og serveromkostninger, og selvfølgelig er gebyrerne til betalingstjenesteudbydere og skatter inkluderet i priserne.",
            "Ved at vælge de søgetjenester, der skal forespørges på, kan du således ikke kun fastsætte dine egne omkostninger, men også samtidig beslutte, hvilke projekter du vil støtte. Derfor også den token-baserede fakturering.",
        ],
    ],
    "payment-methods" => [
        "heading" => "Betalingsmetoder",
        "texts" => [
            "MetaGer-nøgler er designet af os på en sådan måde, at de ikke kræver nogen personlige data. Ikke desto mindre kræves der normalt nogle data senest under udførelsen af en betaling. Det kan være IBAN-nummeret på den betalende konto eller e-mail-adressen på den anvendte PayPal-konto. SUMA-EV behandler ikke selv disse data og gemmer dem ikke. Men afhængigt af betalingsmetoden gør udbyderen af betalingstjenesten det.",
            "Derfor er vores betalingsmetoder konfigureret på en sådan måde, at så lidt som muligt, og i nogle tilfælde slet ingen brugerdata, behøver at blive indsamlet.",
        ],
        "anonymous" => "Anonyme betalingsmetoder",
        "more" => "Andre betalingsmetoder",
    ],
    /**
     * Die Namen der Zahlungsarten. Standen im "checkout"-Namensraum des
     * Keymanagers, der dort bleibt — hierher kopiert, weil diese Seite die
     * einzige war, die sie außerhalb des Bezahlvorgangs gebraucht hat.
     */
    "methods" => [
        "cash" => "Kontanter",
        "prepay" => "Bankoverførsel",
        "card" => "Kredit-/debetkort",
    ],
];
