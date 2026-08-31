<?php

/**
 * Aufladen (/konto/aufladen) — App\Http\Controllers\ChargeController.
 *
 * `cash`, `consent` und micropayment.prepay sind wortgleich aus dem Keymanager
 * übernommen (dessen checkout.json unter payments.cash/payments.prepay/
 * micropayment, dessen order.json unter agb und revocation, je Sprache) —
 * dieselbe Kasse, jetzt hier gerendert. `page`, `manual`,
 * micropayment.lastschrift/directbanking (dort nur Logos, keine Texte),
 * `returned` und vrpayment.label/submit/error.failed sind neu; vrpayment.privacy
 * ist wortgleich aus dem Keymanager übernommen wie cash/consent/micropayment.
 */
return [
    'page' => [
        'change' => 'Aantal wijzigen',
        'methods' => [
            'heading' => 'Betaalmethode kiezen',
            'more' => 'Meer betaalmethoden',
            'back' => 'Kies een andere betaalmethode',
        ],
        'cancel' => 'Terug naar account',
    ],

    'cash' => [
        'label' => 'Contant',
        'description' => 'Je kunt je sleutel ook contant opladen. Stuur ons hiervoor het volgende bestelnummer per post samen met het gewenste geldbedrag. Houd er rekening mee dat het bestelnummer leesbaar moet zijn om door ons verwerkt te kunnen worden.',
        'note' => 'Houd rekening met het volgende:',
        'no_large_values' => 'Stuur ons voor je eigen veiligheid niet meer dan € 100 per post. Wij zijn niet aansprakelijk voor de transportroute. Je bent er zelf verantwoordelijk voor dat de brief ons bereikt.',
        'no_coins' => 'We accepteren alleen bankbiljetten. Stuur geen munten!',
        'accepted_currencies' => 'We accepteren alleen de volgende valuta: EUR, USD, CAD, GBP.',
        'currency_translation' => 'We rekenen altijd bedragen in EUR. Als je ons een andere valuta stuurt, wordt het verzonden bedrag omgerekend tegen de dagelijkse wisselkoers',
        'no_refund' => 'Vanwege de geldende wetgeving op het gebied van witwaspraktijken is restitutie of retournering helaas niet mogelijk. Zodra de betaling door ons is verzonden, kun je echter onder "Bestellingen" het ID van de verzonden betaling invoeren om een overzicht van de bestelling te krijgen en/of een factuur aan te vragen.',
        'generate' => 'ID betaling genereren',
        'error' => [
            'unreachable' => 'Er is iets misgegaan bij het aanmaken van uw bestelling. Probeer het later nog eens.',
        ],
        'order' => [
            'heading' => 'Je betalings-ID',
            'copy' => 'Kopie betalings ID',
            'address_heading' => 'Stuur de brief naar het volgende adres en noteer de betalings-ID voor je eigen administratie',
            'address' => 'SUMA-EV
Postfach 51 01 43
30631 Hannover
Duitsland',
            'expiration' => 'De betaal-ID is geldig tot :date. Na deze datum kan het niet meer worden gebruikt voor een herbetaling.',
            'unique' => 'Gebruik de betaal-ID alleen voor een enkele herlading. Je ontvangt elke keer dat je deze pagina bezoekt een nieuwe!',
        ],
    ],

    'consent' => [
        'agb' => 'Door verder te gaan met je aankoop, ga je akkoord met onze <a href=":agblink" target="_blank">Algemene voorwaarden</a>.',
        'label' => 'Ik ga uitdrukkelijk akkoord met de uitvoering van het contract voordat de herroepingstermijn is verstreken. Ik begrijp dat het <a href=":revocation_link" target="_blank">herroepingsrecht</a> vervalt bij aanvang van de uitvoering van het contract. In plaats daarvan verlenen wij u een vrijwillig <a href=":refundlink" target="_blank">herroepingsrecht van 30 dagen</a>.',
        'error' => 'Dit veld is verplicht',
    ],

    'manual' => [
        'label' => 'Handmatig (dev)',
        'description' => 'Sla een daadwerkelijke betaling over. Alleen beschikbaar in een ontwikkelomgeving.',
        'submit' => 'Betaling voltooien',
    ],

    'micropayment' => [
        'label' => 'Micropayment',
        'prepay' => [
            'label' => 'Overschrijving',
            'email' => [
                'label' => 'E-mailadres',
                'description' => 'Naar dit adres wordt eenmalig informatie gestuurd over onze bankgegevens en een melding wanneer de betaling is voltooid.',
            ],
        ],
        'lastschrift' => ['label' => 'Automatische incasso'],
        'directbanking' => ['label' => 'Directe bankoverschrijving'],
        'submit' => 'Betalen',
        'privacy' => 'Door op "Betaling verrichten" te klikken, wordt u doorgestuurd naar onze betalingsdienstaanbieder <a href="https://micropayment.de" target="_blank">MicroPayment</a> om de aankoop te voltooien. Meer over <a href=":link" target="_blank">privacy op :link_text</a>.',
    ],

    'vrpayment' => [
        'label' => 'VR Betaling',
        'submit' => 'Betalen',
        'privacy' => 'Door op "Betaling verrichten" te klikken, wordt u doorgestuurd naar onze betalingsdienstaanbieder <a href="https://www.vr-payment.de" target="_blank">VR Payment</a> om de aankoop te voltooien. Meer over <a href=":link" target="_blank">privacy bij VR Payment</a>.',
        'error' => [
            'failed' => 'VR Payment heeft deze betaling geweigerd. Probeer het opnieuw of kies een andere betaalmethode.',
        ],
    ],

    'returned' => [
        'heading' => 'Opwaarderen voltooid',
        'paid' => 'Bedankt! Uw sleutel is opgewaardeerd met :amount tokens.',
        'pending' => 'Uw betaling wordt nog verwerkt. Zodra deze bij ons binnenkomt, wordt uw sleutel automatisch opgewaardeerd.',
    ],
];
