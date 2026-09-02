<?php

return [
    'heading' => 'Akcje z bonami',
    'description' => 'Rozdawaj klucze z własnego salda tokenów, na przykład znajomym lub współpracownikom. Rozdane klucze odejmują tokeny z Twojego klucza dopiero, gdy zostaną faktycznie użyte – nieużyte prezenty nic Cię nie kosztują.',
    'unreachable' => 'Nie udało się teraz wczytać Twoich akcji z bonami. Spróbuj ponownie później.',
    'copy_link' => 'Kopiuj link',
    'public_link' => 'Link publiczny',
    'delete_note' => 'Wygasłe i wyłączone akcje są usuwane automatycznie.',
    'print_cards' => 'Drukuj karty (PDF)',
    'disable' => 'Wyłącz',
    'delete' => 'Usuń teraz',

    'status' => [
        'active' => 'aktywna',
        'disabled' => 'wyłączona',
        'expired' => 'wygasła',
    ],

    'facts' => [
        'tokens_per_key' => ':tokens tokenów na klucz',
        'redeemed' => 'wykorzystano :redeemed z :total',
        'budget' => 'pozostało :left z :total tokenów',
        'expires' => 'kończy się :date',
    ],

    'create' => [
        'heading' => 'Utwórz akcję',
        'info' => 'Akcja jest zabezpieczona tym kluczem: rozdane tokeny są odejmowane z Twojego salda w momencie użycia. Akcje trwają 3 miesiące, rozdane klucze są ważne 1 miesiąc po wykorzystaniu.',
        'name' => 'Nazwa (widoczna tylko dla Ciebie)',
        'tokens_per_key' => 'Tokeny na rozdany klucz',
        'total_volume' => 'Maksymalna łączna liczba tokenów',
        'total_volume_hint' => 'Twój klucz zawiera obecnie :charge tokenów. Nigdy nie możesz rozdać więcej niż wynosi Twoje saldo.',
        'voucher_count' => 'Liczba bonów (opcjonalnie)',
        'voucher_count_hint' => 'Domyślnie: maksymalna łączna liczba podzielona przez tokeny na klucz.',
        'submit' => 'Utwórz akcję',
        'error' => [
            'tokens_per_key_too_high' => 'Liczba tokenów na klucz nie może przekraczać maksymalnej łącznej liczby.',
            'voucher_count_out_of_range' => 'Liczba bonów nie pasuje do tokenów na klucz i maksymalnej łącznej liczby.',
            'over_budget' => 'Maksymalna łączna liczba przekracza Twoje dostępne saldo.',
            'too_many_active' => 'Masz już maksymalną liczbę aktywnych akcji.',
            'invalid' => 'Nie udało się utworzyć akcji. Sprawdź wprowadzone dane.',
            'unreachable' => 'Nie udało się teraz utworzyć akcji. Spróbuj ponownie później.',
        ],
    ],

    /**
     * /c — App\Http\Controllers\VoucherController.
     */
    'redeem' => [
        'enter' => [
            'heading' => 'Zrealizuj bon',
            'description' => 'Otrzymałeś kod bonu na darmowe wyszukiwania w MetaGer? Wpisz go tutaj, aby otrzymać swój osobisty klucz MetaGer.',
            'label' => 'Twój kod bonu',
            'submit' => 'Zrealizuj kod',
            'invalid_code' => 'Ten kod jest nieprawidłowy. Sprawdź wprowadzone dane.',
            'rate_limited' => 'Zbyt wiele prób. Spróbuj ponownie później.',
        ],
        'teaser' => [
            'heading' => 'Twój prezent od MetaGer',
            'tokens' => 'Tokeny',
            'description' => 'Ten kod daje Ci własny klucz MetaGer z :tokens tokenami - przeszukuj internet bez reklam i bez śledzenia.',
            'validity' => 'Klucz jest ważny przez :days dni po realizacji.',
            'submit' => 'Odbierz mój klucz',
        ],
        'redeemed' => [
            'heading' => 'Oto Twój klucz MetaGer!',
            'description' => 'Twój nowy klucz ma :tokens tokenów.',
            'save' => [
                'heading' => '1. Zapisz swój klucz',
                'description' => 'Twój klucz to Twoje dane logowania - jest wyświetlany tylko tutaj i nie da się go odzyskać. Zapisz go w menedżerze haseł, pobierz kod QR lub wydrukuj tę stronę.',
            ],
            'copy_key' => 'Kopiuj klucz',
            'validity' => 'Klucz jest ważny do :date.',
            'use' => [
                'heading' => '2. Zacznij wyszukiwać',
                'description' => 'Otwórz ten link, aby aktywować klucz w swojej przeglądarce. Dodaj go do zakładek, aby pozostać zalogowanym.',
            ],
            'copy_url' => 'Kopiuj link',
            'start_searching' => 'Zacznij wyszukiwać teraz',
            'to_account' => 'Przejdź do mojego konta',
            'qr_alt' => 'Kod QR do klucza',
            'no_cookies' => 'Ta przeglądarka nie wydaje się zapisywać plików cookie. Zamiast tego zapisz klucz lub powyższy kod QR.',
        ],
        'error' => [
            'heading' => 'To nie zadziałało',
            'invalid_code' => 'Ten kod nie istnieje. Sprawdź wprowadzone dane.',
            'invalid_token' => 'Ten link jest nieprawidłowy lub wygasł.',
            'already_redeemed' => 'Ten kod został już zrealizowany.',
            'campaign_inactive' => 'Ta akcja się zakończyła. Kodu nie można już zrealizować.',
            'budget_exhausted' => 'Wszystkie prezenty z tej akcji zostały już rozdane.',
            'rate_limited' => 'Zbyt wiele prób. Spróbuj ponownie później.',
            'unreachable' => 'Nie udało się teraz zrealizować bonu. Spróbuj ponownie później.',
            'unknown' => 'Wystąpił nieoczekiwany błąd. Spróbuj ponownie później.',
            'retry' => 'Wpisz kod',
        ],
    ],
];
