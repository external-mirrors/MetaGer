<?php

/**
 * Aufladen (/konto/aufladen) — App\Http\Controllers\ChargeController.
 *
 * `cash`, `consent` und micropayment.prepay sind wortgleich aus dem Keymanager
 * übernommen (dessen checkout.json unter payments.cash/payments.prepay/
 * micropayment, dessen order.json unter agb und revocation, je Sprache) —
 * dieselbe Kasse, jetzt hier gerendert. `page`, `manual`,
 * micropayment.lastschrift/directbanking (dort nur Logos, keine Texte) und
 * `returned` sind neu.
 */
return [
    'page' => [
        'change' => 'Ändra mängd',
        'methods' => [
            'heading' => 'Välj betalningsmetod',
            'more' => 'Fler betalningsmetoder',
            'back' => 'Välj en annan betalningsmetod',
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
        'label' => 'Micropayment',
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

    'returned' => [
        'heading' => 'Uppladdning slutförd',
        'paid' => 'Tack! Din nyckel har laddats med :amount tokens.',
        'pending' => 'Din betalning behandlas fortfarande. Så snart den når oss laddas din nyckel automatiskt.',
    ],
];
