<?php
return [
    'mehrwortsuche' => [
        '4' => [
            'text' => 'Umieść słowa lub frazy w cudzysłowach, aby wyszukać dokładne kombinacje.',
        ],
        '1' => 'Bez cudzysłowu otrzymasz wyniki zawierające jedno lub kilka słów z wyszukiwanego hasła. Użyj cudzysłowów do wyszukiwania dokładnych fraz, cytatów....',
        '3' => [
            'example' => '"okrągły stół" "decyzja"',
        ],
    ],
    'title' => 'MetaGer - Pomoc',
    'backarrow' => ' Powrót',
    'stopworte' => [
        'title' => 'Wyklucz pojedyncze słowa',
        '2' => 'Przykład: Szukasz nowego samochodu, ale nie BMW. W takim przypadku wyszukiwanie powinno wyglądać następująco: <div class="well well-sm">nowy samochód -bmw</div>',
    ],
    'urls' => [
        'title' => 'Wyklucz adresy URL',
        'explanation' => 'Użyj "-url:", aby wykluczyć wyniki wyszukiwania zawierające określone słowa.',
        'example_b' => 'Wpisz <i>moje słowa wyszukiwania</i> -url:dog',
        'example_a' => 'Przykład: Nie chcesz słowa "pies" w wynikach:',
    ],
    'bang' => [
        'title' => '!bangs',
        '1' => 'MetaGer używa specjalnej pisowni zwanej "!bang syntax". !bang zaczyna się od "!" i nie zawiera spacji ("!twitter", "!facebook" na przykład). Jeśli użyjesz !bang obsługiwanego przez MetaGer, zobaczysz nowy wpis w "Quicktips". Następnie przekierujemy do określonej usługi (kliknij przycisk).',
    ],
    'faq' => [
        '18' => [
            'b' => 'Bang -\"redirections\" są częścią naszych szybkich wskazówek i wymagają dodatkowego kliknięcia. Musieliśmy wybrać między łatwością użycia a zachowaniem kontroli nad danymi. Uważamy za konieczne pokazanie, że linki są własnością strony trzeciej (DuckDuckGo). Istnieje więc dwukierunkowa ochrona: po pierwsze nie przekazujemy wyszukiwanych haseł, a jedynie !bang do DuckDuckGo. Z drugiej strony użytkownik wyraźnie potwierdza cel !bang. Nie mamy zasobów, aby utrzymać wszystkie te !bangs, przykro nam.',
        ],
    ],
    'searchinsearch' => [
        '1' => 'Wynik zostanie zapisany w nowej zakładce po prawej stronie ekranu. Nosi ona nazwę "Zapisane wyniki". Można tu zapisywać pojedyncze wyniki z kilku wyszukiwań. Zakładka ta pozostaje niezmieniona. Wchodząc do tej zakładki, otrzymasz osobistą listę wyników z narzędziami do filtrowania i sortowania wyników. Kliknij inną zakładkę, aby wrócić do dalszych wyszukiwań. Nie będzie to możliwe, jeśli ekran jest zbyt mały. Więcej informacji (na razie tylko w języku niemieckim): <a href="http://blog.suma-ev.de/node/225" target="_blank" rel="noopener"> Blog SUMA</a>.',
    ],
    'selist' => [
        'title' => 'Chcę dodać metager.de do listy wyszukiwarek w mojej przeglądarce.',
        'explanation_b' => 'Niektóre przeglądarki wymagają adresu URL. Prosimy o użycie "https://metager.org/meta/meta.ger3?eingabe=%s" bez znaków cudzysłowu. Jeśli nadal występują problemy, prosimy o <a href="/en/kontakt" target="_blank" rel="noopener">napisać e-mail.</a>',
        'explanation_a' => 'Spróbuj najpierw zainstalować najnowszą dostępną wtyczkę. Wystarczy użyć linku poniżej pola wyszukiwania, ma on automatyczne wykrywanie przeglądarki.',
    ],
];
