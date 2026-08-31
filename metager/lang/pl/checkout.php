<?php

/**
 * Aufladen (/konto/aufladen) — App\Http\Controllers\ChargeController.
 *
 * `cash`, `consent` und micropayment.prepay sind wortgleich aus dem Keymanager
 * übernommen (dessen checkout.json unter payments.cash/payments.prepay/
 * micropayment, dessen order.json unter agb und revocation, je Sprache) —
 * dieselbe Kasse, jetzt hier gerendert. `page`, `manual`,
 * micropayment.lastschrift/directbanking (dort nur Logos, keine Texte) und
 * `returned` sind neu.
 */
return [
    'page' => [
        'change' => 'Zmień ilość',
        'methods' => [
            'heading' => 'Wybierz metodę płatności',
            'more' => 'Więcej metod płatności',
            'back' => 'Wybierz inną metodę płatności',
        ],
        'cancel' => 'Powrót do konta',
    ],

    'cash' => [
        'label' => 'Gotówka',
        'description' => 'Możesz również doładować swój klucz za gotówkę. W tym celu wystarczy przesłać nam pocztą poniższy numer zamówienia wraz z żądaną kwotą pieniędzy. Należy pamiętać, że numer zamówienia musi być czytelny, aby mógł zostać przez nas przetworzony.',
        'note' => 'Należy pamiętać o następujących kwestiach:',
        'no_large_values' => 'Dla własnego bezpieczeństwa nie wysyłaj nam więcej niż 100 € pocztą. Nie ponosimy żadnej odpowiedzialności za trasę transportu. Użytkownik jest odpowiedzialny za zapewnienie, że list do nas dotrze.',
        'no_coins' => 'Akceptujemy tylko banknoty. Nie wysyłaj monet!',
        'accepted_currencies' => 'Akceptujemy tylko następujące waluty: EUR, USD, CAD, GBP.',
        'currency_translation' => 'Zawsze pobieramy opłaty w EUR. Jeśli wyślesz nam inną walutę, wysłana kwota zostanie przeliczona po dziennym kursie wymiany',
        'no_refund' => 'Ze względu na obowiązujące przepisy dotyczące prania brudnych pieniędzy, zwrot pieniędzy nie jest niestety możliwy. Jednak po zaksięgowaniu przez nas opłaty można wprowadzić wysłany identyfikator płatności w sekcji "Zamówienia", aby uzyskać przegląd zamówienia i/lub poprosić o fakturę.',
        'generate' => 'Generowanie identyfikatora płatności',
        'error' => [
            'unreachable' => 'Coś poszło nie tak podczas tworzenia zamówienia. Spróbuj ponownie później.',
        ],
        'order' => [
            'heading' => 'Identyfikator płatności',
            'copy' => 'Kopia identyfikatora płatności',
            'address_heading' => 'Wyślij pismo na poniższy adres i zanotuj identyfikator płatności do własnych akt',
            'address' => 'SUMA-EV
Postfach 51 01 43
30631 Hanower
Niemcy',
            'expiration' => 'Identyfikator płatności jest ważny do :date. Po tym terminie nie będzie można go użyć do doładowania.',
            'unique' => 'Użyj identyfikatora płatności tylko do jednego doładowania. Otrzymasz nowy za każdym razem, gdy odwiedzisz tę stronę!',
        ],
    ],

    'consent' => [
        'agb' => 'Kontynuując zakup, użytkownik wyraża zgodę na nasze <a href=":agblink" target="_blank">Warunki</a>.',
        'label' => 'Wyrażam wyraźną zgodę na wykonanie umowy przed upływem terminu do odstąpienia od umowy. Rozumiem, że <a href=":revocation_link" target="_blank">prawo do odstąpienia od umowy</a> wygasa z chwilą rozpoczęcia wykonywania umowy. Zamiast tego przyznajemy Ci dobrowolne <a href=":refundlink" target="_blank">30-dniowe prawo zwrotu</a>.',
        'error' => 'To pole jest wymagane',
    ],

    'manual' => [
        'label' => 'Ręcznie (dev)',
        'description' => 'Pomiń rzeczywistą płatność. Dostępne tylko w środowisku deweloperskim.',
        'submit' => 'Zakończ płatność',
    ],

    'micropayment' => [
        'label' => 'Micropayment',
        'prepay' => [
            'label' => 'Przelew bankowy',
            'email' => [
                'label' => 'Adres e-mail',
                'description' => 'Na ten adres zostanie wysłana jednorazowa informacja o naszych danych bankowych oraz powiadomienie o zakończeniu płatności.',
            ],
        ],
        'lastschrift' => ['label' => 'Polecenie zapłaty'],
        'directbanking' => ['label' => 'Natychmiastowy przelew bankowy'],
        'submit' => 'Dokonaj płatności',
        'privacy' => 'Kliknięcie przycisku "Dokonaj płatności" spowoduje przekierowanie do naszego dostawcy usług płatniczych <a href="https://micropayment.de" target="_blank">MicroPayment</a> w celu sfinalizowania zakupu. Więcej o prywatności <a href=":link" target="_blank">na :link_text</a>.',
    ],

    'returned' => [
        'heading' => 'Doładowanie zakończone',
        'paid' => 'Dziękujemy! Twój klucz został doładowany o :amount tokenów.',
        'pending' => 'Twoja płatność jest jeszcze przetwarzana. Gdy tylko do nas dotrze, Twój klucz zostanie automatycznie doładowany.',
    ],
];
