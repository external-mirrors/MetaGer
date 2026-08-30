<?php

/**
 * Die Seite zum Erstellen eines Schlüssels — /schluessel-erstellen. Siehe
 * lang/de/key-create.php für die Herkunft der Schlüssel und dafür, was
 * gegenüber dem Keymanager neu ist.
 */
return [
    'heading' => 'Luo avain',
    'lede' => 'Avaimesi on tilisi. Se kantaa kuponkisaldoasi, ja se on kaikki, mitä sinusta tiedämme — ei nimeä, ei sähköpostiosoitetta, ei salasanaa. Se tarkoittaa myös, että jos hukkaat sen, sen saldo on menetetty.',

    'existing' => [
        'text' => 'Onko sinulla ollut MetaGer-avain aiemmin? Kirjaudu sillä sisään sen sijaan, että loisit uuden — uusi avain saa oman erillisen saldonsa, ja vanha saldo jää vanhaan avaimeen.',
        'action' => 'Kirjaudu sisään olemassa olevalla avaimella',
    ],

    'offer' => [
        'text' => 'Yksi painallus, ja sinulla on avain. Ei lomaketta, ei tunnuksia: MetaGer arpoo merkkijonon, joka ei kuulu vielä kenellekään.',
        'button' => 'Luo avain nyt',
    ],

    'working' => 'Hetki: arvomme sinulle uutta avainta …',

    'key' => [
        'label' => 'Uusi avaimesi',
        'hint' => '36 merkkiä. Niillä kirjaudut sisään jokaisella muulla laitteella.',
    ],

    'copy' => [
        'action' => 'Kopioi avain',
        'done' => 'Kopioitu',
    ],

    'save' => [
        'heading' => 'Säilytä se jossakin',
        'text' => 'Niin kauan kuin tämä selain säilyttää evästeen, pysyt kirjautuneena. Jos se menettää sen — uusi laite, tyhjennetyt selaustiedot —, tämä avain on ainoa tie takaisin.',

        'qr' => [
            'alt' => 'QR-koodi, joka johtaa avaimeesi',
            'action' => 'Tallenna kuvana',
            'hint' => 'Kuva, jota kirjautumislomake pyytää. Voit myöhemmin ladata sen sinne tai kuvata sen kameralla.',
        ],

        'url' => [
            'label' => 'Kirjanmerkki',
            'action' => 'Kopioi URL-osoite',
            'hint' => 'Tämän osoitteen avaaminen ottaa avaimen uudelleen käyttöön yhdessä tämän selaimen asetusten kanssa.',
        ],

        'no_cookies' => 'Tämä selain ei tallenna evästeitä MetaGerille. Ilman evästettä et pysy kirjautuneena — silloin yllä oleva osoite on tapa kirjautua ennen hakua. Voit myös lisätä sen selaimeesi hakukoneeksi.',
    ],

    'continue' => 'Seuraavaksi: lisää saldoa',
    'continue_hint' => 'Uudella avaimella ei ole vielä saldoa. Seuraavassa vaiheessa valitset kuponkipaketin.',

    'errors' => [
        'keyserver_unreachable' => 'Avainta ei juuri nyt voitu luoda. Se on meidän vikamme eikä sinun — yritä hetken päästä uudelleen.',
        'too_many_attempts' => 'Tästä yhteydestä on juuri luotu hyvin monta avainta. Odota muutama minuutti ja lataa sivu sitten uudelleen.',
        'no_key' => 'Avain katosi matkalla — niin käy, kun sivu on ollut kauan auki. Tässä on uusi.',
    ],
];
