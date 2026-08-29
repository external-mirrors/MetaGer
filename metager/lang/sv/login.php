<?php

/**
 * Die Anmeldeseite — /anmelden. Siehe lang/de/login.php für die Herkunft der
 * Schlüssel und dafür, was gegenüber dem Keymanager neu ist.
 */
return [
    'heading' => 'Logga in på MetaGer',
    'lede' => 'Din nyckel är ditt konto. Den bär ditt polettsaldo, och den är allt vi vet om dig — inget namn, ingen e-postadress, inget lösenord.',

    'key' => [
        'label' => 'Nyckel eller inloggningskod',
        'hint' => '36 tecken. Från en enhet som redan är inloggad fungerar också det sexsiffriga engångslösenordet från överföringsdialogen.',
        'placeholder' => 'xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx',
    ],

    'submit' => 'Logga in',
    'or' => 'eller',

    'file' => [
        'button' => 'Välj säkerhetskopia',
        'hint' => 'Filen eller QR-kodbilden du sparade när du skapade nyckeln.',
    ],

    'qr' => [
        'button' => 'Skanna QR-kod',
        'hint' => 'Med den här enhetens kamera, till exempel från en annan enhets skärm.',
        'no_camera' => 'Ingen kamera tillgänglig.',
        'invalid' => 'Den QR-koden innehåller ingen nyckel.',
        'close' => 'Stäng',
    ],

    'create' => [
        'prompt' => 'Har du ingen nyckel än?',
        'action' => 'Skapa en nyckel',
    ],

    'errors' => [
        'invalid_key' => 'Det är inte en giltig nyckel. En nyckel har 36 tecken, en inloggningskod sex siffror.',
        'invalid_login_code' => 'Den inloggningskoden gäller inte längre. Den varar några sekunder och fungerar bara för en enda inloggning — låt den inloggade enheten visa dig en ny. Det korta märket bredvid ditt saldo är ingen inloggningskod.',
        // Sex tecken som inte är någon nyckel. Nästan alltid det korta märket
        // bredvid saldot — se KeyIdenticon.
        'key_mark' => 'De sex tecknen är din nyckels korta märke — det som står bredvid ditt saldo. Det namnger ditt konto, men öppnar det inte. För att logga in behöver du hela nyckeln på 36 tecken eller en inloggningskod från en enhet som redan är inloggad.',
        'invalid_key_payment_id' => 'Det är ett betalningsnummer, inte en nyckel. Din nyckel har 36 tecken och börjar inte med Z.',
        'no_input' => 'Ange en nyckel eller välj en säkerhetskopia.',
        'file_unreadable' => 'Ingen nyckel kunde läsas ur den filen. Den ska innehålla QR-koden du sparade när du skapade nyckeln.',
    ],

    'validation' => [
        'hex' => 'En nyckel innehåller bara tecknen 0–9, a–f och bindestreck.',
        'uuid' => 'Det är inte en giltig nyckel.',
        'login' => 'Det är varken en fullständig nyckel eller en inloggningskod.',
    ],

    'empty_key' => [
        'message' => 'Det finns inget saldo på den här nyckeln. Om det är väntat, logga gärna in — annars kan ett tecken ha blivit fel.',
        'entered' => 'Angiven nyckel',
        'revalidate' => 'Kontrollera inmatningen',
        'confirm' => 'Logga in ändå',
    ],

    'extension' => [
        'heading' => 'MetaGer-tillägget för din webbläsare',
        'text' => 'Förbli inloggad även efter att du rensat dina webbläsardata — och förbli <a href=":tokenlink">bevisbart anonym</a> trots att du är inloggad.',
        'install' => 'Installera för :browser',
        'install_generic' => 'Installera tillägget',
    ],
];
