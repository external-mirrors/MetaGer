<?php
return [
    'title' => 'Usuwanie wyniku wyszukiwania – MetaGer',
    'meta_description' => 'Jak usunąć wynik wyszukiwania: prawo do bycia zapomnianym, nieaktualne treści, prawo autorskie. Gdzie złożyć wniosek: Google, Brave i inne.',
    'heading' => 'Usuwanie wyniku wyszukiwania',
    'lead' => 'MetaGer to metawyszukiwarka: nie mamy własnego indeksu, tylko pokazujemy to, co znajdują inne wyszukiwarki. Dlatego wynik można naprawdę usunąć tylko tam, skąd pochodzi: na stronie internetowej i w wyszukiwarkach, których wyniki pokazuje MetaGer. Ta strona wyjaśnia, gdzie to zrobić.',
    'toc' => [
        'label' => 'Na tej stronie',
    ],
    'website' => [
        'heading' => 'Zwróć się do samej witryny',
        'text' => 'To jedyna droga, która działa wszędzie. Gdy strona zostanie usunięta lub zmieniona, każda wyszukiwarka usunie ją lub zaktualizuje przy kolejnej wizycie, także MetaGer, i nie musisz prosić o to każdej z osobna.',
        'tips' => [
            'Większość witryn podaje adres kontaktowy w nocie prawnej lub w polityce prywatności. Poproś tam o usunięcie treści, jej sprostowanie albo przynajmniej usunięcie Twojego nazwiska.',
            'W przypadku danych osobowych witryny w UE muszą odpowiedzieć na wniosek na podstawie art. 17 RODO (prawo do usunięcia danych).',
            'Jeśli sam prowadzisz witrynę: usuń stronę tak, aby odpowiadała kodem „404” lub „410”, albo oznacz ją jako „noindex”. Wyszukiwarki usuną ją wtedy same.',
        ],
        'sources' => 'W przypadku wyników z :sources to jedyna droga.',
    ],
    'kind' => [
        'heading' => 'O co chodzi?',
        'intro' => 'Jeśli witryna nie pomaga albo nie możesz czekać, wyszukiwarki mają własne procedury. To, która jest właściwa, zależy od tego, co chcesz osiągnąć.',
        'personal_data' => [
            'summary' => 'Dane osobowe: prawo do bycia zapomnianym',
            'text' => 'Wynik dla Twojego nazwiska prowadzi do informacji o Tobie, które są nieprawdziwe, nieaktualne, nieistotne lub nadmierne. Na podstawie art. 17 RODO możesz poprosić wyszukiwarkę, by przestała pokazywać ten wynik przy wyszukiwaniu Twojego nazwiska. Wyszukiwarka waży Twój interes wobec interesu publicznego w dostępie do informacji, dlatego nie każdy wniosek kończy się powodzeniem. Na kartach poniżej znajdziesz to pod hasłem „Dane osobowe”.',
        ],
        'outdated' => [
            'summary' => 'Nieaktualne treści',
            'text' => 'Witryna usunęła już lub zmieniła stronę, ale wynik nadal pokazuje stary tytuł lub tekst. Rozwiąże się to samo przy kolejnej wizycie wyszukiwarki; w większości z nich możesz też poprosić o wcześniejszą aktualizację. Na kartach poniżej znajdziesz to pod hasłem „Nieaktualne treści”.',
        ],
        'legal' => [
            'summary' => 'Prawo autorskie i inne przyczyny prawne',
            'text' => 'Wynik narusza Twoje prawa autorskie lub inne prawa. Wyszukiwarki przyjmują takie zgłoszenia przez własne formularze prawne; zazwyczaj może je złożyć tylko uprawniony albo osoba działająca w jego imieniu. Na kartach poniżej znajdziesz to pod hasłem „Prawo autorskie i kwestie prawne”.',
        ],
        'illegal' => [
            'summary' => 'Treści nielegalne',
            'text' => 'Materiały przedstawiające seksualne wykorzystywanie dzieci, treści szkodliwe dla małoletnich, nawoływanie do nienawiści i podobne treści zgłoś najpierw do punktu zgłaszania. Działa on przeciwko samej treści u jej dostawcy hostingu i współpracuje z policją. Jeśli chcesz też, aby MetaGer przestał pokazywać taki wynik, wyślij nam tylko jego adres (URL) jako tekst. Nigdy nie wysyłaj nam samej treści, także zrzutów ekranu ani załączników.',
            'hotlines' => 'Punkty zgłaszania:',
        ],
    ],
    'providers' => [
        'heading' => 'Zwróć się do wyszukiwarek, które pokazuje MetaGer',
        'text' => 'MetaGer nie przeszukuje internetu sam. Pytamy wyszukiwarki wymienione poniżej w chwili, gdy szukasz, pokazujemy to, co zwrócą, i przechowujemy ich odpowiedź w pamięci podręcznej przez kilka godzin. Gdy jedna z nich usunie wynik, znika on też z tego, co MetaGer od niej otrzymuje.',
        'which' => 'Z której wyszukiwarki pochodzi wynik, widać pod wynikiem na stronie wyników MetaGer. Jeśli pochodzi z kilku, zwróć się do każdej z nich.',
        'shows' => 'W MetaGer w:',
        'kinds' => [
            'personal_data' => 'Dane osobowe',
            'outdated' => 'Nieaktualne treści',
            'legal' => 'Prawo autorskie i kwestie prawne',
        ],
        'no_form' => 'Brak formularza, kontakt: :name',
        'contact' => 'Kontakt: :name',
        'us' => 'Do formularza',
        'google' => 'MetaGer otrzymuje wyniki Google przez usługę Serper. To, co Google usunie ze swojego indeksu, znika potem także z MetaGer.',
        'brave' => 'Brave Search ma własny indeks. Wnioski dotyczące danych osobowych wysyła się e-mailem na adres privacy@brave.com z tematem „RTBF request”; podlinkowana strona wyjaśnia, co musi zawierać wiadomość.',
        'mojeek' => 'Mojeek ma własny indeks i nie edytuje wyników ręcznie. Z przyczyn prawnych usuwa strony na wniosek; napisz przez jego stronę kontaktową i podaj dokładne adresy.',
        'staan' => 'Staan to interfejs wyszukiwania European Search Perspective. Wnioski dotyczące danych osobowych przyjmuje przez własny formularz; wypełnij każde pole i dołącz dokumenty potwierdzające. Usunięcie dotyczy tylko indeksu Staan.',
        'polias' => 'Polias ma własny indeks i nie ma formularza. Napisz na adres hallo@polias.eu, podając adres strony i powód.',
        'pixabay' => 'Pixabay sam udostępnia obrazy. Skorzystaj z formularza zgłoszeniowego lub przycisku zgłoszenia na stronie obrazu w Pixabay.',
        'suma' => 'TootNews prowadzi SUMA-EV, stowarzyszenie stojące za MetaGer. Same wpisy znajdują się na serwerach Mastodona; zwróć się najpierw tam, a potem skorzystaj z naszego formularza.',
        'website' => 'BASE pokazuje zawartość przeszukiwanych repozytoriów. Gdy repozytorium usunie rekord, znika on także z BASE.',
    ],
    'us' => [
        'heading' => 'Usuwanie z naszych własnych wyszukiwarek',
        'text' => 'W przypadku własnych wyszukiwarek SUMA-EV możesz złożyć wniosek o usunięcie bezpośrednio u nas: Nolm oraz TootNews / TrötNews. Tak wygląda wniosek:',
        'steps' => [
            'Wybierasz wyszukiwarkę, której dotyczy sprawa, i potwierdzasz swój adres e-mail przez link, który Ci wysyłamy.',
            'Przesyłasz dowód tożsamości i dla każdej strony podajesz adres, wyszukiwane hasła i powód.',
            'Najpierw sprawdzamy dowód, a potem każdą stronę. Jeśli przysługuje Ci roszczenie, na przykład na podstawie art. 17 RODO przy wyszukiwaniu Twojego nazwiska, ukrywamy wynik w tej wyszukiwarce. Na witrynie i we wszystkich innych wyszukiwarkach pozostaje on bez zmian.',
            'Odpowiadamy w ciągu miesiąca. Jeśli wniosek jest szczególnie skomplikowany, może to potrwać do dwóch miesięcy dłużej; poinformujemy Cię o tym w ciągu pierwszego miesiąca. Jeśli nie ukryjemy wyniku, podamy Ci powody.',
        ],
        'include' => 'Potrzebujemy dokładnych adresów (URL) pojedynczych stron. Nie możemy usuwać całych witryn ani „wszystkiego o moim nazwisku”.',
        'identity' => 'Jako dowód potrzebujemy kopii Twojego dokumentu tożsamości, a przy wniosku w imieniu innej osoby także pełnomocnictwa i kopii jej dokumentu tożsamości. W przeciwnym razie każdy mógłby żądać ukrycia dowolnych adresów. Na kopii możesz zaczernić wszystko, czego nie potrzebujemy do weryfikacji. Przechowujemy ją w postaci zaszyfrowanej i usuwamy 6 miesięcy po zakończeniu sprawy wraz z pozostałymi Twoimi danymi.',
        'contact' => 'Do formularza',
    ],
];
