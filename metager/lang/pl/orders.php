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
        'request_invoice' => 'Tworzenie faktury',
        'request_refund' => 'Poproś o zwrot pieniędzy',
    ],

    'invoice' => [
        'heading' => 'Faktura',
        'breadcrumb' => 'Zamówienie :reference',
        'description' => 'Jeśli potrzebujesz faktury, wprowadź swoje dane rozliczeniowe w poniższym formularzu.',
        'ready' => 'Dla tego zamówienia istnieje już faktura.',
        'download' => 'Pobierz fakturę',
        'submit' => 'Tworzenie faktury',
        'storage' => 'Jesteśmy prawnie zobowiązani do przechowywania raz wystawionych faktur <span class="bold">10 lat</span> długo. Ponieważ faktura musi być wystawiona osobiście na użytkownika, z konieczności zawiera dane osobowe (imię i nazwisko, adres).',
        'error' => [
            'invalid' => 'Sprawdź swoje dane — brakuje niektórych wymaganych pól lub są one zbyt długie.',
        ],
        'field' => [
            'company' => 'Nazwa firmy (opcjonalnie)',
            'first_name' => 'Imię',
            'last_name' => 'Nazwisko',
            'address1' => 'Adres 1',
            'address2' => 'Adres 2 (opcjonalnie)',
            'zip' => 'Kod pocztowy',
            'city' => 'Miasto',
            'state' => 'Państwo (opcjonalnie)',
        ],
    ],

    'refund' => [
        'heading' => 'Zwrot pieniędzy',
        'breadcrumb' => 'Zamówienie :reference',
        'unavailable' => 'Dla tego zamówienia nie pozostało już żadne saldo podlegające zwrotowi — albo zwrot pieniędzy został już zgłoszony, albo użyta metoda płatności nie obsługuje wniosku o zwrot pieniędzy za pomocą tego formularza.',
        'success' => 'Twoje zgłoszenie zostało do nas pomyślnie wysłane. Przetworzymy je tak szybko, jak to możliwe. W zależności od metody płatności może minąć kilka dni, zanim zwrot będzie widoczny w sprzedaży.',
        'description' => 'Jesteś niezadowolony ze swojego klucza? Bardzo nam przykro to słyszeć! Oczywiście w takim przypadku zwrócimy kwotę faktury. Zwrot jest zawsze dokonywany na to samo konto, które zostało użyte do pierwotnej płatności. Cieszymy się również z krytyki.',
        'partial_note' => 'Uwaga: część zakupionych środków została już wykorzystana. W związku z tym możemy zwrócić tylko <span class="bold">:count</span> z <span class="bold">:total</span> wyszukiwania.',
        'message' => [
            'label' => 'Twoja wiadomość (opcjonalnie)',
        ],
        'submit' => ':amount € Żądanie zwrotu pieniędzy',
        'error' => [
            'not_allowed' => 'Zwrot pieniędzy dla tego zamówienia nie jest już możliwy.',
            'unreachable' => 'Błąd wysyłania wiadomości. Spróbuj ponownie później.',
        ],
    ],
];
