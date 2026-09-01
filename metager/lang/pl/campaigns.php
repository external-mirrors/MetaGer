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
];
