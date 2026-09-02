<?php

/**
 * Allgemeine Geschäftsbedingungen für die Token-Aufladung — /agb.
 *
 * Vertragstext, aus pass/lang/<locale>/agb.json des Keymanagers übernommen.
 * Tests\Feature\AgbTest vergleicht die gerenderte deutsche Fassung Zeile für
 * Zeile mit einem Abzug der alten Seite; jede Abweichung steht dort
 * ausgeschrieben, damit sie mit rechtlichem Blick nachlesbar bleibt. Es sind
 * drei:
 *
 *   - Der Text nennt seine eigene Fundstelle. Die stand wörtlich als
 *     "metager.de/keys/agb" im Vertrag und ist jetzt der Platzhalter :agburl.
 *   - Die Paketliste in §4 nannte 12000 Token, die es nicht zu kaufen gibt,
 *     und verschwieg die 500, die es gibt. Sie zählt jetzt genau das auf, was
 *     der Checkout verkauft — AgbTest::testTheTokenPackagesAreTheOnesThatCanBeBought
 *     vergleicht sie in allen Sprachen mit App\Landing\KeyPrice.
 *   - Weil sich der Vertragstext damit geändert hat, ist auch das "Stand:"-
 *     Datum weitergerückt.
 */

return [
    "heading" => "Algemene voorwaarden voor het opwaarderen van tokens (on Key)",
    "date" => "Status: Augustus 2026",
    "translationNotice" => "Opmerking: Dit is een vertaling van de geldige Duitse algemene voorwaarden. De wettelijk bindende versie vindt u <a href=\":linkGerman\">hier</a>",
    "paragraphs" => [
        [
            "heading" => "Aanbieder, toepassingsgebied en wijzigingen",
            "paragraphs" => [
                "De volgende Algemene Voorwaarden zijn van toepassing op de zakelijke relaties tussen gebruikers van de diensten van de websites metager.de en metager.org, in het bijzonder het opwaarderen van tokens op de sleutel, en de exploitant SUMA-EV. Hierna worden de 'gebruikers' van het opwaarderen van tokens / de sleutel ook 'gebruikers' genoemd, en SUMA-EV wordt hierna 'MetaGer' genoemd.",
                "Deze AV zijn op elk moment beschikbaar op :agburl en kunnen op elk moment worden geopend, opgeslagen en afgedrukt. Bestellingen uit het verleden kunnen worden bekeken in het klantengedeelte onder 'Beheer sleutel - Bestellingen' door de betalings-ID in te voeren. Dit is alleen mogelijk binnen 30 dagen na de aankoopdatum.",
                "Deze voorwaarden gelden uitsluitend voor gebruikers die consumenten zijn in de zin van § 13 van het Duitse Burgerlijk Wetboek. Een consument is elke natuurlijke persoon die een juridische transactie aangaat voor doeleinden die overwegend noch commercieel noch zelfstandig zijn.",
                "MetaGer behoudt zich het recht voor om de gebruikersgroep en de groep van in aanmerking komende deelnemers uit te breiden of te beperken en behoudt zich verder het recht voor om deze algemene voorwaarden voor 'gebruikers' te allen tijde te wijzigen of aan te vullen indien dit noodzakelijk is in het belang van een eenvoudige of veilige verwerking of om misbruik te voorkomen. Wijzigingen in de algemene voorwaarden worden aangekondigd door publicatie op de website van MetaGer. Als de gebruiker het niet eens is met dergelijke wijzigingen of toevoegingen aan de AV, moet hij binnen 4 weken schriftelijk bezwaar maken tegen de wijziging bij MetaGer. Anders worden de gewijzigde AV geacht te zijn goedgekeurd en worden ze dus een effectief onderdeel van het contract.",
                "De online zoekmachine metager.de, haar partnersites en bijbehorende software worden geëxploiteerd door SUMA-EV. Het hoofdkantoor van SUMA-EV is Henniesruh 28D, 30655 Hannover. SUMA-EV wordt vertegenwoordigd door het bestuur, dat op zijn beurt wordt vertegenwoordigd door de algemeen directeur Dominik Hebeler. Registratienummer: VR200033, Register rechtbank: Amtsgericht Hannover.",
                "De volgende contactgegevens zijn van toepassing:\nTelefoon: +49 511 34000070\nFax: +49 511 34001023\nContactformulier: metager.de/kontakt\n*Vast telefoonnummer.\n",
                "Volgens de verordening inzake online geschillenbeslechting in consumentenzaken verwijzen we naar de volgende link: http://ec.europa.eu/consumers/odr/",
            ],
        ],
        [
            "heading" => "Sluiting van het contract en betalingsvoorwaarden",
            "paragraphs" => [
                "Het aanbieden van de verschillende tokenpakketten door MetaGer vormt geen wettelijk bindend contractueel aanbod, maar slechts een niet-bindende uitnodiging aan de gebruiker om een opwaardering of aankoop te doen. Door te klikken op de knop 'Betaling verrichten' of een vergelijkbare tekst, dient de gebruiker een wettelijk bindend aanbod in om een aankoopcontract te sluiten met MetaGer.",
                "Voordat de bestelling bindend wordt verzonden, kan de gebruiker terugkeren naar de website waar de informatie is opgeslagen en invoerfouten corrigeren of het proces annuleren door de internetbrowser te sluiten door op de 'Terug'-knop te drukken in de internetbrowser die hij gebruikt nadat hij zijn gegevens heeft gecontroleerd.",
                "De vermelde prijzen zijn inclusief wettelijke btw en andere prijscomponenten. Aangezien dit een service is, is er geen verzending nodig en worden de tokens onmiddellijk beschikbaar gesteld nadat het betalingsproces is voltooid. Vooruitbetaling is mogelijk. Als de gebruiker heeft gekozen voor vooruitbetaling, verbindt hij zich ertoe de aankoopprijs onmiddellijk na het afsluiten van het contract te betalen.",
            ],
        ],
        [
            "heading" => "Garantie, contracttaal en klantenservice",
            "paragraphs" => [
                "De wettelijke garantiebepalingen zijn van toepassing.",
                "De contracttaal is Duits.",
                "Een klantenservice voor vragen, klachten en bezwaren is beschikbaar op werkdagen van 9:00 tot 16:00 uur via de contactgegevens van SUMA-EV.",
            ],
        ],
        [
            "heading" => "Sleutel, betalingsopties en opwaarderen",
            "paragraphs" => [
                "De gebruiker kan een tegoedrekening aanmaken, hierna sleutel genoemd, het tegoed daarop opwaarderen en zo tokens kopen. Betaalmogelijkheden zijn onder andere creditcard en PayPal. Contante betaling per post naar het bovenstaande adres van MetaGer is ook mogelijk.",
                "Om een MetaGer-sleutel te gebruiken en tokens op te waarderen, moet de respectievelijke individuele sleutel eerst worden aangemaakt op de MetaGer-website.",
                "Afhankelijk van het gekozen pakket ontvangt de gebruiker precies de gekochte tokens voor gratis (onbeperkt) gebruik. De volgende aankoopopties zijn beschikbaar:",
                [
                    "500 tokens: 5 euro",
                    "1000 tokens: 10 euro",
                    "2000 tokens: 20 euro",
                    "3000 tokens: 30 euro",
                    "4000 tokens: 40 euro",
                    "6000 tokens: 60 euro",
                ],
                "Via marketingcampagnes met derden in het kader van partnercampagnes en klantenbindingsprogramma's kan de gebruiker ook sleutels ontvangen. In dit geval zijn deze AV en, indien van toepassing, de respectievelijke campagnevoorwaarden altijd van toepassing.",
            ],
        ],
        [
            "heading" => "Geldigheid en inwisseling van tokens",
            "paragraphs" => [
                "Tokens kunnen onbeperkt worden ingewisseld door elke gebruiker binnen het opgegeven geldigheidsinterval. De beschikbaarheid van de gekochte tokens en hoe vaak ze binnen een bepaalde periode kunnen worden ingewisseld, wordt aangegeven op de overzichtspagina in de sleutel.",
                "Vanaf de aankoop zijn de tokens twee kalenderjaren geldig. De geldigheidsdatum staat altijd vermeld op het overzicht. Na het verstrijken van de geldigheid vervalt ook de aanbieding.",
                "Nadat je een tokenpakket hebt gekocht, wordt het direct op de sleutel geladen.",
                "Alle herladingen en het hele proces van het aanmaken van de sleutel tot het inwisselen van het token zijn volledig anoniem. De enige uitzondering zijn de gegevens die nodig zijn voor het verwerken van de betaling.",
                "Als bewijs van de opwaardering heeft MetaGer het recht om het betalingsproces te controleren.",
                "De gebruiker is op geen enkel moment verplicht om zijn persoonlijke gegevens te verstrekken bij het opwaarderen van de sleutel. Alle informatie die hij in dit verband verstrekt, is vrijwillig. Bepaalde persoonlijke gegevens kunnen echter nodig zijn voor facturering en betalingsverwerking. Daarom moet de gebruiker alle informatie naar waarheid verstrekken.",
                "De aangekochte tokenpakketten en de resulterende tokens op een MetaGer-sleutel zijn niet overdraagbaar. De overdracht van de respectievelijke sleutel door de gebruiker wordt echter uitdrukkelijk toegestaan door MetaGer.",
            ],
        ],
        [
            "heading" => "Aansprakelijkheid",
            "paragraphs" => [
                "MetaGer is niet aansprakelijk voor schade die voortvloeit uit het gebruik van de service. MetaGer garandeert of aanvaardt geen enkele verantwoordelijkheid voor de juistheid, volledigheid, betrouwbaarheid, kwaliteit en tijdigheid van andere sites die voortvloeien uit het gebruik van de diensten.",
                "MetaGer biedt een online service.",
                "MetaGer biedt vrijwillig de mogelijkheid om de aankoopprijs van ongebruikte tokens terug te betalen, op voorwaarde dat de door de gebruiker gebruikte betaalmethode dit ondersteunt. Contante betalingstransacties zijn uitgesloten. De terugbetaling moet door de gebruiker worden aangevraagd binnen 30 dagen na voltooiing van het aankoopproces. Hiervoor moet de bijbehorende betalings-ID worden ingevoerd op de overzichtspagina.",
                "Tokens die verlopen zijn door het verstrijken van de tijd worden niet terugbetaald.",
                "MetaGer streeft er altijd naar om de functies zo beschikbaar mogelijk te houden. MetaGer aanvaardt geen garantie of aansprakelijkheid voor de beschikbaarheid van het internet of mobiele netwerk.",
                "MetaGer is alleen aansprakelijk voor opzet en grove nalatigheid. Deze en de bovenstaande aansprakelijkheidsbeperkingen zijn niet van toepassing op aansprakelijkheid voor persoonlijk letsel, aansprakelijkheid onder de Productaansprakelijkheidswet of aansprakelijkheid voor de schending van essentiële contractuele verplichtingen. Essentiële contractuele verplichtingen zijn verplichtingen die absoluut noodzakelijk zijn voor de goede uitvoering van een contract, zodat de verwezenlijking van het doel van het contract niet in gevaar komt, en op de naleving waarvan de klant regelmatig mag vertrouwen. Als een dergelijke essentiële contractuele verplichting opzettelijk wordt geschonden, is de aansprakelijkheid beperkt tot de typische contractuele en voorzienbare schade ten tijde van het sluiten van het contract.",
                "Alle beperkingen en uitsluitingen van aansprakelijkheid gelden dienovereenkomstig ook voor vertegenwoordigers, leidinggevende werknemers, organen en andere plaatsvervangende agenten en assistenten van MetaGer.",
                "De gebruiker verbindt zich ertoe de aangeboden diensten niet te gebruiken voor misbruik. Het is in het bijzonder onrechtmatig om persoonsgegevens van derden te verstrekken met als doel misleiding of het verkrijgen van voordelen.",
                "Als de gebruiker van plan is om de dienst buiten het gebruikelijke huishoudelijke bereik te gebruiken, moet dit informeel aan MetaGer worden gemeld, bij voorkeur via het contactformulier, aan het begin van dergelijk gebruik.",
            ],
        ],
        [
            "heading" => "Slotbepalingen",
            "paragraphs" => [
                "Het Duitse recht is van toepassing. De toepassing van het Verdrag der Verenigde Naties inzake internationale koopovereenkomsten betreffende roerende zaken is uitgesloten.",
                "Mochten afzonderlijke of meerdere bepalingen van deze Algemene Voorwaarden ongeldig zijn of worden, dan heeft dit geen invloed op de geldigheid van de overige bepalingen van deze AV. De partijen verplichten zich om ongeldige of nietige bepalingen te vervangen door nieuwe bepalingen die wettelijk voldoen aan de economische inhoud van de ongeldige of nietige bepalingen. Hetzelfde geldt als er een leemte in het contract ontstaat. Om de leemte op te vullen, verbinden de partijen zich ertoe te streven naar de vaststelling van passende bepalingen in dit contract die zo dicht mogelijk komen bij wat de partijen zouden hebben bepaald volgens de betekenis en het doel van dit contract als ze het punt hadden overwogen. Als er geen overeenstemming wordt bereikt, is de wet aanvullend van toepassing.",
            ],
        ],
    ],
];
