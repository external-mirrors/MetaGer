<?php
return [
    'plugin' => 'Zainstaluj MetaGer',
    'plugin-title' => 'Dodaj MetaGer do swojej przeglądarki',
    'key' => [
        'placeholder' => 'Wprowadź swój klucz MetaGer, aby rozpocząć wyszukiwanie.',
        'tooltip' => [
            'nokey' => 'Skonfiguruj wyszukiwanie bez reklam',
            'empty' => 'Żeton zużyty. Doładuj teraz.',
            'low' => 'Żeton wkrótce się zużyje. Doładuj teraz.',
            'full' => 'Wyszukiwanie bez reklam włączone.',
        ],
    ],
    'placeholder' => 'MetaGer: Wyszukiwanie i znajdowanie chronione prywatnością',
    'searchbutton' => 'Uruchom MetaGer-Search',
    'foki' => [
        'web' => 'Sieć',
        'bilder' => 'Obrazy',
        'nachrichten' => 'Aktualności',
        'science' => 'Nauka',
        'produkte' => 'Produkty',
        'maps' => 'Mapy',
    ],
    'adfree' => 'Korzystaj z MetaGer bez reklam',
    'skip' => [
        'search' => 'Przejdź do wprowadzania zapytania wyszukiwania',
        'navigation' => 'Przejdź do nawigacji',
        'fokus' => 'Przejdź do wyboru fokusu wyszukiwania',
    ],
    'lang' => 'język przełącznika',
    'searchreset' => 'usuń wprowadzone zapytanie wyszukiwania',
    'searchbar-replacement' => [
        'tagline' => 'Open source. Bez reklam. Anonimowo.',
        'message' => 'Twój klucz to Twój dostęp – bez konta, bez adresu e-mail. Saldo i ustawienia są z nim powiązane.',
        'first_time' => 'Pierwszy raz tutaj?',
        'start' => 'Skonfiguruj klucz',
        // Swapped in by resources/js/accountBreadcrumb.js when this browser has
        // rendered a signed-in page before. Three strings, replacing three
        // elements in place — nothing appears, nothing moves.
        'welcome_back' => 'Witamy ponownie.',
        'welcome_back_message' => 'Na tym urządzeniu byłeś już zalogowany. Zaloguj się tym samym kluczem – Twoje saldo nadal tam jest.',
        'welcome_back_button' => 'Zaloguj się ponownie',
        'have_key' => 'Zaloguj się moim kluczem',
        'login' => 'Zaloguj się',
        'key_error' => "Wprowadzony klucz był nieprawidłowy. Sprawdź wprowadzone dane.",
        'login_code_error' => "Wprowadzony kod logowania był nieprawidłowy. Wskazówka: Kody logowania są ważne tylko wtedy, gdy są widoczne na innym urządzeniu!",
        'payment_id_error' => "Wprowadzono identyfikator płatności, który nie jest prawidłowym kluczem. Klucz ma długość 36 znaków.",
        'new_key' => 'Nie masz jeszcze klucza?',
        'extension' => 'Pozostań zalogowany i anonimowy dzięki naszemu rozszerzeniu internetowemu',
    ],
    // The landing page shown to a visitor without a key: hero, "how it works",
    // and the five benefit cards. It came from the keymanager's own root page
    // (pass/views/index.ejs, pass/lang/*/index.json), which /keys used to serve
    // and which now redirects here.
    //
    // Placeholders are Laravel's :name, not i18next's {{name}}, and the links
    // are passed in from parts/landing/* so the locale prefix and the /keys
    // paths stay in one place.
    'landing' => [
        'title' => 'MetaGer: szukaj i surfuj po sieci bez bycia obserwowanym',
        'description' => 'MetaGer szanuje Twoją prywatność: bez reklam, bez śledzenia, bez rejestrowania. A teraz możesz też anonimowo odwiedzać dowolną stronę.',
        'advantages' => [
            'ads' => 'Bez reklam',
            'tracking' => 'Bez śledzenia',
            'logging' => 'Bez rejestrowania',
            'compromise' => 'Bez kompromisów',
        ],
        'calltoaction' => 'Jak to działa',
        'benefits' => [
            'browsing' => [
                'heading' => 'Nie tylko anonimowe wyszukiwanie – także anonimowe przeglądanie',
                'description' => 'Za pomocą klucza MetaGer możesz otworzyć dowolną stronę w prywatnej przeglądarce, która działa bezpiecznie na naszych serwerach, a nie na Twoim urządzeniu. Strony nie widzą, kim jesteś ani skąd przeglądasz, a po zakończeniu sesji wszystko jest automatycznie usuwane. Bez instalacji, bez konfiguracji – wystarczy otworzyć i zacząć.',
                'fingerprinting' => 'Fingerprinting',
                'tracking' => 'Śledzenie',
            ],
            'ads' => [
                'heading' => 'Bez reklam',
                'description' => 'Reklamy i prywatność rzadko idą w parze. Dlatego w MetaGer nie ma żadnych reklam, dzięki czemu możemy chronić Twoją prywatność bez kompromisów.',
                'ads' => 'Reklama',
                'tracking' => 'Linki śledzące',
            ],
            'logging' => [
                'heading' => 'Bez rejestrowania',
                'description' => 'Wyszukiwanie w internecie zwykle zostawia po sobie ślad danych. My nie musimy przechowywać żadnych: nasza wyszukiwarka jest zbudowana tak, że walka ze spamem nie wymaga logów. Nie napotkasz też u nas ani jednej captchy, nawet korzystając z VPN.',
                'logging' => 'Rejestrowanie',
            ],
            'compromise' => [
                'heading' => 'Bez kompromisów',
                'description' => 'Zamiast konta powiązanego z Twoimi danymi osobowymi otrzymujesz po prostu losowo wygenerowany klucz – bez imienia i bez adresu e-mail. Wybierz jedną z kilku <a href=":linkPaymentMethods">metod płatności</a>, w tym całkowicie anonimową płatność gotówką. Dzięki naszej <a href=":linkApp">aplikacji na Androida</a> lub rozszerzeniu przeglądarki możesz nawet udowodnić, że Twoje wyszukiwania pozostają anonimowe, korzystając z <a href=":linkToken">anonimowych tokenów</a>.',
                'compromise' => 'Dane osobowe',
            ],
            'efficiency' => [
                'heading' => 'Wyszukuj wydajniej',
                'description' => 'Szybciej znajdź to, czego szukasz. Gdy to pomocne, dodajemy przejrzyste głębokie linki, istotne wiadomości i filmy bezpośrednio do wyników wyszukiwania. Nasze wyszukiwanie obrazów korzysta również z dodatkowych źródeł.',
            ],
        ],
        'howitworks' => [
            'heading' => 'Jak to działa',
            'steps' => [
                [
                    'heading' => 'Odbierz swój darmowy klucz',
                    'description' => 'Twój klucz MetaGer jest generowany automatycznie. Bez rejestracji, bez danych osobowych. To wszystko, czego potrzebujesz, aby korzystać z MetaGer.',
                ],
                [
                    'heading' => 'Aktywuj dostęp',
                    'description' => 'Jednorazowa <a href=":linkCost">płatność</a> dodaje do Twojego klucza środki, które nazywamy tokenami. Aktywuje to wyszukiwanie bez reklam i bez śledzenia oraz anonimowe przeglądanie – wraz ze wszystkimi obecnymi i przyszłymi funkcjami MetaGer. Około 500 tokenów (5 €) wystarcza zwykle na mniej więcej 2 miesiące.',
                    'membership' => 'Uwaga: członkowie naszego stowarzyszenia pożytku publicznego <a href="https://suma-ev.de" target="_blank">SUMA-EV</a> mogą korzystać z MetaGer bez dodatkowych kosztów. <a href=":linkMembership" target="_blank">Zostań członkiem</a>',
                ],
                [
                    'heading' => 'Korzystaj z MetaGer wszędzie',
                    'description' => 'Używaj tego samego klucza na dowolnej liczbie urządzeń lub udostępnij go znajomym i rodzinie. Po prostu otwórz MetaGer na dowolnym urządzeniu, wpisz swój klucz i możesz wyszukiwać – lub przeglądać anonimowo.',
                ],
            ],
            'start' => 'Rozpocznij',
            'login' => 'Mam już klucz',
        ],
    ],
];
