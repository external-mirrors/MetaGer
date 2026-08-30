<?php

/**
 * Die Seite zum Erstellen eines Schlüssels — /schluessel-erstellen. Siehe
 * lang/de/key-create.php für die Herkunft der Schlüssel und dafür, was
 * gegenüber dem Keymanager neu ist.
 */
return [
    'heading' => 'Utwórz klucz',
    'lede' => 'Twój klucz jest twoim kontem. Nosi twoje saldo tokenów i jest wszystkim, co o tobie wiemy — żadnego imienia, żadnego adresu e-mail, żadnego hasła. To znaczy też: kto go zgubi, traci zgromadzone na nim saldo.',

    'existing' => [
        'text' => 'Miałeś już kiedyś klucz MetaGer? Zaloguj się nim zamiast tworzyć nowy — nowy klucz dostaje własne, oddzielne saldo, a stare zostaje na starym kluczu.',
        'action' => 'Zaloguj się istniejącym kluczem',
    ],

    'offer' => [
        'text' => 'Jedno naciśnięcie przycisku i masz klucz. Żadnego formularza, żadnych danych logowania: MetaGer losuje ciąg znaków, który jeszcze do nikogo nie należy.',
        'button' => 'Utwórz klucz teraz',
    ],

    'working' => 'Chwileczkę: losujemy dla ciebie nowy klucz …',

    /**
     * The mark that sits in the corner of every page from here on.
     *
     * Derived from the key and stored nowhere
     * ({@see \App\Authentication\KeyIdenticon}). It is here because a mark you
     * are meant to recognise has to be shown the first time — otherwise it is
     * just a coloured square the second time.
     */
    'identity' => 'Po tym rozpoznasz swoje konto: od teraz ten znak znajduje się w prawym górnym rogu każdej strony.',

    'key' => [
        'label' => 'Twój nowy klucz',
        'hint' => '36 znaków. To nimi logujesz się na każdym kolejnym urządzeniu.',
    ],

    'copy' => [
        'action' => 'Kopiuj klucz',
        'done' => 'Skopiowano',
    ],

    'save' => [
        'heading' => 'Zachowaj go w bezpiecznym miejscu',
        'text' => 'Dopóki ta przeglądarka zachowuje ciasteczko, pozostajesz zalogowany. Jeśli je straci — nowe urządzenie, wyczyszczone dane przeglądania — ten klucz jest jedyną drogą powrotną.',

        'qr' => [
            'alt' => 'Kod QR prowadzący do twojego klucza',
            'action' => 'Zapisz jako obraz',
            'hint' => 'Obraz, o który prosi formularz logowania. Możesz go tam później wgrać albo sfotografować aparatem.',
        ],

        'url' => [
            'label' => 'Zakładka',
            'action' => 'Kopiuj adres URL',
            'hint' => 'Otwarcie tego adresu ponownie konfiguruje klucz razem z ustawieniami tej przeglądarki.',
        ],

        'no_cookies' => 'Ta przeglądarka nie zapisuje ciasteczek dla MetaGer. Bez ciasteczka nie pozostaniesz zalogowany — wtedy powyższy adres jest sposobem na zalogowanie się przed wyszukiwaniem. Możesz go też dodać w przeglądarce jako wyszukiwarkę.',
    ],

    'continue' => 'Dalej: doładuj saldo',
    'continue_hint' => 'Nowy klucz nie ma jeszcze salda. W następnym kroku wybierasz pakiet tokenów.',

    'errors' => [
        'keyserver_unreachable' => 'Właśnie nie udało się utworzyć klucza. To nasza wina, nie twoja — spróbuj za chwilę jeszcze raz.',
        'too_many_attempts' => 'Z tego łącza utworzono właśnie bardzo wiele kluczy. Odczekaj kilka minut i wtedy odśwież stronę.',
        'no_key' => 'Klucz zgubił się po drodze — tak bywa, gdy strona długo stoi otwarta. Oto nowy.',
    ],
];
