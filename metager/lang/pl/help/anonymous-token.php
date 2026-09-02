<?php

/**
 * Anonyme Token — /hilfe/anonyme-token.
 *
 * Aus dem "anonymous-token"-Zweig von pass/lang/<locale>/help.json.
 * Der Pfad /keys/help/anonymous-token wird dauerhaft hierher weitergeleitet:
 * er steht in bereits versandten Mitglieds-Willkommensmails.
 */

return [
    "heading" => "Anonimowe tokeny",
    "description" => [
        "heading" => "Czym są anonimowe tokeny?",
        "text" => "Jeśli korzystasz z klucza MetaGer, otrzymasz losowo wygenerowane hasło, które Twoja przeglądarka wysyła do nas przy każdym zapytaniu wyszukiwania, abyśmy mogli włączyć wyszukiwanie bez reklam. Jeśli korzystasz z naszej aplikacji <a href=\"/app\" target=\"_blank\">na Androida</a> lub naszego rozszerzenia internetowego dla <a href=\"https://chrome.google.com/webstore/detail/gjfllojpkdnjaiaokblkmjlebiagbphd\" target=\"_blank\" rel=\"noopener\">Chrome</a> i <a href=\"https://addons.mozilla.org/firefox/addon/metager-suche/\" target=\"_blank\" rel=\"noopener\">Firefox</a>, zamiast hasła przeglądarka wysyła nam losowo wygenerowane hasło (anonimowy token) przy każdym żądaniu wyszukiwania w celu uwierzytelnienia, które jest generowane lokalnie. Gwarantuje to, że każde hasło jest unikalne i nie ma związku z rzeczywistym kluczem MetaGer ani między poszczególnymi hasłami.",
    ],
    "problem" => [
        "heading" => "Jaki problem mają rozwiązać anonimowe tokeny?",
        "text" => "Gdyby przeglądarka zawsze wysyłała nam to samo hasło przy każdym zapytaniu, przynajmniej teoretycznie mielibyśmy możliwość ustalenia korelacji między wszystkimi wyszukiwaniami wykonanymi przy użyciu tego samego klucza. Nawet jeśli tego nie zrobimy, zaufanie nadal będzie konieczne, aby mieć pewność anonimowego wyszukiwania. Abyśmy nie tylko musieli obiecać anonimowe wyszukiwanie, ale także mogli je udowodnić, wprowadziliśmy anonimowe tokeny.",
    ],
    "general-function" => [
        "heading" => "Jak to działa?",
        "texts" => [
            "Chcemy więc, aby jednorazowe hasła były generowane bezpośrednio z urządzenia końcowego, a następnie wysyłane do nas w celu uwierzytelnienia podczas wyszukiwania. Jednak dla każdego anonimowego tokena na urządzeniu końcowym musimy upewnić się, że zwykły token został odjęty od klucza MetaGer dla niego, bez (i to jest sedno) informowania nas, który klucz MetaGer został użyty do wygenerowania anonimowego tokena.",
            "Tradycyjnie użylibyśmy w tym celu jakiejś formy podpisu kryptograficznego. W takim przypadku podpisujemy wygenerowany anonimowy token. Następnie, gdy wyślesz nam anonimowy token wraz z podpisem w późniejszym czasie, możemy być pewni, że anonimowy token jest ważny. Jednak aby uzyskać podpis, musiałbyś wysłać nam anonimowy token wraz z prawdziwym kluczem, co unieważniłoby anonimowość.",
            "Dlatego zamiast tego używamy zmodyfikowanej formy podpisu kryptograficznego, tak zwanego <a href=\"https://pl.wikipedia.org/wiki/Podpis_%C5%9Blepy\" target=\"_blank\">podpisu ślepego</a>. Aby stworzyć rzeczywistą analogię, to tak, jakby wysłać nam swój anonimowy token w kopercie z kalki. W tym przykładzie nie bylibyśmy w stanie otworzyć koperty, ale bylibyśmy w stanie podpisać się z zewnątrz, więc nasz podpis zostałby przeniesiony na anonimowy token znajdujący się w środku. Kiedy otrzymasz kopertę z powrotem, możesz ją usunąć i odesłać nam hasło i podpis później. Moglibyśmy wtedy potwierdzić, że to rzeczywiście nasz podpis.",
            "W rzeczywistości ta analogia jest nieco myląca, ponieważ w rzeczywistym procesie, w momencie wysłania nam anonimowego tokena i podpisu, nie tylko nigdy wcześniej nie widzieliśmy anonimowego tokena, ale także nigdy nie widzieliśmy samego podpisu. A jednak możemy zweryfikować, że podpis został wygenerowany przez nas.",
        ],
    ],
    "meaning" => [
        "texts" => [
            "Korzystając z opisanego algorytmu, zarówno my, jak i użytkownik możemy zapewnić, że nowe losowe hasło niezwiązane z kluczem MetaGer jest używane za każdym razem do uwierzytelnionych wyszukiwań.",
            "Szczególną cechą tego algorytmu jest to, że wszystkie komponenty zapewniające anonimowość są wykonywane lokalnie na urządzeniu użytkownika. Wykonany kod źródłowy może być przeglądany i weryfikowany przez każdego w dowolnym momencie.",
            "Co najlepsze, nie trzeba niczego konfigurować, aby korzystać z anonimowych tokenów. Wystarczy zainstalować/używać naszego rozszerzenia do przeglądarki/aplikacji na Androida, aby urządzenie używało anonimowych tokenów do wszystkich wyszukiwań.",
        ],
        "heading" => "Co to oznacza dla uwierzytelnionych wyszukiwań?",
    ],
    "technical-function" => [
        "heading" => "Algorytm, który za tym stoi:",
        "texts" => [
            "W klasycznym podpisie RSA, wzięlibyśmy anonimowy token <code>m</code>, tajny wykładnik <code>d</code> oraz publiczny moduł <code>N</code> naszego klucza prywatnego i utworzylibyśmy podpis używając <code>m^d (mod N)</code>. Chcemy jednak, aby <code>m</code> pozostało tajne.",
            "Dlatego terminal tworzy liczbę losową <code>r</code> przy użyciu generatora liczb losowych, która jest niezwiązana z dzielnikiem <code>N</code>. Zatem największy wspólny dzielnik <code>r</code> i <code>N</code> musi wynosić <code>1</code>.",
            "Ponieważ <code>r</code> jest liczbą losową, wynika z tego, że <code>m'</code> nie ujawnia żadnych informacji o lokalnie przechowywanym anonimowym tokenie <code>m</code>.",
            "Nasz serwer otrzymuje teraz zaciemniony anonimowy token <code>m'</code> z urządzenia końcowego wraz z kluczem MetaGer, który ma zostać użyty. Odejmujemy token od klucza i wysyłamy również zaciemniony podpis <code>s'&Congruent; (m')^d (mod N)</code> z powrotem do urządzenia końcowego.",
            "Terminal może teraz obliczyć rzeczywisty prawidłowy podpis RSA <code>s</code> dla niezaszyfrowanego anonimowego tokena: <code>s&Congruent; s' r^-1 (mod N)</code>. Działa to, ponieważ dla kluczy RSA, <code>r^(e*d)&Congruent; r (mod N)</code>. A zatem również: <code>s &Congruent; s' * r^-1 &Congruent; (m')^d*r^-1 &Congruent; m^d*r^(e*d)*r^-1 &Congruent; m^d*r*r^-1 &Congruent; m^d (mod N)</code>.",
            "Urządzenie końcowe użytkownika wysyła nam teraz niezaszyfrowany anonimowy token wraz z powiązanym podpisem w celu autoryzacji podczas wyszukiwania. Sam klucz nie jest już wysyłany do nas podczas wyszukiwania.",
        ],
    ],
];
