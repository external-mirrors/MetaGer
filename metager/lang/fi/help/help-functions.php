<?php
return [
    'title' => 'MetaGer - Apua',
    'backarrow' => 'Takaisin',
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
        'title' => 'Lisää MetaGer Key <a title="For easy help, click here" href="/hilfe/easy-language/functions#eh-keyexplain"><img class="easy-help-icon lm-only" src="/img/help-questionmark-icon-lm.svg"/><img class="easy-help-icon dm-only" src="/img/help-questionmark-icon-dm.svg"/></a>',
        '1' => 'MetaGer-avain asetetaan automaattisesti selaimeesi ja sitä käytetään. Sinun ei tarvitse tehdä mitään muuta. Jos haluat käyttää MetaGer-avainta muissa laitteissa, MetaGer-avain voidaan määrittää usealla eri tavalla:',
        'more' => 'Kaikki käyttöönottotavat ja lisää kysymyksiä avaimesta',
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
