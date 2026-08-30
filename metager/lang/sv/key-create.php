<?php

/**
 * Die Seite zum Erstellen eines Schlüssels — /schluessel-erstellen. Siehe
 * lang/de/key-create.php für die Herkunft der Schlüssel und dafür, was
 * gegenüber dem Keymanager neu ist.
 */
return [
    'heading' => 'Skapa nyckel',
    'lede' => 'Din nyckel är ditt konto. Den bär ditt polettsaldo, och den är allt vi vet om dig — inget namn, ingen e-postadress, inget lösenord. Det betyder också: tappar du bort den är saldot på den borta.',

    'existing' => [
        'text' => 'Har du haft en MetaGer-nyckel förut? Logga in med den i stället för att skapa en ny — en ny nyckel får ett eget, skilt saldo, och det gamla stannar på den gamla nyckeln.',
        'action' => 'Logga in med en befintlig nyckel',
    ],

    'offer' => [
        'text' => 'Ett tryck på knappen, och du har en. Inget formulär, inga inloggningsuppgifter: MetaGer slumpar fram en teckenföljd som ännu inte tillhör någon.',
        'button' => 'Skapa nyckel nu',
    ],

    'working' => 'Ett ögonblick: vi slumpar fram en ny nyckel åt dig …',

    /**
     * The mark that sits in the corner of every page from here on.
     *
     * Derived from the key and stored nowhere
     * ({@see \App\Authentication\KeyIdenticon}). It is here because a mark you
     * are meant to recognise has to be shown the first time — otherwise it is
     * just a coloured square the second time.
     */
    'identity' => 'Så här känner du igen ditt konto: från och med nu står det här märket längst upp till höger på varje sida.',

    'key' => [
        'label' => 'Din nya nyckel',
        'hint' => '36 tecken. Det är med dem du loggar in på varje ytterligare enhet.',
    ],

    'copy' => [
        'action' => 'Kopiera nyckel',
        'done' => 'Kopierad',
    ],

    'save' => [
        'heading' => 'Förvara den någonstans',
        'text' => 'Så länge den här webbläsaren behåller kakan förblir du inloggad. Förlorar den kakan — en ny enhet, rensade webbläsardata — är den här nyckeln enda vägen tillbaka.',

        'qr' => [
            'alt' => 'QR-kod som leder till din nyckel',
            'action' => 'Spara som bild',
            'hint' => 'Bilden som inloggningsformuläret frågar efter. Du kan ladda upp den där senare eller fotografera den med kameran.',
        ],

        'url' => [
            'label' => 'Bokmärke',
            'action' => 'Kopiera URL',
            'hint' => 'Att öppna den här URL:en ställer in nyckeln igen, tillsammans med den här webbläsarens inställningar.',
        ],

        'no_cookies' => 'Den här webbläsaren sparar inga kakor för MetaGer. Utan kaka förblir du inte inloggad — då är URL:en ovan sättet att logga in före en sökning. Du kan också lägga till den som sökmotor i din webbläsare.',
    ],

    'continue' => 'Vidare: fyll på saldo',
    'continue_hint' => 'En ny nyckel har ännu inget saldo. I nästa steg väljer du ett polettpaket.',

    'errors' => [
        'keyserver_unreachable' => 'Det gick inte att skapa någon nyckel just nu. Det beror på oss och inte på dig — försök igen om en stund.',
        'too_many_attempts' => 'Väldigt många nycklar har just skapats från den här anslutningen. Vänta några minuter och ladda sedan om sidan.',
        'no_key' => 'Nyckeln kom bort på vägen — det händer när sidan har stått öppen länge. Här är en ny.',
    ],
];
