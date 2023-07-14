<?php
return [
    'head' => [
        '1' => 'Avoimuuslausuma',
        '2' => 'MetaGer on läpinäkyvä',
        '3' => 'Mikä on metahakukone?',
        '4' => 'Mikä on metahakukoneen etu?',
        '5' => 'Miten sijoituksemme muodostuu?',
        'compliance' => 'Miten MetaGer vastaa viranomaisten pyyntöihin?',
    ],
    'text' => [
        '1' => 'MetaGer on läpinäkyvä. Lähdekoodimme <a href=":sourcecode"></a> on vapaasti lisensoitu ja julkisesti kaikkien nähtävillä. Emme tallenna käyttäjätietoja ja arvostamme tietosuojaa ja yksityisyyttä. Siksi annamme nimettömän pääsyn hakutuloksiin. Tämä on mahdollista anonyymin välityspalvelimen ja TOR-salatun pääsyn avulla. Lisäksi MetaGerin organisaatiorakenne on läpinäkyvä, sillä sitä tukee voittoa tavoittelematon yhdistys <a href=":sumalink">SUMA-EV</a>, jonka jäseneksi kuka tahansa voi liittyä.',
        '2' => [
            '1' => 'Jotta voisimme selittää, mitä metahakukoneet ovat, on järkevää selittää ensin lyhyesti, miten tavallisten hakukoneiden indeksointi toimii. Tavalliset hakukoneet saavat hakutuloksensa verkkosivujen tietokannasta, jota kutsutaan myös indeksiksi. Hakukoneet käyttävät niin sanottuja "indeksoijia", jotka keräävät verkkosivuja ja lisäävät ne indeksiin (tietokantaan). Mönkijä aloittaa tietystä verkkosivujen joukosta ja avaa kaikki siihen linkitetyt verkkosivut. Nämä indeksoidaan eli lisätään indeksiin. Sen jälkeen indeksoija avaa näille sivuille linkitetyt verkkosivut ja jatkaa näin.',
            '2' => 'Metahakukone yhdistää useiden hakukoneiden tulokset ja arvioi ne uudelleen omien kriteeriensä mukaan. Tämä tarkoittaa, että metahakukoneella ei ole omaa hakemistoa. Siksi metahakukoneet eivät käytä indeksoijia. Ne käyttävät muiden hakukoneiden hakemistoa.',
        ],
        '3' => 'Metahakukoneiden selkeä etu on se, että käyttäjä tarvitsee vain yhden hakukyselyn saadakseen useiden hakukoneiden tulokset. Metahakukone antaa asiaankuuluvat tulokset jälleen kerran lajiteltuna tulosluettelona. MetaGer ei ole puhdas metahakukone, sillä käytämme myös omia pieniä hakemistoja.',
        '4' => 'Otamme lähteenä olevien hakukoneiden sijoitukset ja punnitsemme ne. Nämä sijoitukset muunnetaan sitten pistemääriksi. Lisäpisteitä annetaan tai vähennetään hakusanojen esiintymisestä URL-osoitteessa ja pätkässä sekä erikoismerkkien liiallisesta esiintymisestä (esim. muista merkistöistä, kuten kyrillisestä). Käytämme myös estolistaa yksittäisten sivujen poistamiseksi tulosluettelosta. Estämme verkkosivut näytössä, jos olemme siihen lain mukaan velvollisia. Pidätämme myös oikeuden estää verkkosivut, joilla on todistetusti virheellistä tietoa, erittäin huonolaatuiset verkkosivut ja muut erityisen epäilyttävät verkkosivut.',
        '5' => 'Jos sinulla on lisäkysymyksiä tai epäselvyyksiä, käytä <a href=":contact">yhteydenottolomaketta</a> ja kysy meiltä kysymyksesi!',
        'compliance' => 'Noudatamme viranomaisten pyyntöjä, jos olemme laillisesti velvollisia tekemään niin ja tulemme siihen tulokseen, että noudattamisemme ei loukkaa perusvapauksia. Suhtaudumme tähän tarkasteluun erittäin vakavasti. Lisäksi tallennamme mahdollisimman vähän henkilötietoja, jotta vähennämme riskiä siitä, että tietoja joudutaan luovuttamaan. Alla olevasta taulukosta löydät tietoja viranomaisten pyynnöistä, joita olemme käsitelleet viimeisten viiden vuoden aikana. Lisätietoja annetaan lähiaikoina.',
    ],
    'table' => [
        'compliance' => [
            'th' => [
                'authinfocomp' => 'Täytetyt tietopyynnöt',
                'authblockcomp' => 'Täytetyt estopyynnöt',
            ],
        ],
    ],
    'alt' => [
        'text' => [
            '1' => 'Visuaalinen esitys kahdesta toisiaan täydentävästä indeksistä, jotka muodostavat meta-indeksin.',
        ],
    ],
];
