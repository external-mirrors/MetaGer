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
    // The landing page shown to a visitor without a key: hero, "how it works",
    // and the five benefit cards. It came from the keymanager's own root page
    // (pass/views/index.ejs, pass/lang/*/index.json), which /keys used to serve
    // and which now redirects here.
    //
    // Placeholders are Laravel's :name, not i18next's {{name}}, and the links
    // are passed in from parts/landing/* so the locale prefix and the /keys
    // paths stay in one place.
    'landing' => [
        'title' => 'MetaGer: hae ja selaa verkkoa ilman että sinua seurataan',
        'description' => 'MetaGer kunnioittaa yksityisyyttäsi: ei mainoksia, ei seurantaa, ei lokitusta. Ja nyt voit myös vierailla millä tahansa sivustolla nimettömästi.',
        'advantages' => [
            'ads' => 'Ei mainoksia',
            'tracking' => 'Ei seurantaa',
            'logging' => 'Ei lokitusta',
            'compromise' => 'Ei kompromisseja',
        ],
        'calltoaction' => 'Näin se toimii',
        'benefits' => [
            'browsing' => [
                'heading' => 'Ei vain anonyymiä hakua – myös anonyymiä selaamista',
                'description' => 'MetaGer-avaimellasi voit avata minkä tahansa sivuston yksityisessä selaimessa, joka toimii turvallisesti meidän palvelimillamme – ei sinun laitteellasi. Sivustot eivät voi nähdä, kuka olet tai mistä selaat, ja kaikki poistetaan automaattisesti istunnon päätyttyä. Ei asennusta, ei asetuksia – avaa vain ja aloita.',
                'fingerprinting' => 'Sormenjälkitunnistus',
                'tracking' => 'Seuranta',
            ],
            'ads' => [
                'heading' => 'Ilman mainoksia',
                'description' => 'Mainokset ja yksityisyys sopivat harvoin yhteen. Siksi MetaGerissä ei ole minkäänlaista mainontaa, jotta voimme suojata yksityisyytesi ilman kompromisseja.',
                'ads' => 'Mainonta',
                'tracking' => 'Seurantalinkit',
            ],
            'logging' => [
                'heading' => 'Ilman lokitusta',
                'description' => 'Internetissä hakeminen jättää yleensä jälkeensä datajäljen. Meidän ei tarvitse säilyttää siitä mitään: hakukoneemme on rakennettu niin, ettei roskapostin torjunta vaadi lokeja. Et myöskään törmää sivustollamme yhteenkään captchaan, edes VPN:ää käyttäessäsi.',
                'logging' => 'Lokitus',
            ],
            'compromise' => [
                'heading' => 'Ilman kompromisseja',
                'description' => 'Henkilötietoihisi sidotun tilin sijaan saat yksinkertaisesti satunnaisesti luodun avaimen – ilman nimeä ja ilman sähköpostiosoitetta. Valitse useista <a href=":linkPaymentMethods">maksutavoista</a>, mukaan lukien täysin anonyymi käteismaksu. <a href=":linkApp">Android-sovelluksellamme</a> tai selainlaajennuksella voit jopa todistaa, että hakusi pysyvät anonyymeinä, <a href=":linkToken">anonyymien tunnusten</a> avulla.',
                'compromise' => 'Henkilötiedot',
            ],
            'efficiency' => [
                'heading' => 'Hae tehokkaammin',
                'description' => 'Löydä etsimäsi nopeammin. Kun siitä on hyötyä, lisäämme hakutuloksiin selkeitä syvälinkkejä, olennaisia uutisia ja videoita. Myös kuvahakumme hyödyntää lisälähteitä.',
            ],
        ],
        'howitworks' => [
            'heading' => 'Näin se toimii',
            'steps' => [
                [
                    'heading' => 'Hanki ilmainen avaimesi',
                    'description' => 'MetaGer-avaimesi luodaan automaattisesti. Ei rekisteröitymistä, ei henkilötietoja. Se on ainoa asia, jonka tarvitset MetaGerin käyttöön.',
                ],
                [
                    'heading' => 'Aktivoi käyttöoikeutesi',
                    'description' => 'Kertaluonteinen <a href=":linkCost">maksu</a> lisää avaimeesi saldoa, jota kutsumme tokeniksi. Sillä avaat mainoksettoman ja seurannattoman haun sekä anonyymin selaamisen – mukaan lukien kaikki MetaGerin nykyiset ja tulevat ominaisuudet. Noin 500 tokenia (5 €) riittää yleensä noin 2 kuukaudeksi.',
                    'membership' => 'Huomio: <a href="https://suma-ev.de" target="_blank">SUMA-EV</a> on yleishyödyllinen kannatusyhdistyksemme, ja sen jäsenet voivat käyttää MetaGeriä ilman lisäkustannuksia. <a href=":linkMembership" target="_blank">Liity jäseneksi nyt</a>',
                ],
                [
                    'heading' => 'Käytä MetaGeriä kaikkialla',
                    'description' => 'Käytä samaa avainta niin monella laitteella kuin haluat tai jaa se ystävien ja perheen kanssa. Avaa vain MetaGer millä tahansa laitteella, syötä avaimesi, ja voit hakea – tai selata anonyymisti.',
                ],
            ],
            'start' => 'Aloita',
            'login' => 'Minulla on jo avain',
        ],
    ],
];
