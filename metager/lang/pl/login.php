<?php

/**
 * Die Anmeldeseite — /anmelden. Siehe lang/de/login.php für die Herkunft der
 * Schlüssel und dafür, was gegenüber dem Keymanager neu ist.
 */
return [
    'heading' => 'Zaloguj się do MetaGer',
    'lede' => 'Twój klucz jest twoim kontem. Nosi twoje saldo tokenów i jest wszystkim, co o tobie wiemy — żadnego imienia, żadnego adresu e-mail, żadnego hasła.',

    'key' => [
        'label' => 'Klucz lub kod logowania',
        'hint' => '36 znaków. Z urządzenia, które jest już zalogowane, działa też sześciocyfrowe hasło jednorazowe z okna przenoszenia.',
        'placeholder' => 'xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx',
    ],

    'submit' => 'Zaloguj się',
    'or' => 'lub',

    'file' => [
        'button' => 'Wybierz plik kopii zapasowej',
        'hint' => 'Plik lub obraz kodu QR zapisany przy zakładaniu klucza.',
    ],

    'qr' => [
        'button' => 'Skanuj kod QR',
        'hint' => 'Aparatem tego urządzenia, na przykład z ekranu innego.',
        'no_camera' => 'Brak dostępnej kamery.',
        'invalid' => 'Ten kod QR nie zawiera klucza.',
        'close' => 'Zamknij',
    ],

    'create' => [
        'prompt' => 'Nie masz jeszcze klucza?',
        'action' => 'Załóż klucz',
    ],

    'errors' => [
        'invalid_key' => 'To nie jest prawidłowy klucz. Klucz ma 36 znaków, a kod logowania sześć cyfr.',
        'invalid_login_code' => 'Ten kod logowania już nie obowiązuje. Trwa kilka sekund i działa tylko dla jednego logowania — poproś zalogowane urządzenie o nowy. Skrót obok twojego salda nie jest kodem logowania.',
        // Sześć znaków, które nie są kluczem. Prawie zawsze skrót obok salda —
        // zobacz KeyIdenticon.
        'key_mark' => 'Te sześć znaków to skrót twojego klucza — ten, który widnieje obok twojego salda. Nazywa konto, ale go nie otwiera. Aby się zalogować, potrzebujesz pełnego klucza z 36 znaków albo kodu logowania z urządzenia, które jest już zalogowane.',
        'invalid_key_payment_id' => 'To jest numer płatności, a nie klucz. Twój klucz ma 36 znaków i nie zaczyna się od Z.',
        'no_input' => 'Wpisz klucz albo wybierz plik kopii zapasowej.',
        'file_unreadable' => 'Z tego pliku nie udało się odczytać klucza. Powinien zawierać kod QR zapisany przy zakładaniu klucza.',
        // Der Keyserver hat nicht geantwortet, und zu viele Versuche von einer
        // Adresse. Beides sind Aussagen über uns und nicht über die Eingabe.
        'keyserver_unreachable' => 'W tej chwili nie udało się sprawdzić klucza. To nic nie mówi o twoim kluczu — spróbuj ponownie za chwilę.',
        'too_many_attempts' => 'Zbyt wiele prób z tego połączenia. Odczekaj kilka minut i spróbuj ponownie.',
    ],

    'validation' => [
        'hex' => 'Klucz zawiera tylko znaki 0–9, a–f i myślniki.',
        'uuid' => 'To nie jest prawidłowy klucz.',
        'login' => 'To nie jest ani pełny klucz, ani kod logowania.',
    ],

    'empty_key' => [
        'message' => 'Na tym kluczu nie ma salda. Jeśli tak ma być, zaloguj się — w przeciwnym razie mogła zdarzyć się literówka.',
        'entered' => 'Wpisany klucz',
        'revalidate' => 'Sprawdź wpis',
        'confirm' => 'Zaloguj się mimo to',
    ],

    'extension' => [
        'heading' => 'Rozszerzenie MetaGer do twojej przeglądarki',
        'text' => 'Pozostań zalogowany nawet po wyczyszczeniu danych przeglądarki — i mimo zalogowania pozostań <a href=":tokenlink">dowodnie anonimowy</a>.',
        'install' => 'Zainstaluj dla :browser',
        'install_generic' => 'Zainstaluj rozszerzenie',
    ],
];
