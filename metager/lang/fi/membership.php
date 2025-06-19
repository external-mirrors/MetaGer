<?php
return [
    'title' => 'Jäsenyytesi SUMA-EV:ssä',
    'non-de' => 'Valitettavasti voimme tällä hetkellä ottaa vastaan vain saksankielisten maiden hakemuksia. Olet erittäin tervetullut tukemaan meitä lahjoituksella <a href=":donationlink"></a> .',
    'back' => 'Takaisin aloitussivulle',
    'success' => 'Kiitos paljon jäsenhakemuksen jättämisestä. Käsittelemme sen mahdollisimman nopeasti. Tämän jälkeen saat meiltä sähköpostitse lisätietoja annettuun osoitteeseen.',
    'application' => [
        'cancel' => [
            'application' => 'Jäsenhakemuksen poistaminen',
            'update' => 'Hylkää muutokset',
        ],
        'update_hint' => 'Jäsenyytesi muutospyynnöt tarkistetaan/hyväksytään pian. Jos olet tyytyväinen esitettyyn tilaan, voit poistua tältä sivulta. Muussa tapauksessa voit tehdä lisää muutoksia tai poistaa muutospyyntösi alla olevasta painikkeesta.',
        'description' => 'Kiitos, että harkitset <a href="https://suma-ev.de/en/mitglieder/" target="_blank"></a> jäsenyyttä voittoa tavoittelemattomassa yhdistyksessämme. Hakemuksenne käsittelyä varten tarvitsemme vain muutamia tietoja, jotka voitte täyttää tässä.',
        'payment_block' => 'Yritämme hyväksyä seuraavan jäsenmaksusi maksun validoidaksemme maksutapasi, mutta maksu suoritetaan vain, jos se erääntyy seuraavan kahden viikon kuluessa, ja muussa tapauksessa maksu mitätöidään.',
    ],
    'data' => [
        'description' => 'Olemme tallentaneet seuraavat tiedot hakemustasi varten:',
        'company' => "Yrityksen nimi",
        'amount' => "Jäsenmaksu",
        'payment_method' => "Maksutapa",
        'payment_methods' => [
            'directdebit' => "Suoraveloitus",
            'card' => "Luottokortti",
        ],
        'payment' => [
            'interval' => [
                'six-monthly' => "Puolivuosittain",
                'annual' => "vuosittain",
                'monthly' => "kuukausittain",
                'quarterly' => "neljännesvuosittain",
            ],
        ],
        'name' => 'Nimi',
        'email' => 'Sähköpostiosoite',
    ],
    'key' => [
        'description' => 'MetaGerin käyttämiseen käytetään seuraavaa avainta, jota me täydennämme. Jos olit jo kirjautunut sisään, käytettiin olemassa olevaa avaintasi.',
        'later' => 'Ensimmäinen lisäys tehdään sen jälkeen, kun hakemuksesi on käsitelty.',
        'now' => 'Se on jo ladattu ja sitä voidaan käyttää välittömästi.',
    ],
];
