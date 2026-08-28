<?php
return [
    'plugin' => 'Asenna MetaGer',
    'plugin-title' => 'Lisää MetaGer selaimeesi',
    'key' => [
        'placeholder' => 'Kirjoita MetaGer-avaimesi aloittaaksesi haun.',
        'tooltip' => [
            'nokey' => 'Aseta mainokseton haku',
            'empty' => 'Merkki käytetty loppuun. Lataa nyt.',
            'low' => 'Merkki pian käytetty. Lataa nyt.',
            'full' => 'Mainokseton haku käytössä.',
        ],
    ],
    'placeholder' => 'MetaGer: Etsi ja löydä yksityisyyden suojaa',
    'searchbutton' => 'Aloita MetaGer-haku',
    'foki' => [
        'web' => 'Web',
        'bilder' => 'Kuvat',
        'nachrichten' => 'Uutiset',
        'science' => 'Tiede',
        'produkte' => 'Tuotteet',
        'maps' => 'Kartat',
    ],
    'adfree' => 'Käytä MetaGeria mainosvapaasti',
    'skip' => [
        'search' => 'Siirry hakukyselyn syöttöön',
        'navigation' => 'Siirry navigointiin',
        'fokus' => 'Siirry hakutarkennuksen valintaan',
    ],
    'lang' => 'wwitch kieli',
    'searchreset' => 'poista hakukyselyn syöttö',
    'searchbar-replacement' => [
        'tagline' => 'Avoin lähdekoodi. Mainokseton. Anonyymi.',
        'hook' => 'Hakukone, joka ei seuraa sinua.',
        'message' => 'Avaimesi on pääsysi – ei tiliä, ei sähköpostiosoitetta. Saldo ja asetukset ovat siinä kiinni.',
        'first_time' => 'Ensimmäistä kertaa täällä?',
        'start' => 'Ota avain käyttöön',
        // Swapped in by resources/js/accountBreadcrumb.js when this browser has
        // rendered a signed-in page before. Three strings, replacing three
        // elements in place — nothing appears, nothing moves.
        'welcome_back' => 'Tervetuloa takaisin.',
        'welcome_back_message' => 'Olet ollut kirjautuneena tällä laitteella aiemmin. Kirjaudu sisään samalla avaimella – saldosi on yhä tallessa.',
        'welcome_back_button' => 'Kirjaudu uudelleen',
        'have_key' => 'Kirjaudu sisään avaimellani',
        'login' => 'Kirjaudu sisään',
        'key_error' => "Syötetty avain ei ollut kelvollinen. Tarkista syöttö.",
        'login_code_error' => "Syötetty kirjautumiskoodi ei ollut voimassa. Vihje: Kirjautumiskoodit ovat voimassa vain silloin, kun ne näkyvät toisessa laitteessa!",
        'payment_id_error' => "Olet syöttänyt maksutunnuksen, joka ei ole oikea avain. Avaimesi on 36 merkkiä pitkä.",
        'new_key' => 'Ei vielä avainta?',
        'extension' => 'Pysy kirjautuneena sisään ja anonyyminä webextensionin avulla',
    ],
];
