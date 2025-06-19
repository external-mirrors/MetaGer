<?php
return [
    'title' => 'Twoje członkostwo w SUMA-EV',
    'back' => 'Powrót do strony startowej',
    'non-de' => 'Niestety, obecnie możemy przyjmować tylko wnioski o przyjęcie z krajów niemieckojęzycznych. Zachęcamy do wsparcia nas darowizną <a href=":donationlink"></a> .',
    'application' => [
        'payment_block' => 'Spróbujemy autoryzować płatność za następną opłatę członkowską, aby zweryfikować metodę płatności, ale płatność zostanie zrealizowana tylko wtedy, gdy będzie należna w ciągu najbliższych dwóch tygodni, a w przeciwnym razie zostanie anulowana.',
        'cancel' => [
            'application' => 'Usuń wniosek o członkostwo',
            'update' => 'Odrzuć zmiany',
        ],
        'update_hint' => 'Żądane zmiany dotyczące członkostwa zostaną wkrótce sprawdzone/zaakceptowane. Jeśli jesteś zadowolony z wyświetlonego stanu, możesz opuścić tę stronę. W przeciwnym razie możesz wprowadzić więcej zmian lub usunąć żądanie zmiany za pomocą przycisku poniżej.',
        'description' => 'Dziękujemy za rozważenie członkostwa <a href="https://suma-ev.de/en/mitglieder/" target="_blank"></a> w naszym stowarzyszeniu non-profit. W celu przetworzenia wniosku potrzebujemy tylko kilku informacji, które można wypełnić tutaj.',
    ],
    'data' => [
        'description' => 'Zarejestrowaliśmy następujące dane dla Twojej aplikacji:',
        'company' => "Nazwa firmy",
        'amount' => "Opłata członkowska",
        'payment_method' => "Metoda płatności",
        'payment_methods' => [
            'directdebit' => "Polecenie zapłaty",
            'card' => "Karta kredytowa",
            'banktransfer' => "Przelew bankowy",
            'paypal' => "PayPal",
        ],
        'payment' => [
            'interval' => [
                'six-monthly' => "Co pół roku",
                'annual' => "rocznie",
                'monthly' => "miesięcznik",
                'quarterly' => "kwartalnik",
            ],
        ],
        'name' => 'Nazwa',
        'email' => 'Adres e-mail',
    ],
    'key' => [
        'description' => 'Aby korzystać z MetaGer, następujący klucz jest używany i doładowywany przez nas. Jeśli byłeś już zalogowany, Twój istniejący klucz został użyty.',
        'later' => 'Pierwsze doładowanie ma miejsce po rozpatrzeniu wniosku.',
        'now' => 'Jest już naładowany i może być natychmiast użyty.',
    ],
    'success' => 'Dziękujemy za przesłanie wniosku o członkostwo. Przetworzymy go tak szybko, jak to możliwe. Następnie otrzymasz od nas wiadomość e-mail z dalszymi informacjami na podany adres.',
];
