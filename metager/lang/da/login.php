<?php

/**
 * Die Anmeldeseite — /anmelden. Siehe lang/de/login.php für die Herkunft der
 * Schlüssel und dafür, was gegenüber dem Keymanager neu ist.
 */
return [
    'heading' => 'Log ind på MetaGer',
    'lede' => 'Din nøgle er din konto. Den bærer din tokensaldo, og den er alt, hvad vi ved om dig — intet navn, ingen e-mailadresse, ingen adgangskode.',

    'key' => [
        'label' => 'Nøgle eller login-kode',
        'hint' => '36 tegn. Fra en enhed, der allerede er logget ind, virker den sekscifrede engangskode fra overførselsdialogen også.',
        'placeholder' => 'xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx',
    ],

    'submit' => 'Log ind',
    'or' => 'eller',

    'file' => [
        'button' => 'Vælg backup-fil',
        'hint' => 'Den fil eller det QR-kodebillede, du gemte, da du oprettede nøglen.',
    ],

    'qr' => [
        'button' => 'Scan QR-kode',
        'hint' => 'Med denne enheds kamera, for eksempel fra en anden enheds skærm.',
        'no_camera' => 'Intet kamera tilgængeligt.',
        'invalid' => 'Den QR-kode indeholder ingen nøgle.',
        'close' => 'Luk',
    ],

    'create' => [
        'prompt' => 'Har du ingen nøgle endnu?',
        'action' => 'Opret en nøgle',
    ],

    'errors' => [
        'invalid_key' => 'Det er ikke en gyldig nøgle. En nøgle har 36 tegn, en login-kode har seks cifre.',
        'invalid_login_code' => 'Den login-kode gælder ikke længere. Den varer få sekunder og virker kun til én enkelt login — få den enhed, der er logget ind, til at vise dig en ny. Det korte mærke ved siden af din saldo er ikke en login-kode.',
        // Seks tegn, der ikke er en nøgle. Næsten altid det korte mærke ved
        // siden af saldoen — se KeyIdenticon.
        'key_mark' => 'De seks tegn er din nøgles korte mærke — det, der står ved siden af din saldo. Det navngiver din konto, men åbner den ikke. For at logge ind skal du bruge hele nøglen på 36 tegn eller en login-kode fra en enhed, der allerede er logget ind.',
        'invalid_key_payment_id' => 'Det er et betalingsnummer, ikke en nøgle. Din nøgle har 36 tegn og begynder ikke med Z.',
        'no_input' => 'Indtast en nøgle, eller vælg en backup-fil.',
        'file_unreadable' => 'Der kunne ikke læses nogen nøgle fra den fil. Den skal indeholde den QR-kode, du gemte, da du oprettede nøglen.',
        // Der Keyserver hat nicht geantwortet, und zu viele Versuche von einer
        // Adresse. Beides sind Aussagen über uns und nicht über die Eingabe.
        'keyserver_unreachable' => 'Vi kunne ikke tjekke nøglen lige nu. Det siger intet om din nøgle — prøv igen om et øjeblik.',
        'too_many_attempts' => 'For mange forsøg fra denne forbindelse. Vent et par minutter, og prøv igen.',
    ],

    'validation' => [
        'hex' => 'En nøgle indeholder kun tegnene 0–9, a–f og bindestreger.',
        'uuid' => 'Det er ikke en gyldig nøgle.',
        'login' => 'Det er hverken en komplet nøgle eller en login-kode.',
    ],

    'empty_key' => [
        'message' => 'Der er ingen saldo på denne nøgle. Hvis det er forventet, så log endelig ind — ellers er et tegn måske skrevet forkert.',
        'entered' => 'Indtastet nøgle',
        'revalidate' => 'Tjek indtastningen',
        'confirm' => 'Log ind alligevel',
    ],

    'extension' => [
        'heading' => 'MetaGer-udvidelsen til din browser',
        'text' => 'Forbliv logget ind, selv efter at du har slettet dine browserdata — og forbliv <a href=":tokenlink">beviseligt anonym</a>, selv om du er logget ind.',
        'install' => 'Installer til :browser',
        'install_generic' => 'Installer udvidelsen',
    ],
];
