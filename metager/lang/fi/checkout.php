<?php

/**
 * Aufladen (/konto/aufladen) — App\Http\Controllers\ChargeController.
 *
 * Bezahlt wird bei suma-payments: hier stehen nur noch die Zustimmung
 * (`suma`, `consent`), die Schrittleiste und Kurzfassung (`page`) und die
 * Rückkehrseite (`returned`). Die Texte der eigenen Seiten für Bar, Wero,
 * micropayment und PayPal sind mit ihnen gegangen (suma-crm
 * docs/keymanager-migration-plan.md, Schritt 7).
 */
return [
    'page' => [
        'change' => 'Muuta määrää',
        'cancel' => 'Takaisin tilille',
        'steps' => [
            'aria_two' => 'Lataus kahdessa vaiheessa',
            'amount' => 'Määrä',
            'pay' => 'Maksa',
        ],
        'summary' => [
            'buying' => 'Ostat',
            'after' => 'Saldo tämän jälkeen',
            'price' => 'Hinta',
        ],
    ],
    'consent' => [
        'agb' => 'Jatkaessasi ostamista hyväksyt <a href=":agblink" target="_blank">käyttöehdot</a>.',
        'label' => 'Suostun nimenomaisesti siihen, että sopimus pannaan täytäntöön ennen peruuttamisajan päättymistä. Ymmärrän, että <a href=":revocation_link" target="_blank">peruuttamisoikeus</a> päättyy, kun sopimuksen toteuttaminen aloitetaan. Sen sijaan myönnämme sinulle vapaaehtoisen <a href=":refundlink" target="_blank">30 päivän palautusoikeuden</a>.',
        'error' => 'Tämä kenttä on pakollinen',
    ],
    'suma' => [
        'label' => 'Tilisiirto, suoraveloitus ja muuta',
        'heading' => 'Maksa',
        'lede' => 'Seuraavalla sivulla valitset, miten maksat: :methods. Voit maksaa nimettömänä – nimi ja sähköposti ovat vapaaehtoisia, paitsi suoraveloituksessa.',
        'submit' => 'Jatka maksamaan',
        'privacy' => 'Maksu kulkee SUMA-EV:n maksualueen (payments.suma-ev.de) kautta. Tilisiirron, SEPA-suoraveloituksen ja käteisen postitse käsittelemme itse – ulkopuolinen maksupalvelu on mukana vain kortti- ja Wero-maksuissa (VR Payment) sekä PayPalissa.',
        'methods' => [
            'banktransfer' => 'Tilisiirto',
            'sepa_directdebit' => 'SEPA-suoraveloitus',
            'card' => 'Luotto-/pankkikortti',
            'wero' => 'Wero',
            'paypal' => 'PayPal',
            'cash' => 'Käteinen postitse',
        ],
        'error' => [
            'unreachable' => 'Jokin meni pieleen tilausta luodessasi. Yritä myöhemmin uudelleen.',
        ],
    ],
    'returned' => [
        'heading' => 'Lataus valmis',
        'pending_heading' => 'Maksua käsitellään',
        'paid' => 'Kiitos! Avaimesi on ladattu :amount tokenilla.',
        'next' => 'Saldosi on heti käytettävissä – voit jatkaa hakua nyt.',
        'details' => 'Näytä tilauksen tiedot',
        'pending' => 'Maksuasi käsitellään vielä. Heti kun se saapuu meille, avaimesi ladataan automaattisesti.',
    ],
];
