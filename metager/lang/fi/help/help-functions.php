<?php
return [
    'title' => 'MetaGer - Apua',
    'backarrow' => ' Takaisin',
    'suchfunktion' => [
        'title' => 'Hakutoiminnot',
    ],
    'stopworte' => [
        'title' => 'Yksittäisten sanojen poissulkeminen',
        '1' => 'Jos haluat sulkea pois sanoja hakutuloksesta, sinun on laitettava "-" sanan eteen.',
        '2' => 'Esimerkki: Etsit uutta autoa, mutta et BMW:tä. Silloin hakusi pitäisi olla <div class="well well-sm">uusi auto -bmw.</div>',
        '3' => 'uusi auto -bmw',
    ],
    'mehrwortsuche' => [
        'title' => 'Useamman kuin yhden sanan etsiminen',
        '1' => 'Ilman lainausmerkkejä saat tuloksia, jotka sisältävät yhden tai joitakin hakusanoja. Käytä lainausmerkkejä tarkkojen lausekkeiden, sitaattien...., hakuun.',
        '2' => 'Esimerkki: haku Shakespears <div class="well well-sm">to be or not to be</div> antaa monia tuloksia, mutta tarkka lause löytyy vain käyttämällä <div class="well well-sm">"to be or nor to be".</div>',
        '3' => [
            'example' => '"pyöreän pöydän" "päätös"',
            'text' => 'Käytä lainausmerkkejä varmistaaksesi, että hakusanasi näkyvät tulosluettelossa.',
        ],
        '4' => [
            'example' => '"pyöreän pöydän päätös"',
            'text' => 'Laita sanat tai lausekkeet lainausmerkkeihin etsiäksesi tarkkoja yhdistelmiä.',
        ],
    ],
    'urls' => [
        'title' => 'Sulje pois URL-osoitteet',
        'explanation' => 'Käytä "-url:", jos haluat sulkea pois hakutulokset, jotka sisältävät määritettyjä sanoja.',
        'example_b' => 'Kirjoita <i>minun hakusanani</i> -url:dog',
        'example_a' => 'Esimerkki: Esimerkiksi: Et halua sanaa "koira" tuloksissa:',
    ],
    'bang' => [
        'title' => '!bangs',
        '1' => 'MetaGer käyttää hieman erityistä kirjoitusasua nimeltä "!bang-syntaksi". !bang alkaa "!" -merkillä eikä sisällä tyhjiä ("!twitter", "!facebook" esimerkiksi). Jos käytät MetaGerin tukemaa !bangia, näet uuden merkinnän "Pikavihjeissä". Ohjaamme sitten määritettyyn palveluun (klikkaa painiketta).',
    ],
    'faq' => [
        '18' => [
            'h' => 'Miksi !paukkuja ei avata suoraan?',
            'b' => '!bang -\"redirections\" ovat osa pikavihjeitä, ja ne tarvitsevat ylimääräisen klikkauksen. Meidän oli päätettävä helppokäyttöisyyden ja tietojen hallinnan välillä. Mielestämme on tarpeen osoittaa, että linkit ovat kolmannen osapuolen omaisuutta (DuckDuckGo). Suojaus on siis kaksisuuntainen: ensinnäkin emme siirrä hakusanojasi vaan ainoastaan !bangin DuckDuckGolle. Toisaalta käyttäjä vahvistaa !bang-kohteen nimenomaisesti. Meillä ei ole resursseja ylläpitää kaikkia näitä !bangeja, olemme pahoillamme.',
        ],
    ],
    'searchinsearch' => [
        'title' => 'Haku hakusanalla',
        '1' => 'Tulos tallennetaan uuteen välilehteen, joka ilmestyy näytön oikeaan reunaan. Sen nimi on "Tallennetut tulokset". Voit tallentaa tänne yksittäisiä tuloksia useista hauista. TAB säilyy. Syöttämällä tähän välilehteen saat henkilökohtaisen tulosluettelon, jossa on työkaluja tulosten suodattamiseen ja lajitteluun. Napsauttamalla toista TAB:ia voit palata takaisin lisähakuja varten. Tämä ei onnistu, jos näyttö on liian pieni. Lisätietoja (toistaiseksi vain saksaksi): <a href="http://blog.suma-ev.de/node/225" target="_blank" rel="noopener"> SUMA-blogi</a>.',
    ],
    'selist' => [
        'title' => 'Haluan lisätä metager.de-sivuston selaimeni hakukoneiden luetteloon.',
        'explanation_b' => 'Jotkut selaimet tarvitsevat URL-osoitteen. Käytä "https://metager.org/meta/meta.ger3?eingabe=%s" ilman qoutation-merkkejä. Jos ongelmia ilmenee edelleen, kirjoita sähköpostia osoitteeseen <a href="/en/kontakt" target="_blank" rel="noopener">.</a>',
        'explanation_a' => 'Yritä ensin asentaa uusin saatavilla oleva lisäosa. Käytä hakukentän alla olevaa linkkiä, siinä on automaattinen selaimen tunnistus.',
    ],
];
