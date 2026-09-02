<?php

return [
    'lookup' => [
        'heading' => 'Een bestelling opzoeken',
        'description' => 'Voer de betalings-ID van een van uw bestellingen in om de details te bekijken.',
        'placeholder' => 'Betalings-ID',
        'submit' => 'Bestelling tonen',
        'error' => [
            'invalid' => 'Dit is geen geldige betalings-ID.',
            'not_found' => 'Geen bestelling op uw sleutel komt overeen met die betalings-ID.',
        ],
    ],

    'show' => [
        'heading' => 'Bestelling :reference',
        'breadcrumb' => 'Bestellingen',
        'thanks' => 'Bedankt voor uw aankoop!',
        'pending' => 'Uw tokens worden bijgeschreven zodra uw betaling bij ons is ontvangen. U ontvangt dan een bevestigingsmail.',
        'lookup_hint' => 'U kunt dit overzicht op elk moment opnieuw openen door uw betalings-ID (:reference) in te voeren.',
        'order_line' => 'Bestelling :id van :date',
        'item' => 'MetaGer-sleutel: tokens',
        'count' => 'Hoeveelheid',
        'price' => 'Prijs',
        'vat' => 'btw (:rate %)',
        'total' => 'Totaalbedrag',
        'exchange_rate' => 'Wisselkoers',
        'download_confirmation' => 'Orderbevestiging downloaden',
        'request_invoice' => 'Factuur maken',
        'request_refund' => 'Terugbetaling aanvragen',
    ],

    'invoice' => [
        'heading' => 'Factuur',
        'breadcrumb' => 'Bestelling :reference',
        'description' => 'Als u een factuur nodig heeft, vul dan uw factuurgegevens in op het onderstaande formulier.',
        'ready' => 'Er bestaat al een factuur voor deze bestelling.',
        'download' => 'Factuur downloaden',
        'submit' => 'Factuur maken',
        'storage' => 'We zijn wettelijk verplicht om eenmaal uitgereikte facturen <span class="bold">10 jaar</span> lang te bewaren. Aangezien een factuur persoonlijk aan u moet worden uitgereikt, bevat deze noodzakelijkerwijs persoonlijke gegevens (naam, adres).',
        'error' => [
            'invalid' => 'Controleer uw gegevens — sommige verplichte velden ontbreken of zijn te lang.',
        ],
        'field' => [
            'company' => 'Bedrijfsnaam (optioneel)',
            'first_name' => 'Voornaam',
            'last_name' => 'Achternaam',
            'address1' => 'Adres 1',
            'address2' => 'Adres 2 (optioneel)',
            'zip' => 'Postcode',
            'city' => 'Stad',
            'state' => 'Staat (optioneel)',
        ],
    ],

    'refund' => [
        'heading' => 'Terugbetaling',
        'breadcrumb' => 'Bestelling :reference',
        'unavailable' => 'Er is geen terugbetaalbaar tegoed meer voor deze bestelling — er is al een terugbetaling aangevraagd, of de gebruikte betaalmethode ondersteunt geen terugbetalingsverzoek via dit formulier.',
        'description' => 'Bent u niet tevreden met uw sleutel? Dat vinden wij erg jammer om te horen! Uiteraard betalen wij in dat geval het factuurbedrag terug. Een terugbetaling wordt altijd gedaan naar dezelfde rekening die voor de oorspronkelijke betaling is gebruikt. Ook uw kritiek ontvangen wij graag.',
        'partial_note' => 'Let op: een deel van uw gekochte tegoed is al gebruikt. Daarom kunnen wij u slechts <span class="bold">:count</span> van <span class="bold">:total</span> zoekopdrachten terugbetalen.',
        'message' => [
            'label' => 'Uw bericht (optioneel)',
        ],
        'submit' => ':amount € Terugbetaling aanvragen',
        'error' => [
            'not_allowed' => 'Een terugbetaling is niet meer mogelijk voor deze bestelling.',
            'unreachable' => 'Fout bij het verzenden van uw bericht. Probeer het later nog eens.',
        ],
    ],
];
