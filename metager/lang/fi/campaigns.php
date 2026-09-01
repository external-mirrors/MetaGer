<?php

return [
    'heading' => 'Lahjakorttikampanjat',
    'description' => 'Jaa avaimia omasta token-saldostasi esimerkiksi ystäville tai kollegoille. Jaetut avaimet vähentävät tokeneita avaimestasi vasta, kun niitä todella käytetään – käyttämättömät lahjat eivät maksa sinulle mitään.',
    'unreachable' => 'Lahjakorttikampanjoitasi ei juuri nyt voitu ladata. Yritä myöhemmin uudelleen.',
    'copy_link' => 'Kopioi linkki',
    'public_link' => 'Julkinen linkki',
    'delete_note' => 'Vanhentuneet ja poistetut kampanjat poistetaan automaattisesti.',
    'print_cards' => 'Tulosta kortit (PDF)',
    'disable' => 'Poista käytöstä',
    'delete' => 'Poista nyt',

    'status' => [
        'active' => 'aktiivinen',
        'disabled' => 'pois käytöstä',
        'expired' => 'vanhentunut',
    ],

    'facts' => [
        'tokens_per_key' => ':tokens tokenia per avain',
        'redeemed' => ':redeemed / :total lunastettu',
        'budget' => ':left / :total tokenia jäljellä',
        'expires' => 'päättyy :date',
    ],

    'create' => [
        'heading' => 'Luo kampanja',
        'info' => 'Kampanja rahoitetaan tällä avaimella: jaetut tokenit vähennetään saldostasi, kun niitä käytetään. Kampanjat kestävät 3 kuukautta, jaetut avaimet ovat voimassa 1 kuukauden lunastuksen jälkeen.',
        'name' => 'Nimi (näkyy vain sinulle)',
        'tokens_per_key' => 'Tokenia per jaettu avain',
        'total_volume' => 'Tokenien enimmäismäärä yhteensä',
        'total_volume_hint' => 'Avaimessasi on tällä hetkellä :charge tokenia. Et voi koskaan jakaa enempää kuin saldosi.',
        'voucher_count' => 'Lahjakorttien määrä (valinnainen)',
        'voucher_count_hint' => 'Oletus: enimmäismäärä jaettuna tokeneilla per avain.',
        'submit' => 'Luo kampanja',
        'error' => [
            'tokens_per_key_too_high' => 'Tokenit per avain eivät voi ylittää enimmäismäärää.',
            'voucher_count_out_of_range' => 'Lahjakorttien määrä ei sovi yhteen tokenien per avain ja enimmäismäärän kanssa.',
            'over_budget' => 'Enimmäismäärä ylittää käytettävissä olevan saldosi.',
            'too_many_active' => 'Sinulla on jo enimmäismäärä aktiivisia kampanjoita.',
            'invalid' => 'Kampanjaa ei voitu luoda. Tarkista tietosi.',
            'unreachable' => 'Kampanjaa ei juuri nyt voitu luoda. Yritä myöhemmin uudelleen.',
        ],
    ],
];
