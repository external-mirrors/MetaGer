<?php

/**
 * Anonyme Token — /hilfe/anonyme-token.
 *
 * Aus dem "anonymous-token"-Zweig von pass/lang/<locale>/help.json.
 * Der Pfad /keys/help/anonymous-token wird dauerhaft hierher weitergeleitet:
 * er steht in bereits versandten Mitglieds-Willkommensmails.
 */

return [
    "heading" => "Anonieme tokens",
    "description" => [
        "heading" => "Wat zijn anonieme tokens?",
        "text" => "Als je een MetaGer-sleutel gebruikt, ontvang je een willekeurig gegenereerd wachtwoord dat je browser bij elke zoekopdracht naar ons stuurt zodat we advertentievrij zoeken kunnen inschakelen. Als je onze <a href=\"/app\" target=\"_blank\">Android app</a>, of onze webextensie voor <a href=\"https://chrome.google.com/webstore/detail/gjfllojpkdnjaiaokblkmjlebiagbphd\" target=\"_blank\" rel=\"noopener\">Chrome</a> en <a href=\"https://addons.mozilla.org/firefox/addon/metager-suche/\" target=\"_blank\" rel=\"noopener\">Firefox</a>, gebruikt in plaats van het wachtwoord, stuurt je browser ons een willekeurig gegenereerd wachtwoord (anoniem token) bij elke zoekopdracht voor verificatie, dat lokaal wordt gegenereerd. Dit zorgt ervoor dat elk wachtwoord uniek is en geen verband houdt met de werkelijke MetaGer-sleutel, noch tussen de individuele wachtwoorden.",
    ],
    "problem" => [
        "heading" => "Welk probleem moeten anonieme tokens oplossen?",
        "text" => "Als je browser ons altijd hetzelfde wachtwoord stuurt bij elke zoekopdracht, zouden we in theorie tenminste de mogelijkheid hebben om een correlatie vast te stellen tussen alle zoekopdrachten die met dezelfde sleutel zijn uitgevoerd. Zelfs als we dat niet doen, is vertrouwen natuurlijk nog steeds nodig om zeker te zijn van je anonieme zoekopdracht. Zodat we niet alleen de anonieme zoekopdracht hoeven te beloven, maar deze ook kunnen bewijzen, hebben we de anonieme tokens geïntroduceerd.",
    ],
    "general-function" => [
        "heading" => "Hoe werkt het?",
        "texts" => [
            "Dus we willen eenmalige wachtwoorden die direct vanaf uw eindpuntapparaat worden gegenereerd, die u vervolgens naar ons stuurt voor verificatie tijdens uw zoekopdrachten. Voor elk anoniem token op uw eindapparaat moeten we er echter voor zorgen dat er een regulier token is afgetrokken van uw MetaGer-sleutel, zonder (en dit is de crux) ons te vertellen welke MetaGer-sleutel is gebruikt om het anonieme token te genereren.",
            "Traditioneel zouden we hiervoor een vorm van cryptografische handtekening gebruiken. In dit geval ondertekenen we het gegenereerde anonieme token. Wanneer je ons dan op een later tijdstip het anonieme token samen met de handtekening stuurt, kunnen we er zeker van zijn dat het anonieme token geldig is. Om de handtekening te krijgen, zou je ons echter het anonieme token samen met je echte sleutel hebben gestuurd, wat de anonimiteit teniet zou doen.",
            "Daarom gebruiken we in plaats daarvan een aangepaste vorm van cryptografische handtekening, de zogenaamde <a href=\"https://en.wikipedia.org/wiki/Blind_signature\" target=\"_blank\">blinde handtekening</a>. Om een levensechte analogie te maken, is het alsof je ons je anonieme token in een carbonpapieren envelop stuurt. In dit voorbeeld zouden we niet in staat zijn om de envelop te openen, maar we zouden wel in staat zijn om te tekenen vanaf de buitenkant, zodat onze handtekening zou worden overgedragen aan het anonieme token binnenin. Als je de envelop terugkrijgt, kun je deze verwijderen en ons later het wachtwoord en de handtekening terugsturen. We kunnen dan bevestigen dat het inderdaad onze handtekening is.",
            "In feite is deze analogie een beetje misleidend, want in het werkelijke proces, op het moment dat je ons het anonieme token en de handtekening stuurt, hebben we niet alleen nog nooit het anonieme token gezien, maar ook nog nooit de handtekening zelf. En toch kunnen we verifiëren dat de handtekening door ons is gegenereerd.",
        ],
    ],
    "meaning" => [
        "heading" => "Wat betekent dit voor je geauthenticeerde zoekopdrachten?",
        "texts" => [
            "Door het beschreven algoritme te gebruiken, kunnen zowel wij als jij ervoor zorgen dat een nieuw willekeurig wachtwoord dat niet gerelateerd is aan je MetaGer-sleutel elke keer wordt gebruikt voor je geauthenticeerde zoekopdrachten.",
            "Het bijzondere aan dit algoritme is dat alle onderdelen die voor anonimiteit zorgen, lokaal op je apparaat worden uitgevoerd. Deze uitgevoerde broncode kan op elk moment door iedereen worden bekeken en geverifieerd.",
            "Het beste van alles is dat je niets hoeft te configureren om anonieme tokens te gebruiken. Je hoeft alleen maar onze browserextensie/Android app te installeren/gebruiken om je apparaat anonieme tokens te laten gebruiken voor alle zoekopdrachten.",
        ],
    ],
    "technical-function" => [
        "heading" => "Het algoritme erachter:",
        "texts" => [
            "In een klassieke RSA-handtekening zouden we het anonieme token <code>m</code>, de geheime exponent <code>d</code>, en de publieke modulus <code>N</code> van onze privésleutel nemen en de handtekening maken met <code>m^d (mod N)</code>. We willen echter dat <code>m</code> geheim blijft.",
            "Daarom maakt je terminal een willekeurig getal <code>r</code> met een willekeurige getallengenerator, dat divisor-ongerelateerd is aan <code>N</code>. Dus de grootste gemene deler van <code>r</code> en <code>N</code> moet <code>1</code> zijn.",
            "Omdat <code>r</code> een willekeurig getal is, volgt hieruit dat <code>m'</code> geen informatie prijsgeeft over de lokaal opgeslagen anonieme token <code>m</code>.",
            "Onze server ontvangt nu het gecodeerde anonieme token <code>m'</code> van je eindapparaat samen met de te gebruiken MetaGer-sleutel. We trekken een token af van de sleutel en sturen de eveneens gecodeerde handtekening <code>s'&Congruent; (m')^d (mod N)</code> terug naar je eindapparaat.",
            "Je terminal kan nu de werkelijke geldige RSA-handtekening <code>s</code> voor het onversleutelde anonieme token berekenen: <code>s&Congruent; s' r^-1 (mod N)</code>. Dit werkt omdat voor RSA-sleutels, <code>r^(e*d)&Congruent; r (mod N)</code>. En daarom ook: <code>s &Congruent; s' * r^-1 &Congruent; (m')^d*r^-1 &Congruent; m^d*r^(e*d)*r^-1 &Congruent; m^d*r*r^-1 &Congruent; m^d (mod N)</code>.",
            "Je eindapparaat stuurt ons nu het onversleutelde anonieme token samen met de bijbehorende handtekening voor autorisatie tijdens een zoekopdracht. De sleutel zelf wordt tijdens het zoeken niet meer naar ons verzonden.",
        ],
    ],
];
