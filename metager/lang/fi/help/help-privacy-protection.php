<?php
return [
    'title' => 'MetaGer - Apua',
    'backarrow' => 'Takaisin',
    'tor' => [
        'title' => 'TOR Piilotettu palvelu <a title="For easy help, click here" href="/hilfe/easy-language/privacy-protection#eh-torhidden" ><img class="easy-help-icon lm-only" src="/img/help-questionmark-icon-lm.svg"/><img class="easy-help-icon dm-only" src="/img/help-questionmark-icon-dm.svg"/></a>',
        '1' => 'MetaGer on piilottanut IP-osoitteet ja jättänyt ne tallentamatta jo vuosien ajan. Osoitteet ovat kuitenkin tilapäisesti näkyvissä MetaGer-palvelimella haun ollessa käynnissä: jos MetaGer vaarantuu, hyökkääjä voi lukea ja tallentaa osoitteesi. Täyttääkseen korkeimmat turvallisuusvaatimukset käytämme MetaGer-instanssia Tor-verkossa: MetaGer TOR Hidden Service -palvelu, johon pääsee osoitteesta: <a href="/tor/" target="_blank" rel="noopener">https://metager.de/tor/</a>. Sen käyttämiseen tarvitset erityisen selaimen, jonka voit ladata osoitteesta <a href="https://www.torproject.org/" target="_blank" rel="noopener">https://www.torproject.org/</a>.',
        '2' => 'Voit käyttää MetaGeriä Tor-selaimessa osoitteessa: http://metagerv65pwclop2rsfzg4jwowpavpwd6grhhlvdgsswvo6ii4akgyd.onion .',
    ],
    'proxy' => [
        'title' => 'Avaa nimettömästi (SafeBrowse) <a title="For easy help, click here" href="/hilfe/easy-language/privacy-protection#eh-proxy" ><img class="easy-help-icon lm-only" src="/img/help-questionmark-icon-lm.svg"/><img class="easy-help-icon dm-only" src="/img/help-questionmark-icon-dm.svg"/></a>',
        'intro' => '”Avaa nimettömästi” -toiminnolla vierailet verkkosivustolla ilman, että se näkee laitettasi. Sivu avataan selaimessa, joka toimii palvelimillamme: SafeBrowsessa. Sivusto on tekemisissä vain tämän selaimen kanssa eikä saa tietää IP-osoitettasi eikä mitään laitteestasi, selaimestasi tai evästeistäsi.',
        'use' => [
            'title' => 'Näin avaat sivun nimettömästi',
            'text' => [
                'Napsauta tuloksen kohdalla ”Avaa nimettömästi” (kilpikuvake). Sivu avautuu uuteen välilehteen.',
                'Jos haluat avata kaikki tulokset nimettömästi, ota asetuksista käyttöön ”Avaa tulokset anonyymisti”. Silloin jokainen tuloksen linkki kulkee SafeBrowsen kautta, ja ”Avaa suoraan” avaa sivun ilman SafeBrowsea.',
            ],
        ],
        'session' => [
            'title' => 'Istunto',
            'text' => [
                'Ensimmäinen nimettömästi avattu tulos aloittaa istunnon. Jokainen seuraava avautuu samaan istuntoon omaan välilehteensä. Siellä selaat tavalliseen tapaan: seuraat linkkejä, täytät lomakkeita, kirjoitat uuden osoitteen tai hakusanan osoiteriville. Pysyt samalla suojattuna.',
                'Istunto maksaa tokeneita MetaGer-avaimestasi; kuinka monta ja kuinka pitkäksi ajaksi, näytetään ennen aloitusta. Niin kauan kuin käytät istuntoa, sitä jatketaan automaattisesti. Kun lopetat sen käytön, se päättyy itsestään pian sen jälkeen, tai voit lopettaa sen valinnalla ”Sulje istunto”.',
                'Kun istunto päättyy, palvelimillamme oleva selain poistetaan kaikkine sisältöineen: vieraillut sivut, evästeet, kirjautumiset ja ladatut tiedostot. Seuraava istunto alkaa uudella, tyhjällä selaimella. Tallenna siksi ladatut tiedostot laitteellesi kohdasta ”Lataukset” ennen sitä.',
            ],
        ],
        'views' => [
            'title' => 'Kaksi näkymää',
            'text' => [
                '”Sivu” näyttää kopion sivustosta ilman sen skriptejä. Luet, vierität, haet ja kopioit siinä kuten millä tahansa sivulla; napsautuksesi ja syötteesi menevät palvelimillamme olevaan selaimeen. ”Koko selain” näyttää tämän selaimen kuvana kaikkine välilehtineen. Siellä kaikki toimii, myös videot ja sivut, joita ”Sivu” ei pysty näyttämään. Vaihdat näkymien välillä yläpalkista.',
            ],
        ],
        'nojs' => [
            'title' => 'Ilman JavaScriptiä',
            'text' => [
                'Vaikka JavaScript olisi poistettu käytöstä selaimessasi, ”Avaa nimettömästi” avaa sivun SafeBrowsessa. Näet silloin kopion sivusta, jossa linkit, painikkeet ja lomakkeet toimivat. Se ladataan uudelleen jokaisen napsautuksen jälkeen, mutta se ei muutu itsestään. Videot, sivun valintaikkunat ja tiedostojen lähettäminen eivät toimi tällä tavalla. Jos selaimesi estää evästeet, yhdeltä tulossivulta avaamasi tulokset päätyvät silti samaan istuntoon, myös jos jatkat hakemista sieltä.',
            ],
        ],
        'limits' => [
            'title' => 'Mitä ”Avaa nimettömästi” ei tee',
            'text' => [
                'Sivusto ei tunnista sinua laitteestasi, mutta kyllä siitä, mitä itse kerrot sille: jos kirjaudut sisään tai annat henkilötietoja, se tietää, kuka olet. Jos et halua näyttäytyä myöskään MetaGerille, käytä MetaGeria Torin kautta (katso yllä).',
            ],
        ],
    ],
    'content' => [
        'title' => 'Kyseenalainen sisältö / Nuorten suojelu <a title="For easy help, click here" href="/help/easy-language/privacy-protection#eh-content" ><img class="easy-help-icon lm-only" src="/img/help-questionmark-icon-lm.svg"/><img class="easy-help-icon dm-only" src="/img/help-questionmark-icon-dm.svg"/></a>',
        'explanation' => [
            '1' => 'Olen saanut "osumia", jotka eivät ole mielestäni vain ärsyttäviä vaan sisältävät mielestäni myös laitonta sisältöä!',
            '2' => 'Jos löydät internetistä jotain, joka on mielestäsi laitonta tai alaikäisille haitallista, voit ottaa yhteyttä osoitteeseen <a href="mailto:hotline@jugendschutz.net" target="_blank" rel="noopener">hotline@jugendschutz.net</a> sähköpostitse tai käydä osoitteessa <a href="http://www.jugendschutz.net/" target="_blank" rel="noopener">www.jugendschutz.net</a> ja täyttää siellä olevan valituslomakkeen. On hyödyllistä kirjoittaa lyhyesti, mitä pidät sopimattomana ja miten olet törmännyt kyseiseen sisältöön. Voit myös ilmoittaa kyseenalaisesta sisällöstä suoraan meille. Lähetä tätä varten sähköpostia nuorisosuojavastaavalle (<a href="mailto:jugendschutz@metager.de" target="_blank" rel="noopener">jugendschutz@metager.de</a>).',
        ],
    ],
    'maps' => [
        'title' => 'MetaGer-kartat',
        '1' => 'Yksityisyyden suojaaminen maailmanlaajuisten datajättiläisten aikakaudella on myös saanut meidät kehittämään <a href="https://maps.metager.de" target="_blank">https://maps.metager.de</a>: (tietojemme mukaan) ainoa reittisuunnittelija, joka tarjoaa täydet toiminnot selaimen ja sovelluksen kautta tallentamatta käyttäjän sijaintia. Kaikki tämä on todennettavissa, koska ohjelmistomme on avointa lähdekoodia. Suosittelemme maps.metager.de:n käyttöön nopeaa sovellusversiotamme. Voit ladata sovelluksemme osoitteesta <a href="/app" target="_blank">täältä</a> (tai tietysti myös Play Storesta).',
        '2' => 'Tätä karttatoimintoa voi käyttää myös MetaGer-hakupalvelusta (ja päinvastoin). Kun olet hakenut termiä MetaGerissä, näet oikeassa yläkulmassa uuden hakukohdan \'Kartat\'. Klikkaamalla sitä pääset vastaavaan karttaan.',
        '3' => 'Kun kartta on ladattu, siinä näkyvät MetaGerin löytämät pisteet (POI = Points of Interest), jotka on lueteltu myös oikeassa sarakkeessa. Zoomattaessa tämä luettelo mukautuu karttaosaan. Kun viet hiiren osoittimen päälle kartalla tai luettelossa, vastaava kohde korostuu. Napsauttamalla \'Tiedot\' saat lisätietoja kyseisestä pisteestä alla olevasta tietokannasta.',
    ],
    'easy-help' => 'Klikkaamalla symbolia <a title="For easy help, click here" href="/hilfe/easy-language/privacy-protection" ><img class="easy-help-icon lm-only" src="/img/help-questionmark-icon-lm.svg"/><img class="easy-help-icon dm-only" src="/img/help-questionmark-icon-dm.svg"/></a> pääset yksinkertaistettuun versioon ohjeesta.',
    'privacy' => [
        'title' => "Anonymiteetti ja tietoturva",
        '1' => 'Seurantaevästeet, istuntotunnisteet ja IP-osoitteet <a title="For easy help, click here" href="/hilfe/easy-language/privacy-protection#eh-tracking" ><img class="easy-help-icon lm-only" src="/img/help-questionmark-icon-lm.svg"/><img class="easy-help-icon dm-only" src="/img/help-questionmark-icon-dm.svg"/></a>',
        '3' => "Lisätietoja on jäljempänä. Toiminnot löytyvät navigointipalkin kohdasta 'Palvelut'.",
        '2' => 'Mitään näistä ei käytetä, tallenneta, säilytetä tai muutoin käsitellä täällä MetaGerissä (poikkeus: lyhytaikainen tallennus hakkerointi- ja bottihyökkäyksiltä suojaamiseksi). Koska pidämme tätä aihetta erittäin tärkeänä, olemme myös luoneet keinoja, jotka auttavat sinua saavuttamaan korkeimman turvallisuustason: MetaGer TOR Hidden Service ja anonyymi selaimemme.',
    ],
];
