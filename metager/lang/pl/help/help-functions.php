<?php
return [
    'title' => 'MetaGer - Pomoc',
    'backarrow' => 'Powrót',
    'urls' => [
        'title' => 'Wyklucz adresy URL',
        'explanation' => 'Możesz wykluczyć wyniki wyszukiwania, które zawierają określone słowa w linkach do wyników, używając "-url:" w wyszukiwaniu.',
        'example_b' => '<i>moje wyszukiwanie</i> -url:dog',
        'example_a' => 'Przykład: Chcesz wykluczyć wyniki, w których słowo "pies" pojawia się w linku do wyniku:',
    ],
    'bang' => [
        'title' => 'MetaGer Maps <a title="For easy help, click here" href="/hilfe/easy-language/services#eh-maps" ><img class="easy-help-icon lm-only" src="/img/help-questionmark-icon-lm.svg"/><img class="easy-help-icon dm-only" src="/img/help-questionmark-icon-dm.svg"/></a>',
        '1' => 'MetaGer obsługuje w ograniczonym zakresie styl pisania często określany jako składnia \'!bang\'.<br>Składnia \'!bang\' zawsze zaczyna się od wykrzyknika i nie zawiera spacji. Przykłady obejmują \'!twitter\' lub \'!facebook\'.<br>Kiedy w zapytaniu wyszukiwawczym zostanie użyty obsługiwany !bang, w naszych szybkich wskazówkach pojawi się wpis, umożliwiający kontynuowanie wyszukiwania w odpowiedniej usłudze (Twitter lub Facebook) za naciśnięciem przycisku.',
        '2' => 'Dlaczego !bangs nie są otwierane bezpośrednio?',
        '3' => 'Przekierowania !bang są częścią naszych szybkich wskazówek i wymagają dodatkowego "kliknięcia". Była to dla nas trudna decyzja, ponieważ sprawia, że !bangi są mniej użyteczne. Jest to jednak niestety konieczne, ponieważ linki, do których następuje przekierowanie, nie pochodzą od nas, ale od strony trzeciej, DuckDuckGo.<p>Zawsze zapewniamy, że nasi użytkownicy zachowują kontrolę przez cały czas. Dlatego chronimy na dwa sposoby: Po pierwsze, wprowadzony termin wyszukiwania nigdy nie jest przesyłany do DuckDuckGo, a jedynie !bang. Po drugie, użytkownik wyraźnie potwierdza wizytę w miejscu docelowym !bang. Niestety, ze względów kadrowych nie możemy obecnie samodzielnie sprawdzać ani utrzymywać wszystkich tych !bangów.',
    ],
    'selist' => [
        'title' => 'Dodaj MetaGer do listy wyszukiwarek w przeglądarce <a title="For easy help, click here" href="/hilfe/easy-language/functions#eh-selist"><img class="easy-help-icon lm-only" src="/img/help-questionmark-icon-lm.svg"/><img class="easy-help-icon dm-only" src="/img/help-questionmark-icon-dm.svg"/></a>',
        'explanation_b' => 'Niektóre przeglądarki wymagają wprowadzenia adresu URL; powinien to być "https://metager.de/meta/meta.ger3?input=%s" bez cudzysłowów. Adres URL można wygenerować samodzielnie, wyszukując coś za pomocą metager.de, a następnie zastępując to, co znajduje się za "input=" w pasku adresu za pomocą %s. Jeśli nadal masz jakiekolwiek problemy, skontaktuj się z nami: <a href="/kontalt" target="_blank" rel="noopener">Formularz kontaktowy</a>',
        'explanation_a' => 'Spróbuj najpierw zainstalować aktualną wtyczkę. Aby ją zainstalować, wystarczy kliknąć link znajdujący się bezpośrednio pod polem wyszukiwania. Twoja przeglądarka powinna już zostać tam wykryta.',
    ],
    'key' => [
        'title' => 'Dodaj klucz MetaGer <a title="For easy help, click here" href="/hilfe/easy-language/functions#eh-keyexplain"><img class="easy-help-icon lm-only" src="/img/help-questionmark-icon-lm.svg"/><img class="easy-help-icon dm-only" src="/img/help-questionmark-icon-dm.svg"/></a>',
        '1' => 'Klucz MetaGer jest automatycznie konfigurowany w przeglądarce i używany. Nie trzeba robić nic więcej. Jeśli chcesz używać klucza MetaGer na innych urządzeniach, istnieje kilka sposobów jego skonfigurowania:',
        'more' => 'Wszystkie sposoby konfiguracji i więcej pytań o klucz',
    ],
    'multiwordsearch' => [
        'title' => 'Wyszukiwanie wielu słów <a title="For easy help, click here" href="/hilfe/easy-language/functions#eh-severalwords"><img class="easy-help-icon lm-only" src="/img/help-questionmark-icon-lm.svg"/><img class="easy-help-icon dm-only" src="/img/help-questionmark-icon-dm.svg"/></a>',
        '4' => [
            'example' => '"okrągły stół"',
            'text' => "Wyszukiwanie fraz umożliwia wyszukiwanie kombinacji słów zamiast pojedynczych słów. Wystarczy ująć słowa, które powinny pojawić się razem, w cudzysłów.",
        ],
        '3' => [
            'example' => '"okrągły" "stół"',
            'text' => "Jeśli chcesz mieć pewność, że słowa z wyszukiwania również pojawią się w wynikach, musisz ująć je w cudzysłów.",
        ],
        '2' => "Jeśli to nie wystarczy, masz 2 opcje, aby uczynić wyszukiwanie bardziej precyzyjnym:",
        '1' => "Podczas wyszukiwania więcej niż jednego słowa w MetaGer, automatycznie staramy się dostarczyć wyniki, w których wszystkie słowa pojawiają się lub są jak najbardziej zbliżone.",
    ],
    'searchfunction' => [
        'title' => "Funkcje wyszukiwania",
    ],
    'stopwords' => [
        'title' => 'Stopwords <a title="For easy help, click here" href="/hilfe/easy-language/functions#eh-stopwordsearch"><img class="easy-help-icon lm-only" src="/img/help-questionmark-icon-lm.svg"/><img class="easy-help-icon dm-only" src="/img/help-questionmark-icon-dm.svg"/></a>',
        '3' => "samochód nowy -bmw",
        '2' => "Przykład: Szukasz nowego samochodu, ale na pewno nie BMW. Twój wkład byłby następujący:",
        '1' => "Jeśli chcesz wykluczyć wyniki wyszukiwania w MetaGer, które zawierają określone słowa (słowa wykluczające / stopwords), możesz to zrobić, poprzedzając te słowa znakiem minus.",
    ],
    'exactsearch' => [
        'title' => 'Wyszukiwanie dokładne <a title="For easy help, click here" href="/hilfe/easy-language/functions#exactsearch"><img class="easy-help-icon lm-only" src="/img/help-questionmark-icon-lm.svg"/><img class="easy-help-icon dm-only" src="/img/help-questionmark-icon-dm.svg"/></a>',
        '1' => "Jeśli chcesz znaleźć określone słowo w wynikach wyszukiwania MetaGer, możesz poprzedzić to słowo znakiem plus. W przypadku użycia znaku plus i cudzysłowu fraza jest wyszukiwana dokładnie w takiej postaci, w jakiej została wprowadzona.",
        '2' => "Przykład: S",
        '3' => 'Przykład: ',
        'example' => [
            '1' => "+przykładowe słowo",
            '2' => '+"przykładowa fraza"',
        ],
    ],
    'easy-help' => 'Klikając na symbol <a title="For easy help, click here" href="/hilfe/easy-language/services" ><img class="easy-help-icon lm-only" src="/img/help-questionmark-icon-lm.svg"/><img class="easy-help-icon dm-only" src="/img/help-questionmark-icon-dm.svg"/></a> , można uzyskać dostęp do uproszczonej wersji pomocy.',
];
