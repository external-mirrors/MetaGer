<?php
return [
    'lead' => 'Najszybciej odpowiadamy na e-maile.',
    'letter' => [
        '1' => 'Poczta',
        '3' => "SUMA-EV\r
Postfach 51 01 43\r
D-30631 Hannover\r
Niemcy",
    ],
    'headline' => [
        '1' => 'Kontakt',
        '2' => 'E-mail',
    ],
    'email' => [
        'write' => 'Napisz e-mail',
        'pgp' => [
            'summary' => 'Napisz zaszyfrowaną wiadomość (PGP)',
            'signed' => 'Każdy e-mail od nas jest podpisany naszym kluczem OpenPGP, który dołączamy też w nagłówku Autocrypt. Użyj tego klucza, aby napisać do nas w formie zaszyfrowanej:',
            'replies' => [
                'heading' => 'Zaszyfrowane odpowiedzi',
                'how' => 'Odpowiadamy automatycznie w formie zaszyfrowanej, gdy tylko mamy Twój klucz i mu ufamy. W tym celu podpisz swój e-mail do nas. Twój klucz bierzemy z wiadomości (jako załącznik lub z nagłówka Autocrypt) albo z keys.openpgp.org i używamy go samodzielnie, jeśli',
                'trusted_when' => [
                    'keys.openpgp.org udostępnia go dla Twojego adresu (adres jest tam więc potwierdzony), lub',
                    'Twój e-mail ma ważny podpis DKIM domeny nadawcy.',
                ],
                'otherwise' => 'W przeciwnym razie ktoś z naszego zespołu najpierw potwierdza go ręcznie. Od tej chwili szyfrujemy wszystko, co do Ciebie wysyłamy, nie tylko odpowiedzi.',
                'tofu' => 'Ufamy pierwszemu kluczowi, który otrzymamy w ten sposób dla Twojego adresu (Trust on First Use). Nowego klucza dla tego samego adresu nie przyjmujemy automatycznie: dopóki go nie sprawdzimy, szyfrujemy dalej poprzednim.',
                'never_plain' => 'Na zaszyfrowany e-mail nigdy nie odpowiadamy bez szyfrowania. Jeśli nie mamy Twojego klucza, nie możemy odpowiedzieć – dlatego zawsze podpisuj zaszyfrowane wiadomości do nas.',
            ],
            'pubkey' => 'PGP Publickey: <a href="/download/pubkey.asc" download="0x2185CC8F3CA782EC.asc">0x2185CC8F3CA782EC</a> lub na <a href=":keyserver" target="_blank" rel="noopener">keys.openpgp.org</a>',
            'fingerprint' => 'PGP Fingerprint: 5FA5 2398 C382 B498 B14A B7F6 2185 CC8F 3CA7 82EC',
        ],
    ],
    'removal' => [
        'hint' => 'Chodzi o usunięcie wyniku wyszukiwania? Najpierw przeczytaj <a href=":link">nasz poradnik</a>: MetaGer pokazuje wyniki innych wyszukiwarek, więc wynik można zwykle usunąć tylko tam.',
    ],
    'phone' => [
        'heading' => 'Telefon',
    ],
    'topics' => [
        'heading' => 'Częste pytania',
        'key' => [
            'title' => 'Klucz MetaGer i tokeny',
            'text' => 'Jak działa klucz, doładowanie i saldo',
        ],
        'orders' => [
            'title' => 'Zamówienia i faktury',
            'text' => 'Znajdź zamówienie, potwierdzenia i zwroty',
        ],
        'donation' => [
            'title' => 'Darowizna',
            'text' => 'Wesprzyj MetaGer darowizną',
        ],
        'membership' => [
            'title' => 'Zostań członkiem',
            'text' => 'Dołącz do SUMA-EV, stowarzyszenia stojącego za MetaGer',
        ],
        'help' => [
            'title' => 'Pomoc w wyszukiwaniu',
            'text' => 'Funkcje, ustawienia i częste pytania',
        ],
        'privacy' => [
            'title' => 'Prywatność',
            'text' => 'Co zapisujemy — a czego nie',
        ],
    ],
];
