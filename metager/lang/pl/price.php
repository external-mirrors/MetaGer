<?php

/**
 * Was ein MetaGer-Schlüssel kostet — /preise.
 *
 * Aus pass/lang/<locale>/cost.json des Keymanagers übernommen, wo diese Seite
 * bis zum Umzug lag. Die Preiszahlen selbst stehen bewusst nicht hier: sie
 * kommen über App\Landing\KeyPrice vom Keymanager, weil der Checkout sie
 * ausgibt.
 */

return [
    "headings" => [
        "Oto ile kosztuje klucz MetaGer",
        "Najważniejsze podsumowanie",
    ],
    "texts" => [
        "Za każde wyszukiwanie bez reklam w MetaGer z domyślnymi ustawieniami zostanie naliczona opłata <b>1 token</b>. W każdej chwili możesz doładować swój klucz jednym z tych pakietów tokenów.",
    ],
    "short-info" => [
        [
            "heading" => "Tokeny zachowują ważność przez 2 lata",
            "text" => "Zakupione tokeny zachowują ważność do momentu ich wykorzystania. Nie ma stałego zlecenia.",
        ],
        [
            "heading" => "30-dniowa gwarancja zwrotu pieniędzy",
            "text" => "W przypadku niezadowolenia z klucza, użytkownik ma 30 dni od daty zakupu na zwrot niewykorzystanego kredytu.",
        ],
        [
            "heading" => "Klucz jest automatycznie konfigurowany i używany w przeglądarce",
            "text" => "Nie musisz robić nic więcej, aby korzystać z klucza MetaGer w wyszukiwarce. Po naładowaniu zostanie on automatycznie skonfigurowany w przeglądarce, a użytkownik otrzyma informacje o tym, jak łatwo skonfigurować go na dodatkowych urządzeniach.",
        ],
        [
            "heading" => "Brak śledzenia",
            "text" => "Skorzystaj z naszej aplikacji <a href=\":linkapp\">na Androida</a> lub naszego rozszerzenia do przeglądarki i zachowaj anonimowość dzięki <a href=\":linktokens\">anonimowym tokenom</a>.",
        ],
    ],
    "pricing" => [
        "heading" => "Oto jak kształtują się nasze ceny",
        "texts" => [
            "Większość naszych przychodów trafia bezpośrednio do wyszukiwarek. Chcemy oferować zrównoważoną koncepcję, co oznacza, że wyszukiwarki nie ponoszą żadnych strat finansowych, dostarczając anonimowe i wolne od reklam wyniki wyszukiwania dla MetaGer. Ponadto, istnieje udział w pokryciu naszych kosztów osobowych i serwerowych, i oczywiście opłaty dla dostawców usług płatniczych i podatki są wliczone w ceny.",
            "W ten sposób, wybierając usługi wyszukiwania, które mają być przeszukiwane, możesz nie tylko ustawić własne koszty, ale także zdecydować, które projekty chcesz wspierać. Stąd też rozliczenia oparte na tokenach.",
        ],
    ],
    "payment-methods" => [
        "heading" => "Metody płatności",
        "texts" => [
            "Klucze MetaGer zostały zaprojektowane przez nas w taki sposób, aby nie wymagały żadnych danych osobowych. Niemniej jednak, najpóźniej podczas realizacji płatności, niektóre dane są zwykle wymagane. Może to być IBAN konta płatniczego lub adres e-mail używanego konta PayPal. SUMA-EV sama nie przetwarza tych danych i nie przechowuje ich. Jednak w zależności od metody płatności robi to dostawca usług płatniczych.",
            "Dlatego nasze metody płatności są skonfigurowane w taki sposób, aby gromadzić jak najmniej danych użytkownika, a w niektórych przypadkach nawet wcale.",
        ],
        "anonymous" => "Anonimowe metody płatności",
        "more" => "Inne metody płatności",
    ],
    /**
     * Die Namen der Zahlungsarten. Standen im "checkout"-Namensraum des
     * Keymanagers, der dort bleibt — hierher kopiert, weil diese Seite die
     * einzige war, die sie außerhalb des Bezahlvorgangs gebraucht hat.
     */
    "methods" => [
        "cash" => "Gotówka",
        "prepay" => "Przelew bankowy",
        "card" => "Karta kredytowa / debetowa",
    ],
];
