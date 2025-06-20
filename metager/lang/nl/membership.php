<?php
return [
    'title' => 'Jouw lidmaatschap bij SUMA-EV',
    'non-de' => 'Helaas kunnen we op dit moment alleen toelatingsaanvragen voor Duitstalige landen accepteren. U bent van harte welkom om ons te steunen met een donatie <a href=":donationlink"></a> .',
    'back' => 'Terug naar de startpagina',
    'data' => [
        'company' => "Bedrijfsnaam",
        'amount' => "Lidmaatschap",
        'payment_method' => "Betaalmethode",
        'payment_methods' => [
            'directdebit' => "Automatische incasso",
            'card' => "Creditcard",
            'banktransfer' => "Overschrijving",
            'paypal' => "PayPal",
        ],
        'payment' => [
            'interval' => [
                'six-monthly' => "Halfjaarlijks",
                'annual' => "jaarlijks",
                'monthly' => "maandelijks",
                'quarterly' => "driemaandelijks",
            ],
        ],
        'description' => 'We hebben de volgende gegevens opgenomen voor uw toepassing:',
        'name' => 'Naam',
        'email' => 'E-mailadres',
    ],
    'key' => [
        'description' => 'Om MetaGer te gebruiken wordt de volgende sleutel gebruikt en door ons aangevuld. Als je al was ingelogd, werd je bestaande sleutel gebruikt.',
        'later' => 'De eerste aanvulling vindt plaats nadat je aanvraag is verwerkt',
        'now' => 'Hij is al opgeladen en kan onmiddellijk worden gebruikt.',
    ],
    'success' => 'Hartelijk dank voor het indienen van je lidmaatschapsaanvraag. We zullen deze zo snel mogelijk verwerken. Je ontvangt dan een e-mail met verdere informatie van ons op het opgegeven adres.',
    'application' => [
        'cancel' => [
            'application' => 'Lidmaatschapsaanvraag verwijderen',
            'update' => 'Veranderingen weggooien',
        ],
        'update_hint' => 'De aangevraagde wijzigingen voor je lidmaatschap worden binnenkort bekeken/geaccepteerd. Als je tevreden bent met de getoonde status kun je deze pagina verlaten. Anders kunt u meer wijzigingen aanbrengen of uw wijzigingsverzoek verwijderen met de knop hieronder.',
        'description' => 'Bedankt voor het overwegen van <a href="https://suma-ev.de/en/mitglieder/" target="_blank">lidmaatschap</a> in onze non-profit vereniging. Om je aanvraag te verwerken hebben we slechts een paar gegevens nodig, die je hier kunt invullen.',
        'payment_block' => 'We zullen proberen een betaling te autoriseren voor je volgende lidmaatschapsbijdrage om je betaalmethode te valideren, maar de betaling zal alleen worden uitgevoerd als deze binnen de komende twee weken verschuldigd is en anders worden geannuleerd.',
        'update' => 'Hieronder zie je de informatie die we hebben opgeslagen voor je lidmaatschap. Je kunt deze informatie wijzigen door op "Bewerken" te klikken. Het wijzigen van je contactgegevens is hier niet mogelijk. Als deze zijn gewijzigd, stuur ons dan een e-mail <a href=":contact_link" target="_blank"></a> met je bijgewerkte informatie.',
    ],
];
