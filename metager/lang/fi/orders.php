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
    ],
];
