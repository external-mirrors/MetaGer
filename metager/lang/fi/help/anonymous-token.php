<?php

/**
 * Anonyme Token — /hilfe/anonyme-token.
 *
 * Aus dem "anonymous-token"-Zweig von pass/lang/<locale>/help.json.
 * Der Pfad /keys/help/anonymous-token wird dauerhaft hierher weitergeleitet:
 * er steht in bereits versandten Mitglieds-Willkommensmails.
 */

return [
    "heading" => "Anonyymit merkit",
    "description" => [
        "heading" => "Mitä ovat anonyymit merkit?",
        "text" => "Jos käytät MetaGer-avainta, saat satunnaisesti generoidun salasanan, jonka selaimesi lähettää meille jokaisen hakukyselyn yhteydessä, jotta voimme mahdollistaa mainoksetonta hakua. Jos käytät <a href=\"/app\" target=\"_blank\">Android-sovellustamme</a> tai verkkolaajennustamme <a href=\"https://chrome.google.com/webstore/detail/gjfllojpkdnjaiaokblkmjlebiagbphd\" target=\"_blank\" rel=\"noopener\">Chromeen</a> ja <a href=\"https://addons.mozilla.org/firefox/addon/metager-suche/\" target=\"_blank\" rel=\"noopener\">Firefoxiin</a>, selaimesi lähettää meille salasanan sijasta satunnaisesti generoidun salasanan (anonyymin tunnisteen) jokaisen hakupyynnön yhteydessä tunnistautumista varten, joka luodaan paikallisesti. Näin varmistetaan, että jokainen salasana on yksilöllinen eikä sillä ole yhteyttä varsinaiseen MetaGer-avaimeen eikä yksittäisten salasanojen välillä.",
    ],
    "problem" => [
        "heading" => "Mikä ongelma nimettömillä tunnuksilla on tarkoitus ratkaista?",
        "text" => "Jos selaimesi lähettää meille aina saman salasanan jokaisen hakukyselyn yhteydessä, meillä olisi ainakin teoriassa mahdollisuus luoda korrelaatio kaikkien samalla avaimella tehtyjen hakujen välille. Vaikka emme tekisikään niin, luottamus olisi tietysti silti tarpeen, jotta voisimme olla varmoja anonyymistä hausta. Jotta meidän ei tarvitsisi vain luvata anonyymiä hakua, vaan voisimme myös todistaa sen, olemme ottaneet käyttöön anonyymit tunnukset.",
    ],
    "general-function" => [
        "heading" => "Miten se toimii?",
        "texts" => [
            "Haluamme siis, että kertakäyttösalasanat luodaan suoraan päätelaitteestasi, jonka sitten lähetät meille todennusta varten hakujen aikana. Jokaisen päätelaitteessasi olevan anonyymin tunnuksen osalta meidän on kuitenkin varmistettava, että MetaGer-avaimestasi on vähennetty tavallinen tunnus, ilman että (ja tämä on asian ydin) meille kerrotaan, mitä MetaGer-avainta käytettiin anonyymin tunnuksen tuottamiseen.",
            "Perinteisesti käytämme tähän tarkoitukseen jonkinlaista kryptografista allekirjoitusta. Tässä tapauksessa allekirjoittaisimme luodun anonyymin tunnisteen. Kun lähetät meille myöhemmin nimettömän tunnisteen ja allekirjoituksen, voimme olla varmoja, että nimettömät tunnisteet ovat voimassa. Allekirjoituksen saamiseksi olisit kuitenkin lähettänyt meille nimettömän merkin yhdessä oikean avaimesi kanssa, mikä tekisi nimettömyyden tyhjäksi.",
            "Sen vuoksi käytämme sen sijaan muunnettua salakirjoituksen muotoa, niin sanottua <a href=\"https://en.wikipedia.org/wiki/Blind_signature\" target=\"_blank\">sokeaa allekirjoitusta</a>. Todellisen elämän analogian luomiseksi se on kuin lähettäisi meille nimettömän merkkisi hiilipaperikuoressa. Tässä esimerkissä emme pystyisi avaamaan kirjekuorta, mutta voisimme allekirjoittaa sen ulkopuolelta, jolloin allekirjoituksemme siirtyisi sisällä olevaan anonyymiin merkkiin. Kun saat kirjekuoren takaisin, voit poistaa sen ja lähettää meille salasanan ja allekirjoituksen takaisin myöhemmin. Voisimme sitten vahvistaa, että kyseessä on todellakin meidän allekirjoituksemme.",
            "Itse asiassa tämä vertaus on hieman harhaanjohtava, koska todellisessa prosessissa, kun lähetät meille nimettömän tunnisteen ja allekirjoituksen, emme ole koskaan nähneet nimettömiä tunnuksia emmekä myöskään itse allekirjoitusta. Silti voimme varmistaa, että allekirjoitus on meidän tuottamamme.",
        ],
    ],
    "meaning" => [
        "heading" => "Mitä tämä tarkoittaa todennettujen hakujen kannalta?",
        "texts" => [
            "Käyttämällä kuvattua algoritmia me ja sinä voimme varmistaa, että joka kerta todennetuissa hauissa käytetään uutta satunnaista salasanaa, joka ei liity MetaGer-avaimeesi.",
            "Erityistä tässä algoritmissa on se, että kaikki anonymiteetin varmistavat osat suoritetaan paikallisesti laitteessasi. Kuka tahansa voi milloin tahansa tarkastella ja tarkistaa tämän suoritetun lähdekoodin.",
            "Mikä parasta, sinun ei tarvitse määrittää mitään anonyymien tunnisteiden käyttöä varten. Pelkkä selainlaajennuksemme tai Android-sovelluksemme asentaminen/käyttö riittää, jotta laitteesi käyttää anonyymejä tunnuksia kaikissa hauissa.",
        ],
    ],
    "technical-function" => [
        "heading" => "Algoritmi sen takana:",
        "texts" => [
            "Klassisessa RSA-allekirjoituksessa otamme anonyymin tunnuksen <code>m</code>, salaisen eksponentin <code>d</code> ja julkisen moduulin <code>N</code> yksityisestä avaimestamme ja luomme allekirjoituksen käyttäen <code>m^d (mod N)</code>. Haluamme kuitenkin, että <code>m</code> pysyy salassa.",
            "Siksi päätelaitteesi luo satunnaislukugeneraattorilla satunnaisluvun <code>r</code>, joka on jakajasta riippumaton suhteessa <code>N</code>. <code>r</code> ja <code>N</code> suurimman yhteisen jakajan on siis oltava <code>1</code>.",
            "Koska <code>r</code> on satunnaisluku, seuraa, että <code>m'</code> ei paljasta mitään tietoa paikallisesti tallennetusta nimettömästä tunnuksesta <code>m</code>.",
            "Palvelimemme vastaanottaa nyt päätelaitteeltasi peitetyn anonyymin tunnisteen <code>m'</code> sekä käytettävän MetaGer-avaimen. Vähennämme avaimesta merkin ja lähetämme myös peitetyn allekirjoituksen <code>s'&Congruent; (m')^d (mod N)</code> takaisin päätelaitteellesi.",
            "Päätelaitteesi voi nyt laskea todellisen kelvollisen RSA-allekirjoituksen <code>s</code> salaamattomalle nimettömälle tunnukselle: <code>s&Congruent; s' r^-1 (mod N)</code>. Tämä toimii, koska RSA-avaimilla <code>r^(e*d)&Congruent; r (mod N)</code>. Ja siis myös: <code>s &Congruent; s' * r^-1 &Congruent; (m')^d*r^-1 &Congruent; m^d*r^(e*d)*r^-1 &Congruent; m^d*r*r^-1 &Congruent; m^d (mod N)</code>.",
            "Loppulaitteesi lähettää meille nyt salaamattoman anonyymin tunnisteen ja siihen liittyvän allekirjoituksen hakua varten. Itse avainta ei enää lähetetä meille haun aikana.",
        ],
    ],
];
