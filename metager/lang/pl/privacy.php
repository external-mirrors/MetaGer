<?php
return [
    'contexts' => [
        'newsletter' => [
            'contact' => 'Używamy tych danych wyłącznie do wysyłania naszego newslettera i w żadnym wypadku nie przekazujemy ich stronom trzecim.',
            'title' => 'Zarejestruj się, aby otrzymywać biuletyn SUMA-EV',
            'description' => 'Aby informować użytkowników o naszych działaniach, oferujemy biuletyn e-mailowy. Przechowujemy następujące dane do momentu rezygnacji z subskrypcji:',
        ],
        'maps' => [
            'title' => 'Korzystanie z Maps.MetaGer.de',
            'description' => 'Podczas korzystania z usługi mapy MetaGer generowane są następujące dane:',
            'ip' => 'Używamy Twojego adresu IP, aby oszacować dobrą lokalizację początkową, na której początkowo skupimy mapę. W tym celu adres IP użytkownika jest przetwarzany lokalnie. Wyniki nie są nigdzie przechowywane i zostaną natychmiast usunięte po zgłoszeniu żądania.',
        ],
        'proxy' => [
            'title' => 'Korzystanie z anonimizującego serwera proxy',
            'description' => 'Podczas korzystania z anonimizującego serwera proxy generowane są następujące dane:',
        ],
        'quote' => [
            'title' => 'Korzystanie z wyszukiwania cytatów',
            'description' => 'Wprowadzony termin wyszukiwania jest używany do wyszukiwania wyników w bazie danych cytowań. W przeciwieństwie do wyszukiwania w MetaGer, nie jest konieczne przekazywanie wyszukiwanego hasła stronom trzecim, ponieważ baza danych cytatów znajduje się na naszym serwerze. Inne dane nie będą zapisywane ani przekazywane.',
        ],
        'asso' => [
            'title' => 'Korzystanie z asocjatora',
            'description' => 'Asocjator używa wyszukiwanego hasła do określenia i wyświetlenia powiązanych z nim haseł. Inne dane nie będą zapisywane ani przekazywane.',
        ],
        'donate' => [
            'payment' => 'Dane dotyczące płatności zostaną wykorzystane wyłącznie do przetworzenia darowizny i w żadnym wypadku nie zostaną przekazane stronom trzecim. Ze względów podatkowych jesteśmy zobowiązani do przechowywania tych danych przez 10 lat. Następnie zostaną one automatycznie usunięte i nie będą dalej przetwarzane.',
            'message' => 'Wiadomość wprowadzona tutaj zostanie przesłana do nas i uwzględniona podczas przetwarzania darowizny.',
            'title' => 'Korzystanie z formularza darowizny',
            'description' => 'Następujące dane przekazane w formularzu darowizny będą przechowywane przez 2 miesiące w celu ich przetworzenia:',
            'contact' => 'Używamy tych danych wyłącznie do ewentualnych zapytań i w żadnym wypadku nie przekazujemy ich stronom trzecim.',
        ],
        'key' => [
            'title' => 'Sprawdź klucz MetaGer',
            'contact' => 'Używamy tych danych wyłącznie do ewentualnych zapytań lub fakturowania i w żadnym wypadku nie przekazujemy ich stronom trzecim.',
            'payment' => 'Dane dotyczące płatności zostaną wykorzystane wyłącznie do przetworzenia darowizny i w żadnym wypadku nie zostaną przekazane stronom trzecim. Ze względów podatkowych jesteśmy zobowiązani do przechowywania tych danych przez 10 lat. Następnie zostaną one automatycznie usunięte i nie będą dalej przetwarzane.',
        ],
        'suma' => [
            'title' => 'Korzystanie ze strony internetowej <a href="https://suma-ev.de">suma-ev.de</a>',
            'description' => 'Podczas odwiedzania stron internetowych domeny "suma-ev.de" gromadzone i przechowywane przez okres do jednego tygodnia są następujące dane:',
            'function' => 'Podczas odwiedzania stron internetowych domeny "suma-ev.de" gromadzone i przechowywane przez okres do jednego tygodnia są następujące dane:',
            'other' => 'Na innych stronach internetowych naszych domen przetwarzamy zebrane dane wyłącznie w celu udzielenia odpowiedzi na zapytania i w zakresie innych punktów niniejszego oświadczenia o ochronie danych.',
            'startpage' => 'Na stronie startowej naszej usługi MetaGer używamy agenta użytkownika, który przekazałeś, aby wyświetlić odpowiednie instrukcje instalacji wtyczki dla Twojej przeglądarki.',
        ],
        'title' => 'Dane przychodzące według kontekstu',
        'metager' => [
            'title' => 'Korzystanie z wyszukiwarki internetowej MetaGer',
            'description' => 'Podczas korzystania z naszej wyszukiwarki internetowej MetaGer za pośrednictwem formularza internetowego lub interfejsu OpenSearch generowane są następujące dane:',
            'query' => 'Jako integralna część metasearch, zapytanie wyszukiwania jest przesyłane do naszych partnerów w celu uzyskania wyników wyszukiwania do wyświetlenia na stronie wyników. Otrzymane wyniki, w tym wyszukiwane hasło, są wyświetlane przez kilka godzin.',
            'preferences' => 'Używamy tych danych (np. ustawień językowych), aby odpowiedzieć na odpowiednie zapytanie wyszukiwania. Niektóre z tych danych przechowujemy w celach statystycznych.',
            'suggest' => 'Jeśli sugestie wyszukiwania są włączone na pasku adresu przeglądarki (opcjonalnie), musimy przechowywać ustawienia sugestii na naszym serwerze. Kluczem będzie połączony ciąg adresu IP, useragent i nagłówek accept-language przeglądarki, który jest hashowany sha1. Jeśli ustawienie jest wyłączone (domyślnie), nic nie zostanie zapisane.',
        ],
        'contact' => [
            'title' => 'Korzystanie z formularza kontaktowego',
            'description' => 'Podczas korzystania z formularza kontaktowego MetaGer generowane są następujące dane, które przechowujemy w celach referencyjnych do 2 miesięcy po zakończeniu zapytania:',
            'contact' => 'Dane będą przechowywane do celów referencyjnych przez okres do 2 miesięcy po zakończeniu przetwarzania wniosku.',
        ],
        'mapsapp' => [
            'title' => 'Korzystanie z aplikacji MetaGer',
            'description' => 'Korzystanie z aplikacji MetaGer jest takie samo jak korzystanie z MetaGer za pośrednictwem przeglądarki internetowej.',
        ],
        'plugin' => [
            'title' => 'Korzystanie z wtyczki MetaGer',
            'description' => 'Podczas korzystania z wtyczki MetaGer generowane są następujące dane:',
        ],
        'membership' => [
            'title' => 'Składanie wniosku o członkostwo w SUMA-EV i zarządzanie nim',
            'description' => 'Podczas składania wniosku o członkostwo w SUMA-EV oraz w trakcie trwania członkostwa generowane są następujące dane:',
            'contact' => 'W przypadku osób fizycznych: imię i nazwisko oraz adres e-mail; w przypadku organizacji: nazwa organizacji, jej przybliżona wielkość oraz imię i nazwisko oraz adres e-mail osoby kontaktowej. Adres pocztowy jest opcjonalny, a jeśli zostanie podany, służy przede wszystkim do wystawienia pokwitowania darowizny. W żadnym wypadku nie przekazujemy tych danych stronom trzecim.',
            'payment' => 'Twoje dane płatnicze wykorzystujemy wyłącznie do cyklicznego pobierania opłaty członkowskiej. Sposób ich przetwarzania, odbiorcy tych danych oraz okres ich przechowywania opisano w sekcji „Przetwarzanie płatności”.',
            'reduction' => 'Jeśli złożysz wniosek o obniżoną składkę członkowską, przetwarzamy przesłane przez Ciebie dokumenty potwierdzające (np. potwierdzenie przelewu) wyłącznie w celu zweryfikowania Twojego uprawnienia do zniżki. Dokumenty te mogą zawierać dane szczególnie wrażliwe w rozumieniu art. 9 RODO; w związku z tym przechowujemy je tylko tak długo, jak jest to konieczne do przeprowadzenia tej weryfikacji, i nie przekazujemy ich osobom trzecim.',
            'crm' => 'Do zarządzania członkostwami korzystamy z naszego własnego systemu administracji członkostwami, obsługiwanym również przez SUMA-EV. Wymienione powyżej dane są tam przechowywane przez cały okres trwania członkostwa, a następnie przez okresy przechowywania określone przepisami prawa. Twoje członkostwo jest również powiązane z kluczem MetaGer, za pomocą którego Twoja składka członkowska jest zaliczana jako kredyt użytkowy w serwisie MetaGer (zobacz „Klucz MetaGer przy kasie”).',
            'cookies' => 'Formularz zgłoszeniowy i portal dla członków nie zapisują plików cookie w momencie ich otwarcia. Dopiero po przesłaniu formularza lub zalogowaniu się do portalu dla członków zapisujemy technicznie niezbędny plik cookie sesji, abyśmy mogli na przykład wskazać błędy w wprowadzonych danych lub utrzymać stan zalogowania. Plik ten traci ważność po dwóch godzinach bezczynności i jest usuwany po wylogowaniu. Jeśli otworzysz formularz poprzez link wskazujący język inny niż ustawiony w przeglądarce, zapamiętujemy ten język w pliku cookie, który jest usuwany po zamknięciu przeglądarki. Podstawą prawną jest § 25 ust. 2 pkt 2 TDDDG.',
        ],
        'payments' => [
            'title' => 'Przetwarzanie płatności',
            'description' => 'Płatności na rzecz SUMA-EV – obecnie składki członkowskie, a w przyszłości również darowizny i klucze MetaGer – są przetwarzane przez nasz własny system płatności, również obsługiwany przez SUMA-EV. Generowane są następujące dane:',
            'ip' => 'Wyłącznie w przypadku płatności poleceniem zapłaty SEPA: w ramach dokumentacji upoważnienia do polecenia zapłaty (patrz poniżej). W przeciwnym razie dane te nie są przechowywane.',
            'useragent' => 'Wyłącznie w przypadku płatności poleceniem zapłaty SEPA: w ramach dokumentacji upoważnienia do polecenia zapłaty (patrz poniżej). W przeciwnym razie dane te nie są przechowywane.',
            'contact' => 'Otrzymujemy Twoje imię i nazwisko oraz adres e-mail od dostawcy usługi, za którą płacisz (np. w ramach wniosku o członkostwo). Wykorzystujemy te dane do wysyłania wiadomości e-mail dotyczących Twojej płatności, takich jak dane do przelewu bankowego, linki do płatności lub przypomnienia o płatnościach.',
            'payment' => 'W zależności od metody płatności, posiadacza rachunku i numeru IBAN – odniesienie do konta PayPal lub karty u danego dostawcy, a także kwota, czas i status każdej płatności.',
            'methods' => 'Pobrania SEPA i przelewy bankowe realizujemy we własnym zakresie; zlecenia pobrania są przekazywane do naszego banku wraz z danymi posiadacza rachunku, numerem IBAN, kwotą oraz numerem referencyjnym upoważnienia. W przypadku płatności za pomocą PayPal dane użytkownika są wprowadzane bezpośrednio w systemie PayPal (Europe) S.à r.l. et Cie, S.C.A.; dopiero po wybraniu PayPal jako metody płatności strona płatności ładuje kod programu z serwisu PayPal, co przekazuje PayPal adres IP użytkownika i umożliwia mu ustawienie własnych plików cookie. W przypadku płatności kartą lub za pośrednictwem serwisu Wero dane użytkownika są wprowadzane bezpośrednio w systemie VR Payment GmbH. Nigdy nie otrzymujemy numerów kart ani danych logowania do serwisów PayPal lub Wero, a jedynie numer referencyjny, który pozwala nam pobierać opłaty cykliczne. Podstawą prawną jest art. 6 ust. 1 lit. b) RODO.',
            'mandate' => 'Jeśli udzielisz nam upoważnienia do polecenia zapłaty SEPA przez Internet, przechowujemy dane dotyczące treści upoważnienia (posiadacz rachunku, numer IBAN, numer referencyjny upoważnienia, kwota i częstotliwość, treść upoważnienia wyświetlona użytkownikowi) wraz z datą i godziną jego udzielenia, Twoim adresem IP oraz informacją o przeglądarce. Potrzebujemy tej dokumentacji, aby udowodnić naszemu bankowi, że udzieliłeś upoważnienia, w przypadku zakwestionowania polecenia zapłaty; tylko w takim przypadku udostępniamy ją bankowi. Podstawą prawną jest art. 6 ust. 1 lit. b) RODO oraz nasz uzasadniony interes polegający na możliwości udowodnienia istnienia upoważnienia (art. 6 ust. 1 lit. f) RODO).',
            'cookies' => 'Same strony płatności nie zapisują żadnych plików cookie po ich otwarciu. Tylko w przypadku, gdy wprowadzone dane są niekompletne lub nieprawidłowe, zapisujemy na krótki czas niezbędny z technicznego punktu widzenia plik cookie sesji, aby wyświetlić użytkownikowi komunikaty o błędach; plik ten jest usuwany natychmiast po wyświetleniu komunikatów (art. 25 ust. 2 pkt 2 TDDDG).',
            'retention' => 'Jeśli płatność nie zostanie zrealizowana z powodu jej rezygnacji, usuwamy imię i nazwisko oraz adres e-mail użytkownika tydzień po upływie terminu realizacji płatności. Jeśli na przykład wniosek o członkostwo zostanie odrzucony przed pobraniem jakichkolwiek opłat, natychmiast usuwamy dane dotyczące płatności oraz zapis upoważnienia. Wszystkie pozostałe dane dotyczące płatności oraz zapisy dotyczące upoważnień są przechowywane przez cały okres trwania stosunku płatniczego, a następnie przez okres przewidziany w przepisach dotyczących przechowywania danych (do 10 lat).',
        ],
    ],
    'title' => 'polityka prywatności',
    'introduction' => 'W celu zapewnienia maksymalnej przejrzystości podajemy, jakie dane zbieramy od użytkowników i w jaki sposób je wykorzystujemy. Ochrona danych użytkownika jest dla nas ważna i powinna być również ważna dla użytkownika. <strong>Prosimy o uważne przeczytanie niniejszego oświadczenia; leży to w Państwa interesie.</strong>',
    'responsible_party' => [
        'title' => 'Osoby odpowiedzialne i osoby kontaktowe',
        'description' => 'MetaGer i powiązane usługi są obsługiwane przez <a href="https://suma-ev.de">SUMA-EV</a>, która jest również autorem niniejszego oświadczenia. W tym oświadczeniu "my" oznacza ogólnie SUMA-EV. Nasze dane kontaktowe można znaleźć na stronie <a href=":link_impress">Imprint</a>. Można się z nami skontaktować za pośrednictwem poczty elektronicznej, korzystając z formularza kontaktowego <a href=":link_contact"></a> .',
    ],
    'principles' => [
        'title' => 'Zasady',
        'description' => 'Jako stowarzyszenie non-profit jesteśmy zaangażowani w wolny dostęp do wiedzy. Ponieważ wiemy, że swobodne badania nie są kompatybilne z masową inwigilacją, bardzo poważnie traktujemy również ochronę danych. Zawsze przetwarzaliśmy tylko te dane, które są absolutnie niezbędne do działania naszych usług. Ochrona danych jest zawsze naszym standardem. Nie prowadzimy profilowania - tj. automatycznego tworzenia profili użytkowników.',
    ],
    'hosting' => [
        'title' => 'Hosting',
        'description' => 'Nasze usługi są administrowane przez nas, SUMA-EV, i obsługiwane na sprzęcie wynajmowanym od Hetzner Online GmbH.',
    ],
    'description' => [
        'title' => 'Opis danych wynikowych',
        'ip' => [
            'title' => 'Adres protokołu internetowego',
            'description' => 'Adres protokołu internetowego (zwany dalej IP) jest obowiązkowy w celu korzystania z usług internetowych, takich jak MetaGer. Ten adres IP, w połączeniu z datą - podobnie jak numer telefonu - wyraźnie identyfikuje dostęp do Internetu i jego właściciela. Ogólnie rzecz biorąc, pierwsze trzy (z czterech) bloki adresu IP nie są danymi osobowymi. Jeśli tylne bloki adresu IP zostaną skrócone, skrócony adres identyfikuje przybliżony obszar geograficzny wokół połączenia internetowego.',
            'example_full' => 'Przykłady (pełny adres IP)',
            'example_partial' => 'Przykłady (tylko dwa pierwsze bloki)',
        ],
        'useragent' => [
            'title' => 'Identyfikator agenta użytkownika',
            'description' => 'Po wywołaniu strony internetowej przeglądarka automatycznie wysyła identyfikator, zwykle zawierający dane o używanej przeglądarce i systemie operacyjnym. Ten identyfikator przeglądarki (tzw. agent użytkownika) może być wykorzystywany przez strony internetowe, na przykład do rozpoznawania urządzeń mobilnych i prezentowania im spersonalizowanych wyników.',
            'example' => 'Przykład',
        ],
        'payment' => [
            'title' => 'Szczegóły płatności',
            'description' => 'Przy zakupie klucza MetaGer wymagane są różne dane płatności w zależności od dostawcy płatności',
            'examples' => 'Przykłady',
            'name' => 'Max Mustermann, mail@example.com',
            'card' => 'Ostatnie cyfry numeru karty kredytowej',
        ],
        'query' => [
            'title' => 'Wprowadzone zapytanie wyszukiwania',
            'description' => 'Wprowadzone wyszukiwane hasła są absolutnie niezbędne do wyszukiwania w sieci. Zasadniczo nie można z nich uzyskać żadnych danych osobowych; między innymi dlatego, że nie mają one ustalonej struktury.',
            'examples' => 'Przykłady',
            'example_1' => 'zużycie wody prysznic',
            'example_2' => 'Tekst piosenki Na drzewie kukułka',
        ],
        'preferences' => [
            'title' => 'Preferencje użytkownika',
            'description' => 'Oprócz danych formularzy i agentów użytkownika przeglądarka często przesyła inne dane. Obejmuje to wybór języka, ustawienia wyszukiwania, akceptację nagłówków, nagłówki "nie śledź" i inne.',
        ],
        'contact' => [
            'title' => 'Dane kontaktowe',
            'description' => 'Poniżej znajduje się imię i nazwisko użytkownika (imię i nazwisko) oraz adres e-mail użytkownika. Dane te są przez nas przetwarzane wyłącznie w celu udzielenia odpowiedzi i nie są przekazywane dalej.',
        ],
        'message' => [
            'title' => 'Wiadomość',
            'description' => 'Wiadomość wprowadzona tutaj zostanie przesłana do nas i wykorzystana do przetworzenia żądania.',
        ],
        'error' => [
            'title' => 'Raport o błędzie (ślad stosu)',
            'description' => 'Gdy w naszej aplikacji wystąpi błąd techniczny, automatycznie generowany jest opis błędu wraz ze śladem stosu. Dzięki temu wiemy, w którym miejscu kodu źródłowego wystąpił błąd, co pozwala nam go naprawić.',
        ],
        'reduction' => [
            'title' => 'Dowód uprawniający do obniżonej składki członkowskiej',
            'description' => 'Dokument, który należy przesłać w celu udowodnienia uprawnień do obniżonej opłaty członkowskiej, na przykład potwierdzenie otrzymania środków z tytułu transferu.',
        ],
    ],
    'base' => [
        'title' => 'Podstawa prawna przetwarzania',
        'description' => 'Podstawą prawną przetwarzania danych osobowych użytkownika jest art. 6 ust. 1 lit. a) RODO, jeśli użytkownik wyraził zgodę na przetwarzanie danych poprzez korzystanie z naszych usług. 6 ust. 1 lit. a) RODO, jeśli użytkownik wyraził zgodę na przetwarzanie danych poprzez korzystanie z naszych usług, lub art. 6 ust. 1 lit. f) RODO, jeśli przetwarzanie danych jest niezbędne do ochrony naszych prawnie uzasadnionych interesów. 6 (1) (f) RODO, jeśli przetwarzanie jest niezbędne do ochrony naszych uzasadnionych interesów, lub inna podstawa prawna, jeśli powiadomimy o tym użytkownika oddzielnie.',
    ],
    'rights' => [
        'title' => 'Prawa użytkownika (i nasze obowiązki)',
        'description' => 'Aby użytkownik mógł również chronić swoje dane osobowe, wyjaśniamy (zgodnie z art. 13 DSGVO), że przysługują mu następujące prawa:',
        'information' => [
            'title' => 'Prawo do udzielania informacji',
            'description' => 'Użytkownik ma prawo (art. 15 RODO) do zażądania od nas w dowolnym momencie informacji o tym, czy, a jeśli tak, to jakie jego dane (metager.de i SUMA-EV) posiadamy na jego temat. Wyślemy Ci tak szybko, jak to możliwe, tj. w ciągu kilku dni, pełną kopię danych, które przechowujemy lub w inny sposób przechowujemy na Twój temat zgodnie z art. 15 ust. 3 podsekcja 1 RODO. Preferujemy w tym celu metodę elektroniczną zgodnie z art. 15 ust. 3 akapit 3 RODO; w tym celu zapiszemy Twój adres e-mail na czas przetwarzania. Prosimy o poinformowanie nas, jeśli użytkownik chce otrzymać informacje w formie papierowej.',
        ],
        'correction' => [
            'title' => 'Prawo do korekty i uzupełnienia',
            'description' => 'Zgodnie z art. 16 RODO. Jeśli przechowujemy nieprawidłowe dane o użytkowniku, może on zażądać ich poprawienia. Dotyczy to również brakujących elementów, w tym przypadku użytkownik ma prawo do ich uzupełnienia.',
        ],
        'deletion' => [
            'title' => 'Prawo do usunięcia danych',
            'description' => 'Zgodnie z art. 17 RODO',
        ],
        'processing' => [
            'title' => 'Prawo do ograniczenia przetwarzania',
            'description' => 'Zgodnie z art. 18 RODO; Na przykład, jeśli użytkownik poprosił nas o usunięcie lub zmianę danych na jego temat, może nałożyć na nas zakaz przetwarzania danych na czas potrzebny nam na wykonanie tych czynności. Jest to możliwe niezależnie od tego, czy ostatecznie zmienimy, usuniemy itp. dane, o których mowa.',
        ],
        'complaint' => [
            'title' => 'Prawo do złożenia skargi',
            'description' => 'Zgodnie z art. 13 ust. 2 lit. d) RODO możesz złożyć skargę na nas do inspektora ochrony danych kraju związkowego Dolna Saksonia. Online: <a href="https://www.lfd.niedersachsen.de/startseite/">Inspektor ochrony danych</a>',
        ],
        'opposition' => [
            'title' => 'Prawo do sprzeciwu wobec przetwarzania',
            'description' => 'Zgodnie z art. 21 RODO; na przykład, jeśli jesteś na liście i chcesz tam być, nadal możesz zabronić przetwarzania lub dalszego przetwarzania tych danych.',
        ],
        'portability' => [
            'title' => 'Prawo do przenoszenia danych',
            'description' => 'Zgodnie z art. 20 RODO oznacza to, że jesteśmy zobowiązani do dostarczenia żądanych danych w sposób czytelny, możliwy do odczytu maszynowego lub w sposób zwyczajowo przyjęty, tak aby użytkownik mógł udostępnić te dane innej osobie w takiej formie, w jakiej są (do przekazania).',
        ],
        'obligation_notify' => [
            'title' => 'Obowiązek powiadomienia w związku z korektą lub usunięciem danych osobowych lub ograniczeniem przetwarzania:',
            'description' => 'Zgodnie z art. 19 RODO; gdybyśmy udostępnili dane, które nam powierzyłeś, stronom trzecim (czego nigdy nie robimy), bylibyśmy zobowiązani do poinformowania ich, że na Twoje żądanie usuniemy, zmienimy itp.',
        ],
        'perception' => 'Aby skorzystać z tych praw, wystarczy skontaktować się z nami za pomocą naszego <a href=":contact_link">formularza kontaktowego</a></b>. Jeśli wolisz formę listowną, wyślij nam wiadomość na adres naszego biura:',
    ],
    'changes' => [
        'title' => 'Zmiany w niniejszym Oświadczeniu',
        'description' => 'Podobnie jak nasze oferty, niniejsze oświadczenie o ochronie danych również podlega ciągłym zmianom. Dlatego też należy je regularnie czytać.',
        'date' => 'Ta wersja naszej polityki prywatności jest datowana: :date',
    ],
    'data' => [
        'ip' => 'Adres IP',
        'useragent' => 'User-Agent',
        'query' => 'Zapytanie wyszukiwania',
        'preferences' => 'Preferencje użytkownika',
        'contact' => 'Dane kontaktowe',
        'message' => 'Wiadomość',
        'payment' => 'Dane płatności',
        'referrer' => 'odsyłacz wysłany przez użytkownika',
        'gps' => 'Dane lokalizacji',
        'optional' => 'opcjonalny',
        'unused' => 'Nie będą zapisywane ani udostępniane.',
        'error' => 'Raport o błędach',
        'reduction' => 'Dowód redukcji',
    ],
    'monitoring' => [
        'collected' => [
            'useragent' => 'Typ przeglądarki, system operacyjny oraz przybliżony typ urządzenia, określone na podstawie informacji zawartych w agencie użytkownika.',
            'url' => 'Adres (URL) strony lub funkcji, w której wystąpił błąd.',
            'title' => 'Jakie dane są generowane',
            'description' => 'W przypadku wystąpienia błędu w naszej aplikacji internetowej lub na naszych serwerach automatycznie gromadzimy następujące dane:',
            'error' => 'Opis błędu oraz ślad stosu wskazujący miejsce w kodzie, w którym wystąpił błąd.',
        ],
        'not_collected' => [
            'title' => 'Jakie dane nie są generowane',
            'description' => 'Twój adres IP jest usuwany, zanim zostanie przesłany do serwisu GlitchTip, i nigdy nie trafia do systemu. Nie łączymy zgłoszeń błędów z żadną osobą ani kontem użytkownika – nawet w przypadku zalogowanych użytkowników. Nie gromadzimy również haseł, danych dotyczących płatności ani treści formularzy lub wiadomości przesyłanych za pośrednictwem naszych usług.',
        ],
        'retention' => 'Raporty o błędach przechowujemy przez 30 dni; następnie są one automatycznie usuwane.',
        'base' => 'Podstawą prawną tego przetwarzania danych jest nasz uzasadniony interes polegający na niezawodnym i bezpiecznym świadczeniu naszych usług (art. 6 ust. 1 lit. f) RODO).',
        'title' => 'Śledzenie błędów i monitorowanie aplikacji',
        'description' => 'Aby zapewnić niezawodność naszych usług, korzystamy z narzędzia do śledzenia błędów typu open source o nazwie GlitchTip, zarówno w części backendowej, jak i frontendowej naszych aplikacji. GlitchTip działa wyłącznie na naszej własnej infrastrukturze; żadne dane nie są udostępniane ani przekazywane żadnym zewnętrznym dostawcom usług analitycznych lub monitorujących.',
    ],
];
