<?php
return [
    'title' => 'Hakutuloksen poistaminen – MetaGer',
    'meta_description' => 'Näin poistat hakutuloksen: oikeus tulla unohdetuksi, vanhentunut sisältö, tekijänoikeudet. Missä pyyntö tehoaa: Google, Brave ja muut.',
    'heading' => 'Hakutuloksen poistaminen',
    'lead' => 'MetaGer on metahakukone: meillä ei ole omaa hakemistoa, vaan näytämme, mitä muut hakukoneet löytävät. Siksi tuloksen voi todella poistaa vain sieltä, mistä se on peräisin: verkkosivustolta ja niistä hakukoneista, joiden tuloksia MetaGer näyttää. Tämä sivu kertoo, mistä.',
    'toc' => [
        'label' => 'Tällä sivulla',
    ],
    'website' => [
        'heading' => 'Ota yhteyttä itse verkkosivustoon',
        'text' => 'Tämä on ainoa keino, joka toimii kaikkialla. Kun sivu on poistettu tai sitä on muutettu, jokainen hakukone poistaa tai päivittää sen seuraavalla käynnillään, myös MetaGer, eikä sinun tarvitse pyytää sitä jokaiselta erikseen.',
        'tips' => [
            'Useimmat verkkosivustot ilmoittavat yhteysosoitteen yhteystiedoissaan tai tietosuojaselosteessaan. Pyydä niitä poistamaan sisältö, korjaamaan se tai ainakin poistamaan nimesi.',
            'Henkilötietojen osalta EU:ssa toimivien verkkosivustojen on vastattava tietosuoja-asetuksen 17 artiklan mukaiseen pyyntöön (oikeus tietojen poistamiseen).',
            'Jos ylläpidät verkkosivustoa itse: poista sivu niin, että se vastaa koodilla ”404” tai ”410”, tai merkitse se määreellä ”noindex”. Hakukoneet poistavat sen sitten itsestään.',
        ],
        'sources' => 'Lähteestä :sources tulevien tulosten kohdalla tämä on ainoa keino.',
    ],
    'kind' => [
        'heading' => 'Mistä on kyse?',
        'intro' => 'Jos verkkosivusto ei auta tai et voi odottaa sitä, hakukoneilla on omat menettelynsä. Sopiva menettely riippuu siitä, mitä haluat saavuttaa.',
        'personal_data' => [
            'summary' => 'Henkilötiedot: oikeus tulla unohdetuksi',
            'text' => 'Nimelläsi löytyvä tulos johtaa sinua koskeviin tietoihin, jotka ovat virheellisiä, vanhentuneita, epäolennaisia tai liiallisia. Tietosuoja-asetuksen 17 artiklan nojalla voit pyytää hakukonetta olemaan näyttämättä tulosta nimelläsi tehdyissä hauissa. Hakukone punnitsee etuasi suhteessa yleisön etuun saada tieto, joten kaikki pyynnöt eivät onnistu. Alla olevissa korteissa tämä on kohdassa ”Henkilötiedot”.',
        ],
        'outdated' => [
            'summary' => 'Vanhentunut sisältö',
            'text' => 'Verkkosivusto on jo poistanut sivun tai muuttanut sitä, mutta tulos näyttää yhä vanhan otsikon tai tekstin. Tämä korjautuu itsestään hakukoneen seuraavalla käynnillä; useimmilta voit myös pyytää aikaisempaa päivitystä. Alla olevissa korteissa tämä on kohdassa ”Vanhentunut sisältö”.',
        ],
        'legal' => [
            'summary' => 'Tekijänoikeudet ja muut oikeudelliset syyt',
            'text' => 'Tulos loukkaa tekijänoikeuttasi tai muita oikeuksiasi. Hakukoneet ottavat tällaiset ilmoitukset vastaan omilla oikeudellisilla lomakkeillaan; yleensä ilmoituksen voi tehdä vain oikeudenhaltija tai tämän puolesta toimiva henkilö. Alla olevissa korteissa tämä on kohdassa ”Tekijänoikeudet ja juridiikka”.',
        ],
        'illegal' => [
            'summary' => 'Laiton sisältö',
            'text' => 'Lapsen seksuaalista hyväksikäyttöä esittävä aineisto, alaikäisille haitallinen sisältö, kiihottaminen kansanryhmää vastaan ja vastaava: ilmoita niistä ensin ilmoituspalveluun. Se puuttuu itse sisältöön sen palveluntarjoajan kautta ja tekee yhteistyötä poliisin kanssa. Jos haluat lisäksi, ettei MetaGer enää näytä tällaista tulosta, lähetä meille vain sen osoite (URL) tekstinä sähköpostitse. Älä koskaan lähetä meille itse sisältöä, älä myöskään kuvakaappauksia tai liitteitä.',
            'hotlines' => 'Ilmoituspalvelut:',
        ],
    ],
    'providers' => [
        'heading' => 'Pyydä poistoa hakukoneilta, joita MetaGer näyttää',
        'text' => 'MetaGer ei itse käy läpi verkkoa. Kysymme alla olevilta hakukoneilta silloin, kun teet haun, näytämme niiden palauttamat tulokset ja säilytämme niiden vastauksen välimuistissa muutaman tunnin. Kun jokin niistä poistaa tuloksen, se puuttuu sen jälkeen myös siitä, mitä MetaGer siltä saa.',
        'which' => 'Se, mistä hakukoneesta tulos on peräisin, näkyy tuloksen alla MetaGerin tulossivulla. Jos tulos on peräisin useammasta, pyydä poistoa jokaiselta niistä.',
        'shows' => 'MetaGerissa kohdassa:',
        'kinds' => [
            'personal_data' => 'Henkilötiedot',
            'outdated' => 'Vanhentunut sisältö',
            'legal' => 'Tekijänoikeudet ja juridiikka',
        ],
        'no_form' => 'Ei lomaketta, ota yhteyttä: :name',
        'contact' => 'Ota yhteyttä: :name',
        'servers' => 'Mastodon-palvelimet, joilta TootNews hakee',
        'google' => 'MetaGer saa Googlen tulokset Serper-palvelun kautta. Mitä Google poistaa hakemistostaan, puuttuu sen jälkeen myös MetaGerista.',
        'brave' => 'Brave Searchilla on oma hakemisto. Henkilötietoja koskevat pyynnöt lähetetään sähköpostilla osoitteeseen privacy@brave.com otsikolla ”RTBF request”; linkitetyllä sivulla kerrotaan, mitä viestissä pitää olla.',
        'mojeek' => 'Mojeekilla on oma hakemisto, eikä se muokkaa tuloksiaan käsin. Oikeudellisista syistä Mojeek poistaa sivuja pyynnöstä; kirjoita sen yhteydenottosivun kautta ja ilmoita tarkat osoitteet.',
        'staan' => 'Staan on European Search Perspectiven hakurajapinta. Staan ottaa henkilötietoja koskevat pyynnöt vastaan omalla lomakkeellaan; täytä kaikki kentät ja liitä todisteet. Poisto koskee vain Staanin hakemistoa.',
        'polias' => 'Poliasilla on oma hakemisto, eikä sillä ole lomaketta. Kirjoita osoitteeseen hallo@polias.eu ja ilmoita sivun osoite ja syy.',
        'pixabay' => 'Pixabay tarjoaa kuvat itse. Käytä ilmoituslomaketta tai ilmoituspainiketta kuvan sivulla Pixabayssa.',
        'suma' => 'TootNewsia ylläpitää SUMA-EV, MetaGerin taustalla oleva yhdistys, ja se hakee julkaisuja muutamalta Mastodon-palvelimelta. Jokaisen tuloksen kohdalla näkyy, miltä palvelimelta se on; pyydä palvelimen ylläpitäjiä poistamaan tai piilottamaan julkaisu.',
        'nolm' => 'Nolm on SUMA-EV:n oma hakukone, erillään MetaGerista. Nolm hakee Open Web Indexistä. Pyydä poistoa Open Web Indexin poistolomakkeella: mikä siellä poistetaan, katoaa myös Nolmista.',
        'website' => 'BASE näyttää, mitä sen hakemat arkistot sisältävät. Kun arkisto poistaa tietueen, se katoaa myös BASEsta.',
    ],
];
