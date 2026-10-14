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
        'documents' => 'Kuitti ja lasku',
        'request_refund' => 'Pyydä palautusta',
    ],


    'refund' => [
        'heading' => 'Palautus',
        'breadcrumb' => 'Tilaus :reference',
        'unavailable' => 'Tälle tilaukselle ei ole enää hyvitettävää saldoa — joko hyvitystä on jo pyydetty, tai käytetty maksutapa ei tue hyvityspyyntöä tämän lomakkeen kautta.',
        'success' => 'Pyyntösi on lähetetty meille onnistuneesti. Käsittelemme sen mahdollisimman pian. Maksutavasta riippuen voi kestää muutaman päivän ennen kuin hyvitys näkyy myynnissäsi.',
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
