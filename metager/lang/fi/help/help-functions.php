<?php
return [
    'title' => 'MetaGer - Apua',
    'backarrow' => 'Takaisin',
    'mehrwortsuche' => [
        '2' => 'Esimerkki: haku Shakespears <div class="well well-sm">to be or not to be</div> antaa monia tuloksia, mutta tarkka lause löytyy vain käyttämällä <div class="well well-sm">"to be or nor to be".</div>',
    ],
    'urls' => [
        'title' => 'Sulje pois URL-osoitteet',
        'explanation' => 'Voit sulkea pois hakutulokset, joiden tuloslinkit sisältävät tiettyjä sanoja, käyttämällä hakusanaa "-url:".',
        'example_b' => '<i>hakuni</i> -url:dog',
        'example_a' => 'Esimerkki: Koira: Haluat sulkea pois tulokset, joissa sana "koira" esiintyy tuloslinkissä:',
    ],
    'bang' => [
        'title' => 'MetaGer-kartat <a title="For easy help, click here" href="/hilfe/easy-language/services#eh-maps" ><img class="easy-help-icon lm-only" src="/img/help-questionmark-icon-lm.svg"/><img class="easy-help-icon dm-only" src="/img/help-questionmark-icon-dm.svg"/></a>',
        '1' => 'MetaGer tukee rajoitetusti kirjoitustyyliä, jota kutsutaan usein \'!bang\' -syntaksiksi.<br> \'!bang\' alkaa aina huutomerkillä eikä sisällä välilyöntejä. Esimerkkejä ovat \'!twitter\' tai \'!facebook\'.<br>Kun hakukyselyssä käytetään tuettua !bang-merkintää, pikavihjeissämme näkyy merkintä, jonka avulla voit jatkaa hakua kyseisessä palvelussa (Twitter tai Facebook) napin painalluksella.',
        '2' => 'Miksi !paukkuja ei avata suoraan?',
        '3' => '!bangin "uudelleenohjaukset" ovat osa pikavinkkejä ja vaativat ylimääräisen "klikkauksen". Tämä oli meille vaikea päätös, sillä se tekee !bangista vähemmän hyödyllisen. Se on kuitenkin valitettavasti välttämätöntä, koska linkit, joihin uudelleenohjaus tapahtuu, eivät ole peräisin meiltä vaan kolmannelta osapuolelta, DuckDuckGolta. <p>Varmennamme aina, että käyttäjillämme säilyy aina kontrolli. Siksi suojaamme kahdella tavalla: Ensinnäkin, syötettyä hakusanaa ei koskaan välitetä DuckDuckGolle, ainoastaan !bang. Toiseksi käyttäjä vahvistaa nimenomaisesti käynnin !bang-kohteessa. Valitettavasti henkilöstösyistä emme voi tällä hetkellä itse tarkistaa tai ylläpitää kaikkia näitä !bangeja.',
    ],
    'selist' => [
        'title' => 'Lisää MetaGer selaimesi hakukoneluetteloon <a title="For easy help, click here" href="/hilfe/easy-language/functions#eh-selist"><img class="easy-help-icon lm-only" src="/img/help-questionmark-icon-lm.svg"/><img class="easy-help-icon dm-only" src="/img/help-questionmark-icon-dm.svg"/></a>',
        'explanation_b' => 'Joissakin selaimissa sinun on annettava URL-osoite; sen on oltava "https://metager.de/meta/meta.ger3?input=%s" ilman lainausmerkkejä. Voit luoda URL-osoitteen itse etsimällä metager.de:llä jotain ja korvaamalla osoitepalkin "input="-kohdan takana olevan kohdan %s:llä. Jos sinulla on edelleen ongelmia, ota meihin yhteyttä: <a href="/kontalt" target="_blank" rel="noopener">Yhteydenottolomake</a>',
        'explanation_a' => 'Yritä ensin asentaa nykyinen lisäosa. Asenna se klikkaamalla linkkiä suoraan hakukentän alapuolella. Selaimesi pitäisi olla jo havaittu siellä.',
    ],
    'key' => [
        '4' => 'Tallenna tiedosto <br>Kun olet MetaGer-avaimen <a href = "/keys/key/enter">hallintasivulla</a>, siellä on mahdollisuus tallentaa tiedosto. Tämä tallentaa MetaGer-avaimen tiedostoksi. Voit sitten käyttää tätä tiedostoa toisessa laitteessa kirjautuaksesi sisään avaimellasi.',
        '5' => 'Skannaa QR-koodi <br>Vaihtoehtoisesti voit myös skannata <a href = "/keys/key/enter">hallintasivulla</a> näkyvän QR-koodin ja kirjautua sisään toisella laitteella.',
        '6' => 'MetaGer-avaimen syöttäminen manuaalisesti <br>Voit syöttää avaimen myös manuaalisesti toisella laitteella.',
        'colors' => [
            'title' => 'Värillinen MetaGer-avain',
            '1' => 'Jotta voit helposti tunnistaa, etsitkö mainoksetonta sivustoa, olemme antaneet keskeisille symboleillemme värit. Alla on selitykset vastaaville väreille:',
            'grey' => 'Harmaa: Et ole asettanut avainta. Käytät vapaata hakua.',
            'red' => 'Punainen: Jos näppäinsymboli on punainen, se tarkoittaa, että näppäin on tyhjä. Olet käyttänyt kaikki mainoksettomat haut. Voit ladata avaimen uudelleen avainten hallintasivulla.',
            'green' => 'Vihreä: Jos näppäinsymboli on vihreä, käytät ladattua näppäintä.',
            'yellow' => 'Keltainen: Jos näet keltaisen avaimen, sinulla on vielä 30 merkkiä saldona. Hakusi ovat loppumassa. On suositeltavaa ladata avain pian uudelleen.',
        ],
        'title' => 'Lisää MetaGer Key <a title="For easy help, click here" href="/hilfe/easy-language/functions#eh-keyexplain"><img class="easy-help-icon lm-only" src="/img/help-questionmark-icon-lm.svg"/><img class="easy-help-icon dm-only" src="/img/help-questionmark-icon-dm.svg"/></a>',
        '1' => 'MetaGer-avain asetetaan automaattisesti selaimeesi ja sitä käytetään. Sinun ei tarvitse tehdä mitään muuta. Jos haluat käyttää MetaGer-avainta muissa laitteissa, MetaGer-avain voidaan määrittää usealla eri tavalla:',
        '2' => 'Kirjautumiskoodi <br>MetaGer-avaimen <a href = "/keys/key/enter">hallintasivulla</a> voit käyttää kirjautumiskoodia lisätäksesi avaimesi toiseen laitteeseen. Kirjoita yksinkertaisesti kuusinumeroinen numerokoodi kirjautumisen yhteydessä. Kirjautumiskoodia voi käyttää vain kerran, ja se on voimassa vain niin kauan kuin ikkuna on auki.',
        '3' => 'Kopioi URL-osoite <br>Kun olet MetaGer-avaimen <a href = "/keys/key/enter">hallintasivulla</a>, siellä on mahdollisuus kopioida URL-osoite. Tätä URL-osoitetta voidaan käyttää kaikkien MetaGer-asetusten, myös MetaGer-avaimen, tallentamiseen toiseen laitteeseen.',
    ],
    'multiwordsearch' => [
        'title' => 'Monisanahaku <a title="For easy help, click here" href="/hilfe/easy-language/functions#eh-severalwords"><img class="easy-help-icon lm-only" src="/img/help-questionmark-icon-lm.svg"/><img class="easy-help-icon dm-only" src="/img/help-questionmark-icon-dm.svg"/></a>',
        '4' => [
            'text' => "Lausehaun avulla voit etsiä yksittäisten sanojen sijasta sanayhdistelmiä. Sulje vain yhdessä esiintyvät sanat lainausmerkkeihin.",
            'example' => '"pyöreä pöytä"',
        ],
        '3' => [
            'text' => "Jos haluat varmistaa, että hakusanat näkyvät myös tuloksissa, ne on suljettava lainausmerkkeihin.",
            'example' => '"pöytä" "pyöreä" "pöytä"',
        ],
        '2' => "Jos tämä ei riitä sinulle, sinulla on kaksi vaihtoehtoa, joilla voit tarkentaa hakua:",
        '1' => "Kun etsit useampaa kuin yhtä sanaa MetaGerissä, yritämme automaattisesti tarjota tuloksia, joissa kaikki sanat esiintyvät tai ovat mahdollisimman lähellä toisiaan.",
    ],
    'easy-help' => 'Klikkaamalla symbolia <a title="For easy help, click here" href="/hilfe/easy-language/services" ><img class="easy-help-icon lm-only" src="/img/help-questionmark-icon-lm.svg"/><img class="easy-help-icon dm-only" src="/img/help-questionmark-icon-dm.svg"/></a> pääset yksinkertaistettuun versioon ohjeesta.',
    'searchfunction' => [
        'title' => "Hakutoiminnot",
    ],
    'stopwords' => [
        'title' => 'Stopwords <a title="For easy help, click here" href="/hilfe/easy-language/functions#eh-stopwordsearch"><img class="easy-help-icon lm-only" src="/img/help-questionmark-icon-lm.svg"/><img class="easy-help-icon dm-only" src="/img/help-questionmark-icon-dm.svg"/></a>',
        '3' => "uusi auto -bmw",
        '2' => "Esimerkki: Etsit uutta autoa, mutta et todellakaan BMW:tä. Sinun panoksesi olisi:",
        '1' => "Jos haluat sulkea pois MetaGerin hakutulokset, jotka sisältävät tiettyjä sanoja (poissulkevat sanat / stopwords), voit tehdä sen liittämällä näiden sanojen eteen miinusmerkin.",
    ],
    'exactsearch' => [
        'title' => 'Tarkka haku <a title="For easy help, click here" href="/hilfe/easy-language/functions#exactsearch"><img class="easy-help-icon lm-only" src="/img/help-questionmark-icon-lm.svg"/><img class="easy-help-icon dm-only" src="/img/help-questionmark-icon-dm.svg"/></a>',
        '1' => "Jos haluat löytää tietyn sanan MetaGer-hakutuloksista, voit liittää sanan eteen plusmerkin. Kun käytät plus-merkkiä ja lainausmerkkejä, lause etsitään juuri sellaisena kuin olet sen syöttänyt.",
        '2' => "Esimerkki: S",
        '3' => 'Esimerkki: ',
        'example' => [
            '1' => "+esimerkkisana",
            '2' => '+"esimerkkilause"',
        ],
    ],
];
