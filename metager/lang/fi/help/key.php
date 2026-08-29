<?php

/**
 * Fragen zum MetaGer-Schlüssel — /hilfe/schluessel.
 *
 * Aus dem "faq"-Zweig von pass/lang/<locale>/help.json des Keymanagers.
 */

return [
    "heading" => "Kysymyksiä MetaGer-avaimesta",
    "faqs" => [
        [
            "summary" => "Miten MetaGer-avain toimii?",
            "description" => "MetaGer-avaimella haet ilman mainoksia. Saat merkkejä, joista vähennetään yksi haku per haku. Kun käytät MetaGer-avainta, kaikki ominaisuudet, jotka suojaavat MetaGeria automaattisilta puheluilta, poistetaan käytöstä. Tämä tarkoittaa, että et näe captcha-pyyntöjä eikä IP-osoitettasi säilytetä rajoitetun ajan. Yksinkertaisesti sanottuna tämä tekee MetaGeristä nopeamman, luotettavamman ja turvallisemman.",
        ],
        [
            "summary" => "Miten anonyymi merkki toimii?",
            "description" => "Voit käyttää anonyymiä tunnusta selainlaajennuksemme tai sovelluksemme kanssa. Näin voit tehdä hakuja entistäkin turvallisemmin MetaGerillä. Kun käytät anonyymiä tokenia, osa luottotiedoistasi satunnaisten salasanojen muodossa tallennetaan laitteeseesi. <a href=\":tokenlink\">Monimutkaisen salausprosessin</a> avulla edes meidän on mahdotonta yhdistää suoritettuja hakuja toisiinsa tai avaimeesi.",
        ],
        [
            "steps" => [
                [
                    "description" => "Kun olet MetaGerin avainten hallintasivulla, siellä on mahdollisuus kopioida URL-osoite. Tämän URL-osoitteen avulla kaikki MetaGerin asetukset sekä MetaGer-avain voidaan tallentaa toiseen laitteeseen.",
                    "heading" => "Kopioi URL-osoite",
                ],
                [
                    "heading" => "Tallenna tiedosto",
                    "description" => "Kun olet MetaGerin avainten hallintasivulla, siellä on mahdollisuus tallentaa tiedosto. Tämä tallentaa MetaGer-avaimesi tiedostoksi. Voit sitten käyttää tätä tiedostoa toisessa laitteessa kirjautuaksesi sinne avaimellasi.",
                ],
                [
                    "heading" => "Skannaa QR-koodi",
                    "description" => "Vaihtoehtoisesti voit myös skannata hallintasivulla näkyvän QR-koodin kirjautuaksesi sisään toisella laitteella.",
                ],
                [
                    "heading" => "Syötä MetaGer-avain manuaalisesti",
                    "description" => "Voit tietysti syöttää avaimen myös manuaalisesti toisella laitteella.",
                ],
            ],
            "summary" => "Miten käytän MetaGer-avainta?",
            "description" => "MetaGer-avain otetaan automaattisesti käyttöön ja sitä käytetään selaimessa. Sinun ei siis tarvitse tehdä mitään muuta. Jos haluat käyttää MetaGer-avainta muissa laitteissa, on useita tapoja määrittää MetaGer-avain:",
        ],
        [
            "summary" => "Minun on syötettävä avaimeni säännöllisesti. Mitä voin tehdä?",
            "description" => "Ohjeistamme selaimesi tallentamaan avaimen pysyvästi, kun se on luotu tai kirjautunut sisään. Selaimesi asetuksista riippuen olet saattanut asettaa sen poistamaan evästeet ja verkkosivuston tiedot säännöllisesti, jolloin kirjaudut ulos myös MetaGeristä. Sinulla on seuraavat vaihtoehdot:",
            "steps" => [
                [
                    "description" => "Firefoxin asetuksissa voit laittaa MetaGerin valkoiselle listalle, jotta voit poistaa evästeet ja verkkosivuston tiedot, jotka pitävät sinut kirjautuneena sisään.",
                    "heading" => "Lisää poikkeus",
                ],
                [
                    "heading" => "Asenna selainlaajennus",
                    "description" => "Selainlaajennuksemme <a href=\"https://addons.mozilla.org/firefox/addon/metager-suche/\" target=\"_blank\" rel=\"noopener\">Firefoxille</a> ja <a href=\"https://chrome.google.com/webstore/detail/gjfllojpkdnjaiaokblkmjlebiagbphd\" target=\"_blank\" rel=\"noopener\">Chromelle</a> voi tallentaa hakuasetuksesi, jotka sisältävät avaimesi, ilman evästeitä, joten voit poistaa kaikki selaintiedot kirjautumatta ulos MetaGeristä.",
                ],
                [
                    "heading" => "Kirjaudu sisään syöttämättä 36-merkkistä avainta",
                    "description" => "Jos käytät salasanahallintaohjelmaa, voit tallentaa avaimen siihen, jotta voit kirjautua sisään automaattisesti. Vaihtoehtoisesti tarjoamme <a href=\":keylink\">asetusten URL-osoitteen</a> tallennettavaksi esim. kirjanmerkiksi. Kun avaat asetusten URL-osoitteen, kirjaudut sisään ilman avaimen manuaalista syöttämistä.",
                ],
            ],
        ],
        [
            "summary" => "Olen tyytymätön MetaGer-avaimeen. Mitä voin tehdä?",
            "description" => "Tässä tapauksessa voit pyytää hyvitystä käyttämättömistä poleteista 30 päivän kuluessa ostopäivästä. Tätä varten tarvitset maksutunnuksesi. Voit pyytää hyvitystä avaamalla MetaGerin avainten hallintasivun. Napsauta siellä \"Tilaukset\"-valikkokohtaa ja syötä maksutunnuksesi. Sen jälkeen voit napsauttaa painiketta \"Pyydä hyvitystä\" ja lähettää hyvityspyynnön.",
        ],
        [
            "summary" => "Miten voin tehdä hakuja täysin anonyymisti?",
            "description" => "Yksityisyytesi ja nimettömyytesi ovat meille erittäin tärkeitä. Siksi tarjoamme nimettömiä maksutapoja (käteinen). Tarjoamme myös <a href=\":tokenlink\">anonyymien polettien</a> käyttöä, joita he voivat käyttää jopa hakuun todennettavasti anonyymisti.",
        ],
        [
            "summary" => "Tarvitsen laskun. Miten saan sen?",
            "description" => "Tätä varten tarvitset vain maksutunnuksesi. Voit pyytää laskua avaamalla MetaGer-avaimen hallintasivun. Siellä napsautat \"Tilaukset\"-valikkokohtaa ja syötät maksutunnuksesi. Nyt voit napsauttaa painiketta \"Pyydä lasku\" ja käynnistää laskupyynnön. Laskua varten tarvitsemme koko nimesi, sähköpostiosoitteesi ja osoitteesi.",
        ],
        [
            "summary" => "Haluaisin ladata MetaGer-avaimeni automaattisesti. Miten se tehdään?",
            "description" => "Jäsenillemme jäsenyyteen sisältyvä avain täydennetään automaattisesti kuukausittain. Avaimen määrä riippuu tässä tapauksessa maksetusta jäsenmaksusta.",
        ],
        [
            "summary" => "Sain kortin tai linkin, jossa on lahjakoodi. Mitä teen sillä?",
            "description" => "Jotkin organisaatiot lahjoittavat MetaGer-avaimia, joissa on kiinteä saldo, kampanjakorttien tai linkin kautta. Avaa <a href=\":voucherlink\">lunastussivumme</a>, syötä kortille painettu koodi tai lue kortin QR-koodi. Saat heti uuden MetaGer-avaimen, jossa on lahjoitettu saldo. Saldo on voimassa rajoitetun ajan, ja jokainen koodi voidaan lunastaa vain kerran.",
        ],
    ],
    "more-questions" => "Onko teillä muita kysymyksiä? Käytä sitten rohkeasti <a href=\":contactlink\" target=\"_blank\">yhteydenottolomakettamme</a>.",
];
