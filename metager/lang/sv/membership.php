<?php
return [
    'title' => 'Ditt medlemskap i SUMA-EV',
    'non-de' => 'Tyvärr kan vi för närvarande endast acceptera ansökningar om antagning för tysktalande länder. Du är varmt välkommen att stödja oss med en donation <a href=":donationlink"></a> .',
    'back' => 'Tillbaka till startsidan',
    'application' => [
        'description' => 'Tack för att du överväger <a href="https://suma-ev.de/en/mitglieder/" target="_blank">medlemskap</a> i vår ideella förening. För att kunna behandla din ansökan behöver vi bara några få uppgifter, som du kan fylla i här.',
        'payment_block' => 'Vi kommer att försöka godkänna en betalning för din nästa medlemsavgift för att validera din betalningsmetod, men betalningen kommer endast att genomföras om den förfaller inom de närmaste två veckorna och annulleras annars.',
        'cancel' => [
            'application' => 'Radera ansökan om medlemskap',
            'update' => 'Kassera ändringar',
        ],
        'update_hint' => 'De begärda ändringarna för ditt medlemskap kommer att granskas/accepteras inom kort. Om du är nöjd med det visade tillståndet kan du lämna den här sidan. Annars kan du göra fler ändringar eller ta bort din ändringsbegäran med knappen nedan.',
        'update' => 'Nedan ser du den information som vi har lagrat för ditt medlemskap. Du kan ändra denna information genom att klicka på "Redigera". Det är inte möjligt att ändra dina kontaktuppgifter här. Om dessa har ändrats, vänligen skicka oss ett <a href=":contact_link" target="_blank">e-post</a> med din uppdaterade information.',
    ],
    'data' => [
        'company' => "Företagets namn",
        'amount' => "Medlemsavgift",
        'payment_method' => "Betalningsmetod",
        'payment_methods' => [
            'directdebit' => "Direktdebitering",
            'card' => "Kreditkort",
            'banktransfer' => "Banköverföring",
            'paypal' => "PayPal",
        ],
        'payment' => [
            'interval' => [
                'six-monthly' => "Halvårsvis",
                'annual' => "årligen",
                'monthly' => "månadsvis",
                'quarterly' => "kvartalsvis",
            ],
        ],
        'description' => 'Vi har registrerat följande data för din applikation:',
        'email' => 'E-postadress',
        'name' => 'Namn',
    ],
    'key' => [
        'description' => 'För att använda MetaGer används följande nyckel och fylls på av oss. Om du redan var inloggad användes din befintliga nyckel.',
        'later' => 'Den första påfyllnaden sker efter att din ansökan har behandlats',
        'now' => 'Den är redan laddad och kan användas omedelbart.',
    ],
    'success' => 'Tack så mycket för att du har skickat in din ansökan om medlemskap. Vi kommer att behandla den så snabbt som möjligt. Du kommer sedan att få ett e-postmeddelande med ytterligare information från oss på den adress som du har angett.',
];
