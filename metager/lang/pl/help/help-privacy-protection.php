<?php
return [
    'title' => 'MetaGer - Pomoc',
    'backarrow' => 'Powrót',
    'tor' => [
        'title' => 'Ukryta usługa TOR <a title="For easy help, click here" href="/hilfe/easy-language/privacy-protection#eh-torhidden" ><img class="easy-help-icon lm-only" src="/img/help-questionmark-icon-lm.svg"/><img class="easy-help-icon dm-only" src="/img/help-questionmark-icon-dm.svg"/></a>',
        '1' => 'Od wielu lat MetaGer ukrywa i nie przechowuje adresów IP. Adresy te są jednak tymczasowo widoczne na serwerze MetaGer podczas wyszukiwania: jeśli MetaGer zostałby przejęty, atakujący mógłby odczytać i zapisać adresy użytkowników. Aby spełnić najwyższe wymagania bezpieczeństwa, obsługujemy instancję MetaGer w sieci Tor: MetaGer TOR Hidden Service - dostępną pod adresem: <a href="/tor/" target="_blank" rel="noopener">https://metager.de/tor/</a>. Aby z niej skorzystać, potrzebna jest specjalna przeglądarka, którą można pobrać ze strony <a href="https://www.torproject.org/" target="_blank" rel="noopener">https://www.torproject.org/</a>.',
        '2' => 'Dostęp do MetaGer w przeglądarce Tor można uzyskać pod adresem: http://metagerv65pwclop2rsfzg4jwowpavpwd6grhhlvdgsswvo6ii4akgyd.onion .',
    ],
    'proxy' => [
        'title' => 'Otwórz anonimowo (SafeBrowse) <a title="For easy help, click here" href="/hilfe/easy-language/privacy-protection#eh-proxy" ><img class="easy-help-icon lm-only" src="/img/help-questionmark-icon-lm.svg"/><img class="easy-help-icon dm-only" src="/img/help-questionmark-icon-dm.svg"/></a>',
        'intro' => 'Dzięki „Otwórz anonimowo” odwiedzasz stronę internetową tak, że nigdy nie widzi ona Twojego urządzenia. Strona otwiera się w przeglądarce działającej na naszych serwerach: SafeBrowse. Witryna ma do czynienia tylko z tą przeglądarką i nie poznaje ani Twojego adresu IP, ani niczego o Twoim urządzeniu, przeglądarce czy plikach cookie.',
        'use' => [
            'title' => 'Jak otworzyć stronę anonimowo',
            'text' => [
                'Kliknij „Otwórz anonimowo” (ikona tarczy) przy wyniku. Strona otworzy się w nowej karcie.',
                'Jeśli chcesz otwierać każdy wynik anonimowo, włącz w ustawieniach „Otwieranie wyników anonimowo”. Wtedy każdy link wyniku prowadzi przez SafeBrowse, a „Otwórz bezpośrednio” otwiera stronę bez SafeBrowse.',
            ],
        ],
        'session' => [
            'title' => 'Sesja',
            'text' => [
                'Pierwszy wynik otwarty anonimowo rozpoczyna sesję. Każdy kolejny otwiera się w tej samej sesji, we własnej karcie. Tam przeglądasz dalej jak zwykle: klikasz linki, wypełniasz formularze, wpisujesz nowy adres lub hasło wyszukiwania w pasku adresu. Przez cały czas pozostajesz chroniony.',
                'Sesja kosztuje tokeny z Twojego klucza MetaGer; ile i na jak długo, widać przed startem. Dopóki korzystasz z sesji, jest ona automatycznie przedłużana. Gdy przestaniesz z niej korzystać, wkrótce kończy się sama, albo kończysz ją przyciskiem „Zamknij sesję”.',
                'Gdy sesja się kończy, przeglądarka na naszych serwerach jest usuwana razem ze wszystkim, co w niej było: odwiedzonymi stronami, plikami cookie, logowaniami i pobranymi plikami. Następna sesja zaczyna się z nową, pustą przeglądarką. Dlatego wcześniej zapisz pobrane pliki na swoim urządzeniu w „Pliki do pobrania”.',
            ],
        ],
        'views' => [
            'title' => 'Dwa widoki',
            'text' => [
                '„Strona” pokazuje kopię witryny bez jej skryptów. Czytasz, przewijasz, wyszukujesz i kopiujesz w niej jak na każdej stronie; Twoje kliknięcia i wpisy trafiają do przeglądarki na naszych serwerach. „Pełna przeglądarka” pokazuje tę przeglądarkę jako obraz, ze wszystkimi jej kartami. Tam działa wszystko, także filmy i strony, których „Strona” nie potrafi pokazać. Między widokami przełączasz się na pasku u góry.',
            ],
        ],
        'nojs' => [
            'title' => 'Bez JavaScriptu',
            'text' => [
                'Nawet gdy JavaScript jest wyłączony w Twojej przeglądarce, „Otwórz anonimowo” otwiera stronę w SafeBrowse. Widzisz wtedy kopię strony, w której działają linki, przyciski i formularze. Jest ona przeładowywana po każdym kliknięciu, ale sama się nie zmienia. Filmy, okna dialogowe strony i przesyłanie plików w ten sposób nie działają. Jeśli Twoja przeglądarka blokuje pliki cookie, wyniki otwierane z jednej strony wyników i tak trafiają do tej samej sesji, także gdy stamtąd szukasz dalej.',
            ],
        ],
        'limits' => [
            'title' => 'Czego „Otwórz anonimowo” nie robi',
            'text' => [
                'Witryna nie rozpoznaje Cię po urządzeniu, ale owszem po tym, co sam jej powiesz: jeśli się zalogujesz lub podasz dane osobowe, wie, kim jesteś. Jeśli nie chcesz pokazywać się także MetaGerowi, korzystaj z MetaGera przez Tor (zob. wyżej).',
            ],
        ],
    ],
    'content' => [
        'title' => 'Wątpliwa zawartość / ochrona młodzieży <a title="For easy help, click here" href="/help/easy-language/privacy-protection#eh-content" ><img class="easy-help-icon lm-only" src="/img/help-questionmark-icon-lm.svg"/><img class="easy-help-icon dm-only" src="/img/help-questionmark-icon-dm.svg"/></a>',
        'explanation' => [
            '1' => 'Otrzymywałem "trafienia", które nie tylko mnie irytowały, ale także zawierały, moim zdaniem, nielegalne treści!',
            '2' => 'Jeśli znajdziesz w Internecie treści, które uważasz za nielegalne lub szkodliwe dla małoletnich, możesz skontaktować się z <a href="mailto:hotline@jugendschutz.net" target="_blank" rel="noopener">hotline@jugendschutz.net</a> pocztą elektroniczną lub odwiedzić <a href="http://www.jugendschutz.net/" target="_blank" rel="noopener">www.jugendschutz.net</a> i wypełnić dostępny tam formularz skargi. Pomocne jest podanie krótkiej notatki na temat tego, co uważasz za niedopuszczalne i w jaki sposób natknąłeś się na te treści. Wątpliwe treści można również zgłaszać bezpośrednio do nas. W tym celu należy wysłać wiadomość e-mail do naszego specjalisty ds. ochrony młodzieży (<a href="mailto:jugendschutz@metager.de" target="_blank" rel="noopener">jugendschutz@metager.de</a>).',
        ],
    ],
    'maps' => [
        'title' => 'MetaGer Maps',
        '1' => 'Zachowanie prywatności w dobie globalnych gigantów danych doprowadziło nas również do opracowania <a href="https://maps.metager.de" target="_blank">https://maps.metager.de</a>: (według naszej wiedzy) jedynego narzędzia do planowania tras, które oferuje pełną funkcjonalność za pośrednictwem przeglądarki i aplikacji bez przechowywania lokalizacji użytkownika. Wszystko to można zweryfikować, ponieważ nasze oprogramowanie jest open source. Do korzystania z maps.metager.de zalecamy naszą szybką wersję aplikacji. Nasze aplikacje można pobrać ze strony <a href="/app" target="_blank">tutaj</a> (lub oczywiście również ze Sklepu Play).',
        '2' => 'Ta funkcja mapy może być również dostępna z wyszukiwarki MetaGer (i odwrotnie). Po wyszukaniu terminu w MetaGer, w prawym górnym rogu pojawi się nowe pole wyszukiwania "Mapy". Kliknięcie go spowoduje przejście do odpowiedniej mapy.',
        '3' => 'Po załadowaniu mapa wyświetla punkty (POI = Points of Interest) znalezione przez MetaGer, które są również wymienione w prawej kolumnie. Podczas powiększania lista ta dostosowuje się do sekcji mapy. Najechanie kursorem myszy na znacznik na mapie lub na liście powoduje podświetlenie odpowiedniego elementu. Kliknij "Szczegóły", aby uzyskać więcej informacji o tym punkcie z poniższej bazy danych.',
    ],
    'easy-help' => 'Klikając na symbol <a title="For easy help, click here" href="/hilfe/easy-language/privacy-protection" ><img class="easy-help-icon lm-only" src="/img/help-questionmark-icon-lm.svg"/><img class="easy-help-icon dm-only" src="/img/help-questionmark-icon-dm.svg"/></a> , można uzyskać dostęp do uproszczonej wersji pomocy.',
    'privacy' => [
        'title' => "Anonimowość i bezpieczeństwo danych",
        '1' => 'Śledzące pliki cookie, identyfikatory sesji i adresy IP <a title="For easy help, click here" href="/hilfe/easy-language/privacy-protection#eh-tracking" ><img class="easy-help-icon lm-only" src="/img/help-questionmark-icon-lm.svg"/><img class="easy-help-icon dm-only" src="/img/help-questionmark-icon-dm.svg"/></a>',
        '3' => "Więcej informacji można znaleźć poniżej. Funkcje są dostępne w sekcji \"Usługi\" na pasku nawigacyjnym.",
        '2' => 'Żadne z nich nie są wykorzystywane, przechowywane, zatrzymywane ani w inny sposób przetwarzane w MetaGer (wyjątek: krótkoterminowe przechowywanie w celu ochrony przed atakami hakerów i botów). Ponieważ uważamy ten temat za niezwykle ważny, stworzyliśmy również sposoby, które pomogą ci osiągnąć najwyższy poziom bezpieczeństwa: MetaGer TOR Hidden Service i naszą anonimową przeglądarkę.',
    ],
];
