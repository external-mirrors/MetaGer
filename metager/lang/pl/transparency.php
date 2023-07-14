<?php
return [
    'head' => [
        '1' => 'Oświadczenie o przejrzystości',
        '2' => 'MetaGer jest przezroczysty',
        '3' => 'Czym jest wyszukiwarka metasearch?',
        '4' => 'Jaka jest zaleta metawyszukiwarki?',
        '5' => 'Jak tworzony jest nasz ranking?',
        'compliance' => 'W jaki sposób MetaGer odpowiada na żądania władz?',
    ],
    'text' => [
        '1' => 'MetaGer jest transparentny. Nasz kod źródłowy <a href=":sourcecode"></a> jest dostępny na wolnej licencji i publicznie dostępny dla wszystkich. Nie przechowujemy danych użytkowników i cenimy sobie ochronę danych i prywatności. Dlatego zapewniamy anonimowy dostęp do wyników wyszukiwania. Jest to możliwe dzięki anonimowemu proxy i ukrytemu dostępowi TOR. Ponadto MetaGer ma przejrzystą strukturę organizacyjną, ponieważ jest wspierany przez stowarzyszenie non-profit <a href=":sumalink">SUMA-EV</a>, którego członkiem może zostać każdy.',
        '2' => [
            '1' => 'Aby wyjaśnić, czym są metawyszukiwarki, warto najpierw pokrótce wyjaśnić, jak działa indeksowanie zwykłych wyszukiwarek. Zwykłe wyszukiwarki uzyskują wyniki wyszukiwania z bazy danych stron internetowych, która jest również nazywana indeksem. Wyszukiwarki używają tak zwanych "crawlerów", które zbierają strony internetowe i dodają je do indeksu (bazy danych). Crawler zaczyna od zestawu stron internetowych i otwiera wszystkie strony internetowe, do których prowadzą linki. Są one indeksowane, tj. dodawane do indeksu. Następnie crawler otwiera strony internetowe powiązane z tymi stronami internetowymi i kontynuuje w ten sposób.',
            '2' => 'Metawyszukiwarka łączy wyniki kilku wyszukiwarek i ocenia je ponownie według własnych kryteriów. Oznacza to, że metawyszukiwarka nie posiada własnego indeksu. Dlatego metawyszukiwarki nie korzystają z robotów indeksujących. Korzystają one z indeksu innych wyszukiwarek.',
        ],
        '3' => 'Oczywistą zaletą metawyszukiwarek jest to, że użytkownik potrzebuje tylko jednego zapytania, aby uzyskać dostęp do wyników z kilku wyszukiwarek. Metawyszukiwarka wyświetla odpowiednie wyniki w postaci ponownie posortowanej listy wyników. MetaGer nie jest czystą metawyszukiwarką, ponieważ korzystamy również z własnych małych indeksów.',
        '4' => 'Bierzemy rankingi z naszych wyszukiwarek źródłowych i ważymy je. Rankingi te są następnie przekształcane w wyniki. Dodatkowe punkty są przyznawane lub odejmowane za występowanie wyszukiwanych terminów w adresie URL i we fragmencie, a także za nadmierne występowanie znaków specjalnych (np. innych zestawów znaków, takich jak cyrylica). Używamy również listy blokowania, aby usunąć poszczególne strony z listy wyników. Blokujemy wyświetlane strony internetowe, jeśli jesteśmy do tego prawnie zobowiązani. Zastrzegamy sobie również prawo do blokowania stron internetowych z ewidentnie nieprawdziwymi informacjami, stron internetowych o wyjątkowo niskiej jakości i innych szczególnie wątpliwych stron internetowych.',
        '5' => 'W razie jakichkolwiek dalszych pytań lub niejasności, prosimy o skorzystanie z naszego formularza kontaktowego <a href=":contact"></a> i zadawanie nam pytań!',
        'compliance' => 'Spełniamy żądania władz, jeśli jesteśmy do tego prawnie zobowiązani i dochodzimy do wniosku, że nasza zgodność nie narusza podstawowych wolności. Traktujemy tę weryfikację bardzo poważnie. Ponadto przechowujemy jak najmniej danych osobowych, aby zmniejszyć ryzyko konieczności ich ujawnienia. W poniższej tabeli znajdują się dane dotyczące wniosków od władz, które przetworzyliśmy w ciągu ostatnich 5 lat. Dalsze informacje pojawią się wkrótce.',
    ],
    'table' => [
        'compliance' => [
            'th' => [
                'authinfocomp' => 'Wypełnione wnioski o udzielenie informacji',
                'authblockcomp' => 'Zrealizowane żądania blokowania',
            ],
        ],
    ],
    'alt' => [
        'text' => [
            '1' => 'Wizualna reprezentacja dwóch indeksów, które wzajemnie się uzupełniają, tworząc metaindeks',
        ],
    ],
];
