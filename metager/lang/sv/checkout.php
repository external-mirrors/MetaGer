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
        'change' => 'Ändra mängd',
        'methods' => [
            'heading' => 'Välj betalningsmetod',
            'more' => 'Fler betalningsmetoder',
            'back' => 'Välj en annan betalningsmetod',
            'cash_note' => 'Anonymt',
        ],
        'cancel' => 'Tillbaka till kontot',
    ],

    'cash' => [
        'label' => 'Kontanter',
        'description' => 'Du kan också ladda din nyckel mot kontanter. Skicka då följande ordernummer till oss per post tillsammans med önskad summa pengar. Observera att ordernumret måste vara läsbart för att vi ska kunna behandla det.',
        'note' => 'Vänligen observera följande:',
        'no_large_values' => 'För din egen säkerhets skull bör du inte skicka mer än 100 euro per post till oss. Vi tar inget ansvar för transportvägen. Du ansvarar själv för att brevet kommer fram till oss.',
        'no_coins' => 'Vi tar endast emot sedlar. Skicka inte mynt!',
        'accepted_currencies' => 'Vi accepterar endast följande valutor: EUR, USD, CAD, GBP.',
        'currency_translation' => 'Vi debiterar alltid belopp i EUR. Om du skickar oss en annan valuta, kommer det skickade beloppet att konverteras till den dagliga växelkursen',
        'no_refund' => 'På grund av gällande lagar om penningtvätt är en återbetalning eller retur tyvärr inte möjlig. När avgiften har bokförts av oss kan du dock ange det skickade betalnings-ID:t under "Beställningar" för att få en orderöversikt och/eller begära en faktura.',
        'generate' => 'Skapa betalnings-ID',
        'error' => [
            'unreachable' => 'Något gick fel när vi skapade din beställning. Vänligen försök igen senare.',
        ],
        'order' => [
            'heading' => 'Ditt betalnings-ID',
            'copy' => 'Kopia av betalnings-ID',
            'address_heading' => 'Skicka brevet till följande adress och notera betalningsnumret för dina egna handlingar',
            'address' => 'SUMA-EV
Postfach 51 01 43
30631 Hannover
Tyskland',
            'expiration' => 'Betalnings-ID:t är giltigt fram till :date. Efter detta datum kan det inte längre användas för en laddning.',
            'unique' => 'Använd betalnings-ID endast för en enda laddning. Du kommer att få ett nytt varje gång du besöker denna sida!',
        ],
    ],

    'consent' => [
        'agb' => 'Genom att fortsätta ditt köp godkänner du våra <a href=":agblink" target="_blank">Villkor och bestämmelser</a>.',
        'label' => 'Jag samtycker uttryckligen till att avtalet genomförs innan ångerfristen löpt ut. Jag förstår att <a href=":revocation_link" target="_blank">rätten till återkallelse</a> löper ut när genomförandet av avtalet påbörjas. Istället ger vi dig en frivillig <a href=":refundlink" target="_blank">30-dagars returrätt</a>.',
        'error' => 'Detta fält är obligatoriskt',
    ],

    'manual' => [
        'label' => 'Manuell (dev)',
        'description' => 'Hoppa över en verklig betalning. Endast tillgängligt i en utvecklingsmiljö.',
        'submit' => 'Slutför betalning',
    ],

    'micropayment' => [
        'prepay' => [
            'label' => 'Banköverföring',
            'email' => [
                'label' => 'E-postadress',
                'description' => 'Till denna adress kommer du att få engångsinformation om våra bankuppgifter och ett meddelande när betalningen är slutförd.',
            ],
        ],
        'lastschrift' => ['label' => 'Autogiro'],
        'directbanking' => ['label' => 'Direktbanköverföring'],
        'submit' => 'Göra betalning',
        'privacy' => 'Genom att klicka på "Gör betalning" kommer du att omdirigeras till vår betaltjänstleverantör <a href="https://micropayment.de" target="_blank">MicroPayment</a> för att slutföra köpet. Mer om <a href=":link" target="_blank">integritet på :link_text</a>.',
    ],

    'vrpayment' => [
        'label' => 'Wero',
        'submit' => 'Göra betalning',
        'privacy' => 'Genom att klicka på "Gör betalning" kommer du att omdirigeras till vår betaltjänstleverantör <a href="https://www.vr-payment.de" target="_blank">VR Payment</a> för att slutföra köpet. Mer om <a href=":link" target="_blank">integritet på VR Payment</a>.',
        'error' => [
            'failed' => 'VR Payment avvisade denna betalning. Försök igen eller välj en annan betalningsmetod.',
            'onion' => 'Wero är inte tillgängligt via vår onion-adress – betalningsleverantören kan inte skicka tillbaka dig hit efteråt. Välj en annan betalningsmetod.',
        ],
    ],

    'paypal' => [
        'heading' => 'Göra betalning',
        'submit' => 'Göra betalning',
        'loading' => 'Betalningsmetod är laddad',
        'cancel' => 'Betalningsprocessen avbröts. Om din betalning gick igenom innan du avbröt, kommer din beställning att behandlas så snart betalningen bekräftas av betalningsprocessorn. I annat fall ber vi dig att försöka igen.',
        'privacy' => 'Betalningsmetoder i denna grupp kräver vanligtvis inte ett PayPal-konto, men behandlas där. Mer om <a href="https://www.paypal.com/us/legalhub/privacy-full" target="_blank">integritet på PayPal</a>.',
        'noscript' => 'Denna betalningsmetod kräver JavaScript. Välj en annan betalningsmetod eller aktivera JavaScript.',
        'funding' => [
            'paypal' => 'PayPal',
            'card' => 'Kredit- eller betalkort',
            'p24' => 'Przelewy24',
            'bancontact' => 'Bancontact',
            'blik' => 'BLIK',
            'eps' => 'EPS',
            'mybank' => 'MyBank',
        ],
        'error' => [
            'not_available' => 'Tyvärr är den valda betalningsmetoden inte tillgänglig i din region.',
            'generic' => 'Betalningsprocessen avbröts på grund av ett fel.  Om din betalning gick igenom innan du avbröt kommer din beställning att behandlas så snart betalningen har bekräftats av betalningsprocessorn. Annars ber vi dig att försöka igen.',
        ],
        'card' => [
            'label' => 'Kredit- eller betalkort',
            'name' => 'Kortinnehavarens namn (valfritt)',
            'number' => 'Kortnummer',
            'expiration' => 'Giltig till',
            'cvv' => 'CVV',
            'error' => [
                '9500' => 'Kreditkortet avvisas som bedrägligt',
                '5100' => 'Kreditkortet har nekats av kreditinstitutet',
                '00N7' => 'Fel CVV. Vänligen kontrollera inmatningen',
                '5400' => 'Kreditkortet har gått ut',
                '5180' => 'Luhn check misslyckades',
                '5120' => 'Kreditkortet nekades på grund av otillräckliga medel.',
                '9520' => 'Kreditkort avvisat som förlorat/stulet',
                '0500' => 'Kreditkort nekat av kreditinstitut',
                '1330' => 'Kreditkortet är ogiltigt. Vänligen kontrollera din post',
                '3ds' => '3D-autentisering misslyckades',
                'generic' => 'Kreditkort nekat av kreditinstitut',
            ],
        ],
    ],
    'returned' => [
        'heading' => 'Uppladdning slutförd',
        'paid' => 'Tack! Din nyckel har laddats med :amount tokens.',
        'pending' => 'Din betalning behandlas fortfarande. Så snart den når oss laddas din nyckel automatiskt.',
    ],
];
