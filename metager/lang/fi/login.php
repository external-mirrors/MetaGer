<?php

/**
 * Die Anmeldeseite — /anmelden. Siehe lang/de/login.php für die Herkunft der
 * Schlüssel und dafür, was gegenüber dem Keymanager neu ist.
 */
return [
    'heading' => 'Kirjaudu sisään MetaGeriin',
    'lede' => 'Avaimesi on tilisi. Se kantaa kuponkisaldoasi, ja se on kaikki, mitä sinusta tiedämme — ei nimeä, ei sähköpostiosoitetta, ei salasanaa.',

    'key' => [
        'label' => 'Avain tai kirjautumiskoodi',
        'hint' => '36 merkkiä. Jo kirjautuneelta laitteelta käy myös siirtoikkunan kuusinumeroinen kertakäyttösalasana.',
        'placeholder' => 'xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx',
    ],

    'submit' => 'Kirjaudu sisään',
    'or' => 'tai',

    'file' => [
        'button' => 'Valitse varmuuskopiotiedosto',
        'hint' => 'Tiedosto tai QR-koodikuva, jonka tallensit avainta luodessasi.',
    ],

    'qr' => [
        'button' => 'Skannaa QR-koodi',
        'hint' => 'Tämän laitteen kameralla, esimerkiksi toisen laitteen näytöltä.',
        'no_camera' => 'Kameraa ei ole käytettävissä.',
        'invalid' => 'Tuo QR-koodi ei sisällä avainta.',
        'close' => 'Sulje',
    ],

    'create' => [
        'prompt' => 'Eikö sinulla ole vielä avainta?',
        'action' => 'Luo avain',
    ],

    'errors' => [
        'invalid_key' => 'Tuo ei ole kelvollinen avain. Avaimessa on 36 merkkiä, kirjautumiskoodissa kuusi numeroa.',
        'invalid_login_code' => 'Tuo kirjautumiskoodi ei ole enää voimassa. Se kestää muutaman sekunnin ja toimii vain yhteen kirjautumiseen — pyydä kirjautuneelta laitteelta uusi. Saldosi vieressä oleva lyhenne ei ole kirjautumiskoodi.',
        // Kuusi merkkiä, jotka eivät ole avain. Lähes aina saldon vieressä
        // näkyvä lyhenne — katso KeyIdenticon.
        'key_mark' => 'Nämä kuusi merkkiä ovat avaimesi lyhenne — se, joka näkyy saldosi vieressä. Se nimeää tilisi, mutta ei avaa sitä. Kirjautumiseen tarvitset koko 36-merkkisen avaimen tai kirjautumiskoodin laitteelta, joka on jo kirjautunut sisään.',
        'invalid_key_payment_id' => 'Tuo on maksunumero, ei avain. Avaimessasi on 36 merkkiä eikä se ala Z-kirjaimella.',
        'no_input' => 'Syötä avain tai valitse varmuuskopiotiedosto.',
        'file_unreadable' => 'Tuosta tiedostosta ei voitu lukea avainta. Sen pitäisi sisältää QR-koodi, jonka tallensit avainta luodessasi.',
        // Der Keyserver hat nicht geantwortet, und zu viele Versuche von einer
        // Adresse. Beides sind Aussagen über uns und nicht über die Eingabe.
        'keyserver_unreachable' => 'Avainta ei juuri nyt voitu tarkistaa. Se ei kerro avaimestasi mitään — yritä hetken kuluttua uudelleen.',
        'too_many_attempts' => 'Liian monta yritystä tästä yhteydestä. Odota muutama minuutti ja yritä uudelleen.',
    ],

    'validation' => [
        'hex' => 'Avain sisältää vain merkit 0–9, a–f ja väliviivat.',
        'uuid' => 'Tuo ei ole kelvollinen avain.',
        'login' => 'Tuo ei ole kokonainen avain eikä kirjautumiskoodi.',
    ],

    'empty_key' => [
        'message' => 'Tällä avaimella ei ole saldoa. Jos näin pitääkin olla, kirjaudu sisään — muuten jokin merkki on ehkä kirjoitettu väärin.',
        'entered' => 'Syötetty avain',
        'revalidate' => 'Tarkista syöte',
        'confirm' => 'Kirjaudu silti sisään',
    ],

    'extension' => [
        'heading' => 'MetaGer-laajennus selaimeesi',
        'text' => 'Pysy kirjautuneena selaintietojen tyhjentämisen jälkeenkin — ja pysy kirjautuneenakin <a href=":tokenlink">todistettavasti nimettömänä</a>.',
        'install' => 'Asenna selaimeen :browser',
        'install_generic' => 'Asenna laajennus',
    ],
];
