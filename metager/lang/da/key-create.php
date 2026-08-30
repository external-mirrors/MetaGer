<?php

/**
 * Die Seite zum Erstellen eines Schlüssels — /schluessel-erstellen. Siehe
 * lang/de/key-create.php für die Herkunft der Schlüssel und dafür, was
 * gegenüber dem Keymanager neu ist.
 */
return [
    'heading' => 'Opret nøgle',
    'lede' => 'Din nøgle er din konto. Den bærer din tokensaldo, og den er alt, hvad vi ved om dig — intet navn, ingen e-mailadresse, ingen adgangskode. Det betyder også: mister du den, er saldoen på den væk.',

    'existing' => [
        'text' => 'Har du haft en MetaGer-nøgle før? Log ind med den i stedet for at oprette en ny — en ny nøgle får sin egen adskilte saldo, og den gamle bliver på den gamle nøgle.',
        'action' => 'Log ind med en eksisterende nøgle',
    ],

    'offer' => [
        'text' => 'Et tryk på knappen, og du har en. Ingen formular, ingen loginoplysninger: MetaGer slår en tegnfølge, som endnu ikke tilhører nogen.',
        'button' => 'Opret nøgle nu',
    ],

    'working' => 'Et øjeblik: vi slår en ny nøgle til dig …',

    /**
     * The mark that sits in the corner of every page from here on.
     *
     * Derived from the key and stored nowhere
     * ({@see \App\Authentication\KeyIdenticon}). It is here because a mark you
     * are meant to recognise has to be shown the first time — otherwise it is
     * just a coloured square the second time.
     */
    'identity' => 'Sådan genkender du din konto: fra nu af står dette mærke øverst til højre på hver side.',

    'key' => [
        'label' => 'Din nye nøgle',
        'hint' => '36 tegn. Det er dem, du logger ind med på enhver anden enhed.',
    ],

    'copy' => [
        'action' => 'Kopiér nøgle',
        'done' => 'Kopieret',
    ],

    'save' => [
        'heading' => 'Gem den et sted',
        'text' => 'Så længe denne browser beholder cookien, forbliver du logget ind. Mister den den — en ny enhed, slettede browserdata — er denne nøgle den eneste vej tilbage.',

        'qr' => [
            'alt' => 'QR-kode, der fører til din nøgle',
            'action' => 'Gem som billede',
            'hint' => 'Billedet, som loginformularen beder om. Du kan uploade det der senere eller fotografere det med kameraet.',
        ],

        'url' => [
            'label' => 'Bogmærke',
            'action' => 'Kopiér URL',
            'hint' => 'Åbner du denne URL, sættes nøglen op igen sammen med denne browsers indstillinger.',
        ],

        'no_cookies' => 'Denne browser gemmer ingen cookies for MetaGer. Uden cookie forbliver du ikke logget ind — så er URL\'en ovenfor måden at logge ind på før en søgning. Du kan også tilføje den som søgemaskine i din browser.',
    ],

    'continue' => 'Videre: læg saldo på',
    'continue_hint' => 'En ny nøgle har endnu ingen saldo. I næste skridt vælger du en tokenpakke.',

    'errors' => [
        'keyserver_unreachable' => 'Der kunne ikke oprettes en nøgle lige nu. Det er vores skyld og ikke din — prøv igen om et øjeblik.',
        'too_many_attempts' => 'Der er lige oprettet meget mange nøgler fra denne forbindelse. Vent et par minutter, og genindlæs så siden.',
        'no_key' => 'Nøglen gik tabt undervejs — det sker, når siden har stået åben længe. Her er en ny.',
    ],
];
