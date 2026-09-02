<?php

/**
 * Die Anmeldeseite — /anmelden. Siehe lang/de/login.php für die Herkunft der
 * Schlüssel und dafür, was gegenüber dem Keymanager neu ist.
 */
return [
    'heading' => 'Aanmelden bij MetaGer',
    'lede' => 'Uw sleutel is uw account. Hij draagt uw tokensaldo, en hij is alles wat wij van u weten — geen naam, geen e-mailadres, geen wachtwoord.',

    'key' => [
        'label' => 'Sleutel of inlogcode',
        'hint' => '36 tekens. Vanaf een apparaat dat al is ingelogd werkt ook het zescijferige eenmalige wachtwoord uit het overdrachtsvenster.',
        'placeholder' => 'xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx',
    ],

    'submit' => 'Inloggen',
    'or' => 'of',

    'file' => [
        'button' => 'Back-upbestand kiezen',
        'hint' => 'Het bestand of de QR-codeafbeelding die u bij het instellen van uw sleutel hebt opgeslagen.',
    ],

    'qr' => [
        'button' => 'QR-code scannen',
        'hint' => 'Met de camera van dit apparaat, bijvoorbeeld vanaf het scherm van een ander.',
        'no_camera' => 'Geen camera beschikbaar.',
        'invalid' => 'Die QR-code bevat geen sleutel.',
        'close' => 'Sluiten',
    ],

    'create' => [
        'prompt' => 'Nog geen sleutel?',
        'action' => 'Sleutel instellen',
    ],

    'errors' => [
        'invalid_key' => 'Dat is geen geldige sleutel. Een sleutel heeft 36 tekens, een inlogcode zes cijfers.',
        'invalid_login_code' => 'Die inlogcode geldt niet meer. Hij duurt enkele seconden en werkt voor één inlog — laat het ingelogde apparaat u een nieuwe tonen. Het kenmerk naast uw saldo is geen inlogcode.',
        // Zes tekens die geen sleutel zijn. Bijna altijd het kenmerk naast het
        // saldo — zie KeyIdenticon.
        'key_mark' => 'Die zes tekens zijn het kenmerk van uw sleutel — dat wat naast uw saldo staat. Het benoemt uw account, maar opent het niet. Om in te loggen hebt u de volledige sleutel van 36 tekens nodig, of een inlogcode van een apparaat dat al is ingelogd.',
        'invalid_key_payment_id' => 'Dat is een betalingsnummer, geen sleutel. Uw sleutel heeft 36 tekens en begint niet met een Z.',
        'no_input' => 'Voer een sleutel in of kies een back-upbestand.',
        'file_unreadable' => 'Uit dat bestand kon geen sleutel worden gelezen. Het zou de QR-code moeten bevatten die u bij het instellen van uw sleutel hebt opgeslagen.',
        // Der Keyserver hat nicht geantwortet, und zu viele Versuche von einer
        // Adresse. Beides sind Aussagen über uns und nicht über die Eingabe.
        'keyserver_unreachable' => 'We konden de sleutel op dit moment niet controleren. Dat zegt niets over uw sleutel — probeer het zo meteen opnieuw.',
        'too_many_attempts' => 'Te veel pogingen vanaf deze verbinding. Wacht een paar minuten en probeer het opnieuw.',
    ],

    'validation' => [
        'hex' => 'Een sleutel bevat alleen de tekens 0–9, a–f en streepjes.',
        'uuid' => 'Dat is geen geldige sleutel.',
        'login' => 'Dat is geen volledige sleutel en ook geen inlogcode.',
    ],

    'empty_key' => [
        'message' => 'Op deze sleutel staat geen saldo. Als dat de bedoeling is, log dan gerust in — anders is er misschien een teken verkeerd getypt.',
        'entered' => 'Ingevoerde sleutel',
        'revalidate' => 'Invoer controleren',
        'confirm' => 'Toch inloggen',
    ],

    'extension' => [
        'heading' => 'De MetaGer-extensie voor uw browser',
        'text' => 'Blijf ingelogd, zelfs na het wissen van uw browsergegevens — en blijf ondanks het inloggen <a href=":tokenlink">aantoonbaar anoniem</a>.',
        'install' => 'Installeren voor :browser',
        'install_generic' => 'De extensie installeren',
    ],

    'no_cookies_notice' => 'Uw browser bewaart het inlogcookie niet. MetaGer kan uw sleutel alleen onthouden zolang het adres van deze pagina die nog bevat — voeg deze pagina toe aan uw bladwijzers, of installeer de MetaGer-extensie om zonder cookies ingelogd te blijven.',
];
