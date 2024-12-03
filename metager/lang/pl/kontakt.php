<?php
return [
    'form' => [
        '2' => 'Możesz wysłać nam anonimową wiadomość za pomocą tego formularza. Jeśli zdecydujesz się nie podawać swojego adresu e-mail, oczywiście nie otrzymasz odpowiedzi.',
        'name' => 'Nazwa',
        '5' => 'Twój adres e-mail (opcjonalnie)',
        '6' => 'Twoja wiadomość',
        '7' => 'Przedmiot',
        '9' => 'Do 5 załączników (rozmiar pliku < 5 MB)',
        '1' => 'Anonimowy formularz kontaktowy',
        '8' => 'Wyślij',
    ],
    'letter' => [
        '1' => 'Listownie',
        '2' => 'Preferujemy kontakt cyfrowy. Jeśli jednak uznasz za konieczne skontaktowanie się z nami drogą pocztową, możesz wysłać do nas wiadomość na adres:',
        '3' => "SUMA-EV\r
Postfach 51 01 43\r
D-30631 Hannover\r
Niemcy",
    ],
    'error' => [
        '1' => 'Przepraszamy, ale niestety nie otrzymaliśmy żadnych danych z prośbą o kontakt. Wiadomość nie została wysłana.',
        '2' => 'Wystąpił błąd podczas dostarczania wiadomości. Możesz skontaktować się z nami bezpośrednio pod adresem :email',
    ],
    'success' => [
        '1' => 'Wiadomość została dostarczona pomyślnie. Pierwsza automatyczna odpowiedź została wysłana na adres :email.',
    ],
    'headline' => [
        '1' => 'Kontakt',
        '2' => 'E-mail',
        'pgp' => 'Szyfrowanie',
    ],
    'email' => [
        'text' => 'Możesz skontaktować się z nami wysyłając wiadomość na adres: <a href="mailto::mail">:mail</a>',
        'pgp' => [
            'description' => 'Nasze wiadomości e-mail są podpisywane kryptograficznie. Jeśli chcesz zweryfikować podpis lub wysłać zaszyfrowaną wiadomość, użyj poniższego klucza publicznego. Jeśli chcesz otrzymać zaszyfrowaną odpowiedź, dołącz swój klucz publiczny do zaszyfrowanej i podpisanej wiadomości.',
            'pubkey' => 'PGP Publickey: <a href="/download/pubkey.asc" download="0x2185CC8F3CA782EC.asc">0x2185CC8F3CA782EC</a> lub na <a href=":keyserver" target="_blank" rel="noopener">keys.openpgp.org</a>',
            'fingerprint' => 'PGP Fingerprint: 5FA5 2398 C382 B498 B14A B7F6 2185 CC8F 3CA7 82EC',
        ],
    ],
];
