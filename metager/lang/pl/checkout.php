<?php

/**
 * Aufladen (/konto/aufladen) — App\Http\Controllers\ChargeController.
 *
 * Bezahlt wird bei suma-payments: hier stehen nur noch die Zustimmung
 * (`suma`, `consent`), die Schrittleiste und Kurzfassung (`page`) und die
 * Rückkehrseite (`returned`). Die Texte der eigenen Seiten für Bar, Wero,
 * micropayment und PayPal sind mit ihnen gegangen (suma-crm
 * docs/keymanager-migration-plan.md, Schritt 7).
 */
return [
    'page' => [
        'change' => 'Zmień ilość',
        'cancel' => 'Powrót do konta',
        'steps' => [
            'aria_two' => 'Doładowanie w dwóch krokach',
            'amount' => 'Ilość',
            'pay' => 'Zapłać',
        ],
        'summary' => [
            'buying' => 'Kupujesz',
            'after' => 'Środki po doładowaniu',
            'price' => 'Cena',
        ],
    ],
    'consent' => [
        'agb' => 'Kontynuując zakup, użytkownik wyraża zgodę na nasze <a href=":agblink" target="_blank">Warunki</a>.',
        'label' => 'Wyrażam wyraźną zgodę na wykonanie umowy przed upływem terminu do odstąpienia od umowy. Rozumiem, że <a href=":revocation_link" target="_blank">prawo do odstąpienia od umowy</a> wygasa z chwilą rozpoczęcia wykonywania umowy. Zamiast tego przyznajemy Ci dobrowolne <a href=":refundlink" target="_blank">30-dniowe prawo zwrotu</a>.',
        'error' => 'To pole jest wymagane',
    ],
    'suma' => [
        'label' => 'Przelew, polecenie zapłaty i więcej',
        'heading' => 'Zapłać',
        'lede' => 'Na następnej stronie wybierasz sposób płatności: :methods. Nadal można pozostać anonimowym – imię i e-mail są dobrowolne, z wyjątkiem polecenia zapłaty.',
        'submit' => 'Przejdź do płatności',
        'privacy' => 'Płatność odbywa się przez strefę płatności SUMA-EV (payments.suma-ev.de) i, zależnie od metody, przez VR Payment lub PayPal.',
        'methods' => [
            'banktransfer' => 'Przelew bankowy',
            'sepa_directdebit' => 'Polecenie zapłaty SEPA',
            'card' => 'Karta kredytowa/debetowa',
            'wero' => 'Wero',
            'paypal' => 'PayPal',
            'cash' => 'Gotówka pocztą',
        ],
        'error' => [
            'unreachable' => 'Coś poszło nie tak podczas tworzenia zamówienia. Spróbuj ponownie później.',
        ],
    ],
    'returned' => [
        'heading' => 'Doładowanie zakończone',
        'pending_heading' => 'Płatność jest przetwarzana',
        'paid' => 'Dziękujemy! Twój klucz został doładowany o :amount tokenów.',
        'next' => 'Twoje środki są dostępne od razu — możesz już dalej wyszukiwać.',
        'details' => 'Zobacz szczegóły zamówienia',
        'pending' => 'Twoja płatność jest jeszcze przetwarzana. Gdy tylko do nas dotrze, Twój klucz zostanie automatycznie doładowany.',
    ],
];
