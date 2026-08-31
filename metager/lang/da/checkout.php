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
        'change' => 'Skift beløb',
        'methods' => [
            'heading' => 'Vælg betalingsmetode',
            'more' => 'Flere betalingsmetoder',
            'back' => 'Vælg en anden betalingsmetode',
        ],
        'cancel' => 'Tilbage til kontoen',
    ],

    'cash' => [
        'label' => 'Kontanter',
        'description' => 'Du kan også opkræve kontanter for din nøgle. For at gøre det skal du blot sende os følgende ordrenummer via mail sammen med det ønskede beløb. Bemærk venligst, at ordrenummeret skal være læseligt for at kunne behandles af os.',
        'note' => 'Bemærk venligst følgende:',
        'no_large_values' => 'For din egen sikkerheds skyld må du ikke sende os mere end 100 € med posten. Vi påtager os ikke noget ansvar for transportruten. Du er selv ansvarlig for, at brevet når frem til os.',
        'no_coins' => 'Vi accepterer kun pengesedler. Send ikke mønter!',
        'accepted_currencies' => 'Vi accepterer kun følgende valutaer: EUR, USD, CAD, GBP.',
        'currency_translation' => 'Vi opkræver altid beløb i EUR. Hvis du sender os en anden valuta, omregnes det sendte beløb til dagskursen.',
        'no_refund' => 'På grund af gældende love om hvidvaskning af penge er det desværre ikke muligt at refundere eller returnere. Men når vi har bogført opkrævningen, kan du indtaste det sendte betalings-ID under "Ordrer" for at få en ordreoversigt og/eller anmode om en faktura.',
        'generate' => 'Generer betalings-ID',
        'error' => [
            'unreachable' => 'Noget gik galt under oprettelsen af din ordre. Prøv venligst igen senere.',
        ],
        'order' => [
            'heading' => 'Dit betalings-ID',
            'copy' => 'Kopi af betalings-ID',
            'address_heading' => 'Send brevet til følgende adresse, og noter betalingsnummeret til din egen dokumentation',
            'address' => 'SUMA-EV
Postfach 51 01 43
30631 Hannover
Tyskland',
            'expiration' => 'BetalingsID\'et er gyldigt indtil :date. Efter denne dato kan det ikke længere bruges til en opladning.',
            'unique' => 'Brug kun betalings-ID\'et til en enkelt opladning. Du vil modtage et nyt, hver gang du besøger denne side!',
        ],
    ],

    'consent' => [
        'agb' => 'Ved at fortsætte dit køb accepterer du vores <a href=":agblink" target="_blank">Vilkår og betingelser</a>.',
        'label' => 'Jeg accepterer udtrykkeligt udførelsen af kontrakten inden udløbet af tilbagekaldelsesperioden. Jeg forstår, at <a href=":revocation_link" target="_blank">fortrydelsesret</a> udløber ved påbegyndelse af udførelsen af kontrakten. I stedet giver vi dig en frivillig <a href=":refundlink" target="_blank">30-dages returret</a>.',
        'error' => 'Dette felt er påkrævet',
    ],

    'manual' => [
        'label' => 'Manuel (dev)',
        'description' => 'Spring en faktisk betaling over. Kun tilgængelig i et udviklingsmiljø.',
        'submit' => 'Gennemfør betaling',
    ],

    'micropayment' => [
        'label' => 'Micropayment',
        'prepay' => [
            'label' => 'Bankoverførsel',
            'email' => [
                'label' => 'E-mail-adresse',
                'description' => 'Til denne adresse vil du få tilsendt engangsoplysninger om vores bankoplysninger og en meddelelse, når betalingen er gennemført.',
            ],
        ],
        'lastschrift' => ['label' => 'Direkte debitering'],
        'directbanking' => ['label' => 'Øjeblikkelig bankoverførsel'],
        'submit' => 'Foretag betaling',
        'privacy' => 'Ved at klikke på "Foretag betaling" vil du blive omdirigeret til vores betalingstjenesteudbyder <a href="https://micropayment.de" target="_blank">MicroPayment</a> for at gennemføre købet. Mere om <a href=":link" target="_blank">privatliv på :link_text</a>.',
    ],

    'vrpayment' => [
        'label' => 'VR-betaling',
        'submit' => 'Foretag betaling',
        'privacy' => 'Ved at klikke på "Foretag betaling" vil du blive omdirigeret til vores betalingstjenesteudbyder <a href="https://www.vr-payment.de" target="_blank">VR Payment</a> for at gennemføre købet. Mere om <a href=":link" target="_blank">privatliv på VR Payment</a>.',
        'error' => [
            'failed' => 'VR Payment afviste denne betaling. Prøv igen, eller vælg en anden betalingsmetode.',
        ],
    ],

    'paypal' => [
        'label' => 'PayPal',
        'heading' => 'Foretag betaling',
        'submit' => 'Foretag betaling',
        'loading' => 'Betalingsmetode er indlæst',
        'cancel' => 'Betalingsprocessen blev annulleret. Hvis din betaling gik igennem, før du annullerede, vil din ordre blive behandlet, så snart betalingen er bekræftet af betalingsformidleren. Ellers bedes du prøve igen.',
        'privacy' => 'Betalingsmetoder i denne gruppe kræver normalt ikke en PayPal-konto, men behandles der. Mere om <a href="https://www.paypal.com/us/legalhub/privacy-full" target="_blank">privatliv på PayPal</a>.',
        'noscript' => 'Denne betalingsmetode kræver JavaScript. Vælg venligst en anden betalingsmetode, eller aktiver JavaScript.',
        'funding' => [
            'paypal' => 'PayPal',
            'card' => 'Kredit-/debetkort',
            'p24' => 'Przelewy24',
            'bancontact' => 'Bancontact',
            'blik' => 'BLIK',
            'eps' => 'EPS',
            'mybank' => 'MyBank',
        ],
        'error' => [
            'not_available' => 'Beklager, den valgte betalingsmetode er ikke tilgængelig i din region.',
            'generic' => 'Betalingsprocessen blev annulleret på grund af en fejl.  Hvis din betaling gik igennem, før du annullerede, vil din ordre blive behandlet, så snart betalingen er bekræftet af betalingsformidleren. Ellers bedes du prøve igen.',
        ],
        'card' => [
            'label' => 'Kredit-/debetkort',
            'name' => 'Kortholders navn (valgfrit)',
            'number' => 'Kortnummer',
            'expiration' => 'Gyldig indtil',
            'cvv' => 'CVV',
            'error' => [
                '9500' => 'Kreditkort afvist som svigagtigt',
                '5100' => 'Kreditkortet blev afvist af kreditinstituttet.',
                '00N7' => 'Forkert CVV. Kontroller venligst input',
                '5400' => 'Kreditkortet er udløbet',
                '5180' => 'Luhn-tjekket mislykkedes',
                '5120' => 'Kreditkort afvist på grund af utilstrækkelige midler.',
                '9520' => 'Kreditkort afvist som tabt/stjålet',
                '0500' => 'Kreditkort afvist af kreditinstitut',
                '1330' => 'Kreditkortet er ugyldigt. Tjek venligst din tilmelding',
                '3ds' => '3D-godkendelse mislykkedes',
                'generic' => 'Kreditkort afvist af kreditinstitut',
            ],
        ],
    ],
    'returned' => [
        'heading' => 'Opladning gennemført',
        'paid' => 'Tak! Din nøgle er blevet opladet med :amount tokens.',
        'pending' => 'Din betaling behandles stadig. Så snart den når frem til os, bliver din nøgle automatisk opladet.',
    ],
];
