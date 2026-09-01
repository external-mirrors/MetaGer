<?php

/**
 * Aufladen (/konto/aufladen) — App\Http\Controllers\ChargeController.
 *
 * `cash`, `consent` und micropayment.prepay sind wortgleich aus dem Keymanager
 * übernommen (dessen checkout.json unter payments.cash/payments.prepay/
 * micropayment, dessen order.json unter agb und revocation, je Sprache) —
 * dieselbe Kasse, jetzt hier gerendert. `page`, `manual`,
 * micropayment.lastschrift/directbanking (dort nur Logos, keine Texte),
 * `returned` und vrpayment.label/submit/error.failed sind neu; vrpayment.privacy
 * ist wortgleich aus dem Keymanager übernommen wie cash/consent/micropayment.
 */
return [
    'page' => [
        'change' => 'Zmień ilość',
        'methods' => [
            'heading' => 'Wybierz metodę płatności',
            'more' => 'Więcej metod płatności',
            'back' => 'Wybierz inną metodę płatności',
            'cash_note' => 'Anonimowo',
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

    'vrpayment' => [
        'label' => 'Wero',
        'submit' => 'Dokonaj płatności',
        'privacy' => 'Kliknięcie przycisku "Dokonaj płatności" spowoduje przekierowanie do naszego dostawcy usług płatniczych <a href="https://www.vr-payment.de" target="_blank">VR Payment</a> w celu sfinalizowania zakupu. Więcej o prywatności <a href=":link" target="_blank">na stronie VR Payment</a>.',
        'error' => [
            'failed' => 'VR Payment odrzucił tę płatność. Spróbuj ponownie lub wybierz inną metodę płatności.',
            'onion' => 'Wero nie jest dostępne pod naszym adresem onion — dostawca płatności nie może później odesłać Cię z powrotem tutaj. Wybierz inną metodę płatności.',
        ],
    ],

    'paypal' => [
        'heading' => 'Dokonaj płatności',
        'submit' => 'Dokonaj płatności',
        'loading' => 'Metoda płatności jest załadowana',
        'cancel' => 'Proces płatności został anulowany. Jeśli płatność została zrealizowana przed anulowaniem, zamówienie zostanie przetworzone, gdy tylko płatność zostanie potwierdzona przez procesor płatności. W przeciwnym razie spróbuj ponownie.',
        'privacy' => 'Metody płatności w tej grupie zwykle nie wymagają konta PayPal, ale są tam przetwarzane. Więcej informacji na temat <a href="https://www.paypal.com/us/legalhub/privacy-full" target="_blank">prywatności w PayPal</a>.',
        'noscript' => 'Ta metoda płatności wymaga JavaScript. Wybierz inną metodę płatności lub włącz JavaScript.',
        'funding' => [
            'paypal' => 'PayPal',
            'card' => 'Karta kredytowa / debetowa',
            'p24' => 'Przelewy24',
            'bancontact' => 'Bancontact',
            'blik' => 'BLIK',
            'eps' => 'EPS',
            'mybank' => 'MyBank',
        ],
        'error' => [
            'not_available' => 'Przepraszamy, wybrana metoda płatności nie jest dostępna w Twoim regionie.',
            'generic' => 'Proces płatności został anulowany z powodu błędu.  Jeśli płatność została zrealizowana przed anulowaniem, zamówienie zostanie przetworzone, gdy tylko płatność zostanie potwierdzona przez procesor płatności. W przeciwnym razie spróbuj ponownie.',
        ],
        'card' => [
            'label' => 'Karta kredytowa / debetowa',
            'name' => 'Nazwa posiadacza karty (opcjonalnie)',
            'number' => 'Numer karty',
            'expiration' => 'Ważne do',
            'cvv' => 'CVV',
            'error' => [
                '9500' => 'Karta kredytowa odrzucona jako fałszywa',
                '5100' => 'Karta kredytowa została odrzucona przez instytucję kredytową',
                '00N7' => 'Nieprawidłowy kod CVV. Sprawdź wprowadzone dane',
                '5400' => 'Karta kredytowa wygasła',
                '5180' => 'Kontrola Luhna nie powiodła się',
                '5120' => 'Karta kredytowa została odrzucona z powodu niewystarczających środków.',
                '9520' => 'Karta kredytowa odrzucona jako zagubiona/skradziona',
                '0500' => 'Karta kredytowa odrzucona przez instytucję kredytową',
                '1330' => 'Karta kredytowa jest nieważna. Sprawdź swoje zgłoszenie',
                '3ds' => 'Uwierzytelnianie 3D nie powiodło się',
                'generic' => 'Karta kredytowa odrzucona przez instytucję kredytową',
            ],
        ],
    ],
    'returned' => [
        'heading' => 'Doładowanie zakończone',
        'paid' => 'Dziękujemy! Twój klucz został doładowany o :amount tokenów.',
        'next' => 'Twoje środki są dostępne od razu — możesz już dalej wyszukiwać.',
        'pending' => 'Twoja płatność jest jeszcze przetwarzana. Gdy tylko do nas dotrze, Twój klucz zostanie automatycznie doładowany.',
    ],
];
