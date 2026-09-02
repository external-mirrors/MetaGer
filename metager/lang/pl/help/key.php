<?php

/**
 * Fragen zum MetaGer-Schlüssel — /hilfe/schluessel.
 *
 * Aus dem "faq"-Zweig von pass/lang/<locale>/help.json des Keymanagers.
 */

return [
    "heading" => "Pytania o klucz MetaGer",
    "faqs" => [
        [
            "summary" => "Jak działa klucz MetaGer?",
            "description" => "Klucz MetaGer umożliwia wyszukiwanie bez reklam. Otrzymujesz tokeny, od których odejmowane jest jedno wyszukiwanie. Podczas korzystania z klucza MetaGer wszystkie funkcje chroniące MetaGer przed automatycznymi połączeniami są wyłączone. Oznacza to, że nie zobaczysz żądań captcha, a Twój adres IP nie będzie przechowywany przez ograniczony czas. Mówiąc prościej, sprawi to, że MetaGer będzie szybszy, bardziej niezawodny i bezpieczniejszy.",
        ],
        [
            "summary" => "Jak działa anonimowy token?",
            "description" => "Możesz użyć anonimowego tokena z naszym rozszerzeniem przeglądarki lub aplikacją. Pozwoli to na jeszcze bezpieczniejsze wyszukiwanie w MetaGer. Podczas korzystania z anonimowego tokena część Twoich danych w postaci losowych haseł będzie przechowywana na Twoim urządzeniu. Dzięki <a href=\":tokenlink\">złożonemu procesowi kryptograficznemu</a> nawet dla nas niemożliwe jest powiązanie wykonanych wyszukiwań ze sobą lub z Twoim kluczem.",
        ],
        [
            "summary" => "Jak używać klucza MetaGer?",
            "description" => "Klucz MetaGer jest automatycznie konfigurowany i używany w przeglądarce. Nie trzeba więc robić nic więcej. Jeśli chcesz używać klucza MetaGer na dodatkowych urządzeniach, istnieje kilka sposobów jego skonfigurowania:",
            "steps" => [
                [
                    "heading" => "Kopiuj adres URL",
                    "description" => "Na stronie zarządzania kluczami MetaGer dostępna jest opcja skopiowania adresu URL. Za pomocą tego adresu URL wszystkie ustawienia MetaGer, a także klucz MetaGer można zapisać na innym urządzeniu.",
                ],
                [
                    "heading" => "Zapisz plik",
                    "description" => "Na stronie zarządzania kluczami MetaGer dostępna jest opcja zapisania pliku. Spowoduje to zapisanie klucza MetaGer jako pliku. Możesz następnie użyć tego pliku na innym urządzeniu, aby zalogować się tam za pomocą swojego klucza.",
                ],
                [
                    "heading" => "Skanowanie kodu QR",
                    "description" => "Alternatywnie można również zeskanować kod QR wyświetlany na stronie administracyjnej, aby zalogować się na innym urządzeniu.",
                ],
                [
                    "heading" => "Wprowadź ręcznie klucz MetaGer",
                    "description" => "Oczywiście można również wprowadzić klucz ręcznie na innym urządzeniu.",
                ],
            ],
        ],
        [
            "summary" => "Muszę regularnie wprowadzać klucz. Co mogę zrobić?",
            "description" => "Nakazujemy przeglądarce trwałe przechowywanie klucza po jego wygenerowaniu lub zalogowaniu. W zależności od konfiguracji przeglądarki użytkownik może mieć ustawione regularne usuwanie plików cookie i danych witryny, co oczywiście spowoduje również wylogowanie z MetaGer. Dostępne są następujące opcje:",
            "steps" => [
                [
                    "heading" => "Dodaj wyjątek",
                    "description" => "W ustawieniach Firefoksa możesz umieścić MetaGer na białej liście, aby uzyskać wyłączenie usuwania plików cookie i danych witryny, co pozwoli Ci pozostać zalogowanym.",
                ],
                [
                    "heading" => "Zainstaluj nasze rozszerzenie przeglądarki",
                    "description" => "Nasze rozszerzenie przeglądarki dla <a href=\"https://addons.mozilla.org/firefox/addon/metager-suche/\" target=\"_blank\" rel=\"noopener\">Firefox</a> i <a href=\"https://chrome.google.com/webstore/detail/gjfllojpkdnjaiaokblkmjlebiagbphd\" target=\"_blank\" rel=\"noopener\">Chrome</a> może przechowywać ustawienia wyszukiwania, w tym klucz, bez użycia plików cookie, dzięki czemu można usunąć wszystkie dane przeglądarki bez wylogowywania się z MetaGer.",
                ],
                [
                    "description" => "Jeśli korzystasz z menedżera haseł, możesz zapisać w nim klucz, aby zalogować się automatycznie. Alternatywnie oferujemy <a href=\":keylink\">adres URL ustawień</a>, który można zapisać np. jako zakładkę. Po otwarciu adres URL ustawień zaloguje użytkownika bez konieczności ręcznego wprowadzania klucza.",
                    "heading" => "Logowanie bez wprowadzania 36-znakowego klucza",
                ],
            ],
        ],
        [
            "summary" => "Jestem niezadowolony z klucza MetaGer. Co mogę zrobić?",
            "description" => "W takim przypadku możesz zażądać zwrotu pieniędzy za niewykorzystane tokeny w ciągu 30 dni od zakupu. Aby to zrobić, będziesz potrzebować swojego identyfikatora płatności. Aby zażądać zwrotu, otwórz stronę zarządzania kluczami MetaGer. Tam kliknij pozycję menu \"Zamówienia\" i wprowadź swój identyfikator płatności. Następnie kliknij przycisk \"Żądanie zwrotu\" i wyślij żądanie zwrotu.",
        ],
        [
            "summary" => "Jak wyszukiwać całkowicie anonimowo?",
            "description" => "Twoja prywatność i anonimowość są dla nas bardzo ważne. Dlatego oferujemy anonimowe metody płatności (gotówka). Oferujemy również korzystanie z <a href=\":tokenlink\">anonimowych tokenów</a>, których można nawet używać do anonimowego wyszukiwania.",
        ],
        [
            "summary" => "Potrzebuję faktury. Jak mogę ją otrzymać?",
            "description" => "W tym celu potrzebny jest tylko identyfikator płatności. Aby poprosić o fakturę, otwórz stronę administracyjną klucza MetaGer. Tutaj kliknij pozycję menu \"Zamówienia\" i wprowadź swój identyfikator płatności. Teraz możesz kliknąć przycisk \"Poproś o fakturę\" i rozpocząć żądanie faktury. Do wystawienia faktury potrzebujemy Twojego imienia i nazwiska, adresu e-mail i adresu.",
        ],
        [
            "summary" => "Chciałbym automatycznie naładować mój klucz MetaGer. Jak to zrobić?",
            "description" => "W przypadku naszych członków klucz zawarty w członkostwie jest automatycznie doładowywany co miesiąc. Ilość tokenów zależy od uiszczonej opłaty członkowskiej.",
        ],
        [
            "summary" => "Otrzymałem kartę lub link z kodem podarunkowym. Co mam z tym zrobić?",
            "description" => "Niektóre organizacje rozdają klucze MetaGer ze stałym saldem za pomocą kart promocyjnych lub linku. Otwórz <a href=\":voucherlink\">naszą stronę realizacji</a>, wpisz nadrukowany kod albo zeskanuj kod QR z karty. Otrzymasz natychmiast nowy klucz MetaGer z podarowanym saldem, ważnym przez ograniczony czas. Każdy kod można zrealizować tylko raz.",
        ],
    ],
    "more-questions" => "Masz dodatkowe pytania? Zapraszamy do skorzystania z naszego <a href=\":contactlink\" target=\"_blank\">formularza kontaktowego</a>.",
];
