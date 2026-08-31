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
        'change' => 'Muuta määrää',
        'methods' => [
            'heading' => 'Valitse maksutapa',
            'more' => 'Muut maksutavat',
            'back' => 'Valitse toinen maksutapa',
        ],
        'cancel' => 'Takaisin tilille',
    ],

    'cash' => [
        'label' => 'Käteinen',
        'description' => 'Voit myös ladata avaimesi käteisellä. Lähetä meille postitse seuraava tilausnumero ja haluamasi rahasumma. Huomaa, että tilausnumeron on oltava luettavissa, jotta voimme käsitellä sen.',
        'note' => 'Huomaa seuraavat seikat:',
        'no_large_values' => 'Oman turvallisuutesi vuoksi älä lähetä meille postitse yli 100€. Emme ota mitään vastuuta kuljetusreitistä. Olet itse vastuussa siitä, että kirje saapuu meille.',
        'no_coins' => 'Hyväksymme vain seteleitä. Älä lähetä kolikoita!',
        'accepted_currencies' => 'Hyväksymme vain seuraavat valuutat: EUR, USD, CAD, GBP.',
        'currency_translation' => 'Veloitamme aina euroina. Jos lähetät meille jonkin muun valuutan, lähetetty summa muunnetaan päivittäisen valuuttakurssin mukaan.',
        'no_refund' => 'Sovellettavien rahanpesulakien vuoksi palautus tai palautus ei valitettavasti ole mahdollista. Kun olemme kuitenkin lähettäneet maksun, voit syöttää lähetetyn maksutunnuksen "Tilaukset"-kohdassa saadaksesi yleiskatsauksen tilauksesta ja/tai pyytää laskun.',
        'generate' => 'Luo maksutunnus',
        'error' => [
            'unreachable' => 'Jokin meni pieleen tilausta luodessasi. Yritä myöhemmin uudelleen.',
        ],
        'order' => [
            'heading' => 'Maksutunnuksesi',
            'copy' => 'Kopioi maksutunnus',
            'address_heading' => 'Lähetä kirje seuraavaan osoitteeseen ja merkitse maksun tunniste muistiin omia tietojasi varten.',
            'address' => 'SUMA-EV
Postfach 51 01 43
30631 Hannover
Saksa',
            'expiration' => 'Maksutunnus on voimassa osoitteeseen :date asti. Tämän päivämäärän jälkeen sitä ei voi enää käyttää lataukseen.',
            'unique' => 'Käytä maksutunnusta vain yhtä latausta varten. Saat uuden tunnuksen joka kerta, kun käyt tällä sivulla!',
        ],
    ],

    'consent' => [
        'agb' => 'Jatkaessasi ostamista hyväksyt <a href=":agblink" target="_blank">käyttöehdot</a>.',
        'label' => 'Suostun nimenomaisesti siihen, että sopimus pannaan täytäntöön ennen peruuttamisajan päättymistä. Ymmärrän, että <a href=":revocation_link" target="_blank">peruuttamisoikeus</a> päättyy, kun sopimuksen toteuttaminen aloitetaan. Sen sijaan myönnämme sinulle vapaaehtoisen <a href=":refundlink" target="_blank">30 päivän palautusoikeuden</a>.',
        'error' => 'Tämä kenttä on pakollinen',
    ],

    'manual' => [
        'label' => 'Manuaalinen (dev)',
        'description' => 'Ohita todellinen maksu. Käytettävissä vain kehitysympäristössä.',
        'submit' => 'Viimeistele maksu',
    ],

    'micropayment' => [
        'label' => 'Micropayment',
        'prepay' => [
            'label' => 'Pankkisiirto',
            'email' => [
                'label' => 'Sähköpostiosoite',
                'description' => 'Tähän osoitteeseen lähetetään kertaluonteisesti tiedot pankkitiedoistamme ja ilmoitus, kun maksu on suoritettu.',
            ],
        ],
        'lastschrift' => ['label' => 'Suoraveloitus'],
        'directbanking' => ['label' => 'Pikapankkisiirto'],
        'submit' => 'Suorita maksu',
        'privacy' => 'Klikkaamalla "Suorita maksu" sinut ohjataan maksupalveluntarjoajallemme <a href="https://micropayment.de" target="_blank">MicroPayment</a> ostoksen suorittamista varten. Lisätietoja <a href=":link" target="_blank">tietosuojasta osoitteessa :link_text</a>.',
    ],

    'returned' => [
        'heading' => 'Lataus valmis',
        'paid' => 'Kiitos! Avaimesi on ladattu :amount tokenilla.',
        'pending' => 'Maksuasi käsitellään vielä. Heti kun se saapuu meille, avaimesi ladataan automaattisesti.',
    ],
];
