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

    /**
     * /c — App\Http\Controllers\VoucherController.
     */
    'redeem' => [
        'enter' => [
            'heading' => 'Lunasta lahjakorttisi',
            'description' => 'Saitko lahjakorttikoodin ilmaisiin MetaGer-hakuihin? Syötä se tähän saadaksesi oman henkilökohtaisen MetaGer-avaimesi.',
            'label' => 'Lahjakorttikoodisi',
            'submit' => 'Lunasta koodi',
            'invalid_code' => 'Tämä koodi ei ole kelvollinen. Tarkista syöttämäsi tiedot.',
            'rate_limited' => 'Liian monta yritystä. Yritä myöhemmin uudelleen.',
        ],
        'teaser' => [
            'heading' => 'MetaGer-lahjasi',
            'tokens' => 'Tokenia',
            'description' => 'Tämä koodi antaa sinulle oman MetaGer-avaimen, jossa on :tokens tokenia - hae verkosta mainoksitta ja ilman seurantaa.',
            'validity' => 'Avain on voimassa :days päivää lunastuksen jälkeen.',
            'submit' => 'Hae avaimeni',
        ],
        'redeemed' => [
            'heading' => 'Tässä on MetaGer-avaimesi!',
            'description' => 'Uudessa avaimessasi on :tokens tokenia.',
            'save' => [
                'heading' => '1. Tallenna avaimesi',
                'description' => 'Avaimesi on kirjautumistietosi - se näytetään vain täällä eikä sitä voi palauttaa. Tallenna se salasananhallintaan, lataa QR-koodi tai tulosta tämä sivu.',
            ],
            'copy_key' => 'Kopioi avain',
            'validity' => 'Avain on voimassa :date asti.',
            'use' => [
                'heading' => '2. Aloita haku',
                'description' => 'Avaa tämä linkki aktivoidaksesi avaimen selaimessasi. Lisää se kirjanmerkkeihin pysyäksesi kirjautuneena.',
            ],
            'copy_url' => 'Kopioi linkki',
            'start_searching' => 'Aloita haku nyt',
            'to_account' => 'Siirry tililleni',
            'qr_alt' => 'Avaimen QR-koodi',
            'no_cookies' => 'Tämä selain ei näytä säilyttävän evästeitä. Tallenna sen sijaan avain tai yllä oleva QR-koodi.',
        ],
        'error' => [
            'heading' => 'Tämä ei toiminut',
            'invalid_code' => 'Tätä koodia ei ole olemassa. Tarkista syöttämäsi tiedot.',
            'invalid_token' => 'Tämä linkki on virheellinen tai vanhentunut.',
            'already_redeemed' => 'Tämä koodi on jo lunastettu.',
            'campaign_inactive' => 'Tämä kampanja on päättynyt. Koodia ei voi enää lunastaa.',
            'budget_exhausted' => 'Kaikki tämän kampanjan lahjat on jo jaettu.',
            'rate_limited' => 'Liian monta yritystä. Yritä myöhemmin uudelleen.',
            'unreachable' => 'Lahjakorttia ei juuri nyt voitu lunastaa. Yritä myöhemmin uudelleen.',
            'unknown' => 'Tapahtui odottamaton virhe. Yritä myöhemmin uudelleen.',
            'retry' => 'Syötä koodi',
        ],
    ],
];
