<?php

return [
    'lookup' => [
        'heading' => 'Wyszukaj zamówienie',
        'description' => 'Wprowadź identyfikator płatności jednego ze swoich zamówień, aby wyświetlić jego szczegóły.',
        'placeholder' => 'Identyfikator płatności',
        'submit' => 'Pokaż zamówienie',
        'error' => [
            'invalid' => 'To nie jest prawidłowy identyfikator płatności.',
            'not_found' => 'Żadne zamówienie na Twoim kluczu nie pasuje do tego identyfikatora płatności.',
        ],
    ],

    'show' => [
        'heading' => 'Zamówienie :reference',
        'breadcrumb' => 'Zamówienia',
        'thanks' => 'Dziękujemy za zakup!',
        'pending' => 'Twoje tokeny zostaną zaksięgowane, gdy tylko Twoja płatność do nas dotrze. Otrzymasz wtedy wiadomość e-mail z potwierdzeniem.',
        'lookup_hint' => 'Możesz w każdej chwili ponownie otworzyć to podsumowanie, wpisując swój identyfikator płatności (:reference).',
        'order_line' => 'Zamówienie :id z :date',
        'item' => 'Klucz MetaGer: tokeny',
        'count' => 'Ilość',
        'price' => 'Cena',
        'vat' => 'VAT (:rate %)',
        'total' => 'Całkowita kwota',
        'exchange_rate' => 'Kurs wymiany',
        'download_confirmation' => 'Pobierz potwierdzenie zamówienia',
    ],
];
