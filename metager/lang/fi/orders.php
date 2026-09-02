<?php

return [
    'lookup' => [
        'heading' => 'Hae tilaus',
        'description' => 'Kirjoita jonkin tilauksesi maksutunnus nähdäksesi sen tiedot.',
        'placeholder' => 'Maksutunnus',
        'submit' => 'Näytä tilaus',
        'error' => [
            'invalid' => 'Tämä ei ole kelvollinen maksutunnus.',
            'not_found' => 'Avaimellasi ei ole tilausta, joka vastaisi tätä maksutunnusta.',
        ],
    ],

    'show' => [
        'heading' => 'Tilaus :reference',
        'breadcrumb' => 'Tilaukset',
        'thanks' => 'Kiitos ostoksestasi!',
        'pending' => 'Tokenisi hyvitetään heti, kun maksusi on saapunut meille. Saat vahvistussähköpostin, kun näin on tapahtunut.',
        'lookup_hint' => 'Voit avata tämän yhteenvedon uudelleen milloin tahansa syöttämällä maksutunnuksesi (:reference).',
        'order_line' => 'Tilaus :id, :date',
        'item' => 'MetaGer-avain: tokenit',
        'count' => 'Määrä',
        'price' => 'Hinta',
        'vat' => 'ALV (:rate %)',
        'total' => 'Kokonaismäärä',
        'exchange_rate' => 'Valuuttakurssi',
        'download_confirmation' => 'Lataa tilausvahvistus',
        'request_invoice' => 'Luo lasku',
        'request_refund' => 'Pyydä palautusta',
    ],

    'invoice' => [
        'heading' => 'Lasku',
        'breadcrumb' => 'Tilaus :reference',
        'description' => 'Jos tarvitset laskun, anna laskutustietosi alla olevalla lomakkeella.',
        'ready' => 'Tälle tilaukselle on jo luotu lasku.',
        'download' => 'Lataa lasku',
        'submit' => 'Luo lasku',
        'storage' => 'Meillä on lakisääteinen velvollisuus säilyttää kerran laaditut laskut <span class="bold">10 vuotta</span>. Koska lasku on osoitettava sinulle henkilökohtaisesti, se sisältää välttämättä henkilötietoja (nimi, osoite).',
        'error' => [
            'invalid' => 'Tarkista tietosi — joitakin pakollisia kenttiä puuttuu tai ne ovat liian pitkiä.',
        ],
        'field' => [
            'company' => 'Yrityksen nimi (vapaaehtoinen)',
            'first_name' => 'Etunimi',
            'last_name' => 'Sukunimi',
            'address1' => 'Osoite 1',
            'address2' => 'Osoite 2 (valinnainen)',
            'zip' => 'Postinumero',
            'city' => 'Kaupunki',
            'state' => 'Valtio (valinnainen)',
        ],
    ],

    'refund' => [
        'heading' => 'Palautus',
        'breadcrumb' => 'Tilaus :reference',
        'unavailable' => 'Tälle tilaukselle ei ole enää hyvitettävää saldoa — joko hyvitystä on jo pyydetty, tai käytetty maksutapa ei tue hyvityspyyntöä tämän lomakkeen kautta.',
        'description' => 'Oletko tyytymätön avaimeesi? Olemme hyvin pahoillamme siitä! Tietenkin hyvitämme laskun summan tässä tapauksessa. Palautus tehdään aina samalle tilille, jota käytettiin alkuperäisessä maksussa. Otamme mielellämme vastaan myös kritiikkisi.',
        'partial_note' => 'Huomaa: Osa ostamastasi hyvityksestä on jo käytetty. Siksi voimme hyvittää sinulle vain <span class="bold">:count</span>/<span class="bold">:total</span> hakua.',
        'message' => [
            'label' => 'Viestisi (vapaaehtoinen)',
        ],
        'submit' => ':amount € Pyydä palautusta',
        'error' => [
            'not_allowed' => 'Tälle tilaukselle ei ole enää mahdollista saada hyvitystä.',
            'unreachable' => 'Virhe viestin lähettämisessä. Yritä myöhemmin uudelleen.',
        ],
    ],
];
