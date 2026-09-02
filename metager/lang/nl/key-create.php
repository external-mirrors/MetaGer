<?php

/**
 * Die Seite zum Erstellen eines Schlüssels — /schluessel-erstellen. Siehe
 * lang/de/key-create.php für die Herkunft der Schlüssel und dafür, was
 * gegenüber dem Keymanager neu ist.
 */
return [
    'heading' => 'Sleutel aanmaken',
    'lede' => 'Uw sleutel is uw account. Hij draagt uw tokensaldo, en hij is alles wat wij van u weten — geen naam, geen e-mailadres, geen wachtwoord. Dat betekent ook: raakt u hem kwijt, dan is het saldo erop weg.',

    'existing' => [
        'text' => 'Had u al een MetaGer-sleutel? Log daarmee in in plaats van een nieuwe aan te maken — een nieuwe sleutel krijgt zijn eigen, gescheiden saldo, en het oude blijft op de oude sleutel staan.',
        'action' => 'Inloggen met een bestaande sleutel',
    ],

    'offer' => [
        'text' => 'Eén druk op de knop en u hebt er een. Geen formulier, geen inloggegevens: MetaGer dobbelt een tekenreeks die nog van niemand is.',
        'button' => 'Nu sleutel maken',
    ],

    'working' => 'Een moment: we dobbelen een nieuwe sleutel voor u …',

    /**
     * The mark that sits in the corner of every page from here on.
     *
     * Derived from the key and stored nowhere
     * ({@see \App\Authentication\KeyIdenticon}). It is here because a mark you
     * are meant to recognise has to be shown the first time — otherwise it is
     * just a coloured square the second time.
     */
    'identity' => 'Hieraan herkent u uw account: dit merkteken staat vanaf nu rechtsboven op elke pagina.',

    'key' => [
        'label' => 'Uw nieuwe sleutel',
        'hint' => '36 tekens. Daarmee logt u op elk ander apparaat in.',
    ],

    'copy' => [
        'action' => 'Sleutel kopiëren',
        'done' => 'Gekopieerd',
    ],

    'save' => [
        'heading' => 'Bewaar hem ergens',
        'text' => 'Zolang deze browser de cookie bewaart, blijft u ingelogd. Raakt hij die kwijt — een nieuw apparaat, gewiste browsergegevens —, dan is deze sleutel de enige weg terug.',

        'qr' => [
            'alt' => 'QR-code die naar uw sleutel leidt',
            'action' => 'Als afbeelding opslaan',
            'hint' => 'De afbeelding waar het inlogformulier om vraagt. U kunt hem daar later uploaden of met de camera fotograferen.',
        ],

        'url' => [
            'label' => 'Bladwijzer',
            'action' => 'URL kopiëren',
            'hint' => 'Deze URL openen stelt de sleutel opnieuw in, samen met de instellingen van deze browser.',
        ],

        'no_cookies' => 'Deze browser bewaart geen cookies voor MetaGer. Zonder cookie blijft u niet ingelogd — dan is de URL hierboven de manier om vóór een zoekopdracht in te loggen. U kunt hem ook als zoekmachine in uw browser instellen.',
    ],

    'continue' => 'Verder: saldo opwaarderen',
    'continue_hint' => 'Een nieuwe sleutel heeft nog geen saldo. In de volgende stap kiest u een tokenpakket.',

    'errors' => [
        'keyserver_unreachable' => 'Er kon zojuist geen sleutel worden aangemaakt. Dat ligt aan ons en niet aan u — probeer het zo meteen opnieuw.',
        'too_many_attempts' => 'Vanaf deze verbinding zijn zojuist heel veel sleutels aangemaakt. Wacht een paar minuten en laad de pagina dan opnieuw.',
        'no_key' => 'De sleutel is onderweg verloren gegaan — dat gebeurt als de pagina lang open heeft gestaan. Hier is een nieuwe.',
    ],
];
