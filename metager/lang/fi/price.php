<?php

/**
 * Was ein MetaGer-Schlüssel kostet — /preise.
 *
 * Aus pass/lang/<locale>/cost.json des Keymanagers übernommen, wo diese Seite
 * bis zum Umzug lag. Die Preiszahlen selbst stehen bewusst nicht hier: sie
 * kommen über App\Landing\KeyPrice vom Keymanager, weil der Checkout sie
 * ausgibt.
 */

return [
    "headings" => [
        "MetaGer-avain maksaa näin paljon",
        "Tärkein yhteenveto",
    ],
    "texts" => [
        "Jokaisesta mainoksettomasta MetaGer-verkkohausta oletusasetuksilla veloitetaan <b>1 merkki</b>. Voit milloin tahansa täydentää avaintasi jollakin näistä token-paketeista.",
    ],
    "short-info" => [
        [
            "heading" => "Kortit ovat voimassa 2 vuotta",
            "text" => "Ostamasi merkit on suunniteltu pysymään voimassa, kunnes ne on käytetty loppuun. Kestotilausta ei ole.",
        ],
        [
            "heading" => "30 päivän rahat takaisin -takuu",
            "text" => "Jos olet tyytymätön avaimeen, sinulla on 30 päivää ostopäivästä aikaa palauttaa käyttämätön hyvitys.",
        ],
        [
            "heading" => "Avain asetetaan automaattisesti ja sitä käytetään selaimessa.",
            "text" => "Sinun ei tarvitse tehdä mitään muuta, jotta voit käyttää MetaGer-avainta haussa. Kun olet ladannut sen, se otetaan automaattisesti käyttöön selaimessasi, ja saat tietoa siitä, miten se voidaan helposti ottaa käyttöön lisälaitteissa.",
        ],
        [
            "heading" => "Ei seurantaa",
            "text" => "Käytä <a href=\":linkapp\">Android-sovellusta</a> tai selainlaajennustamme ja ole todistettavasti anonyymi käyttämällä <a href=\":linktokens\">anonyymejä tunnuksia</a>.",
        ],
    ],
    "pricing" => [
        "heading" => "Hintamme muodostuvat seuraavasti",
        "texts" => [
            "Suurin osa tuloistamme virtaa suoraan hakupalveluihin, joita kysyt. Haluamme tarjota kestävän konseptin, mikä tarkoittaa, että hakupalveluille ei aiheudu taloudellista vahinkoa siitä, että ne tarjoavat nimettömiä ja mainoksettomia hakutuloksia MetaGerille. Lisäksi on olemassa osuus, jolla katetaan henkilöstö- ja palvelinkustannuksiamme, ja tietysti maksupalveluntarjoajien maksut ja verot sisältyvät hintoihin.",
            "Valitsemalla hakupalvelut, joita haetaan, voit siis paitsi määrittää omat kustannuksesi myös päättää samalla, mitä hankkeita haluat tukea. Siksi myös token-pohjainen laskutus.",
        ],
    ],
    "payment-methods" => [
        "heading" => "Maksutavat",
        "texts" => [
            "MetaGer-avaimet on suunniteltu siten, että ne eivät vaadi henkilötietoja. Viimeistään maksun suorittamisen yhteydessä tarvitaan kuitenkin yleensä joitakin tietoja. Olkoon se sitten maksutilin IBAN-tili tai käytetyn PayPal-tilin sähköpostiosoite. SUMA-EV ei käsittele näitä tietoja itse eikä tallenna niitä. Maksutavan mukaan maksupalveluntarjoaja kuitenkin tekee niin.",
            "Tämän vuoksi maksutapamme on määritetty siten, että käyttäjätietoja on kerättävä mahdollisimman vähän ja joissakin tapauksissa niitä ei tarvitse kerätä lainkaan.",
        ],
        "anonymous" => "Anonyymit maksutavat",
        "more" => "Muut maksutavat",
    ],
    /**
     * Die Namen der Zahlungsarten. Standen im "checkout"-Namensraum des
     * Keymanagers, der dort bleibt — hierher kopiert, weil diese Seite die
     * einzige war, die sie außerhalb des Bezahlvorgangs gebraucht hat.
     */
    "methods" => [
        "cash" => "Käteinen",
        "prepay" => "Pankkisiirto",
        "card" => "Luotto- / pankkikortti",
    ],
];
