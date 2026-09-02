<?php

return [
    'heading' => 'Voucheracties',
    'description' => 'Geef sleutels weg uit je eigen tokensaldo, bijvoorbeeld aan vrienden of collega\'s. Weggegeven sleutels trekken hun tokens pas van je sleutel af zodra ze daadwerkelijk gebruikt worden – ongebruikte cadeaus kosten je niets.',
    'unreachable' => 'Je voucheracties konden op dit moment niet worden geladen. Probeer het later opnieuw.',
    'copy_link' => 'Link kopiëren',
    'public_link' => 'Openbare link',
    'delete_note' => 'Verlopen en gedeactiveerde acties worden automatisch verwijderd.',
    'print_cards' => 'Kaarten afdrukken (pdf)',
    'disable' => 'Deactiveren',
    'delete' => 'Nu verwijderen',

    'status' => [
        'active' => 'actief',
        'disabled' => 'gedeactiveerd',
        'expired' => 'verlopen',
    ],

    'facts' => [
        'tokens_per_key' => ':tokens tokens per sleutel',
        'redeemed' => ':redeemed van :total ingewisseld',
        'budget' => ':left van :total tokens over',
        'expires' => 'eindigt op :date',
    ],

    'create' => [
        'heading' => 'Actie aanmaken',
        'info' => 'De actie wordt gedekt door deze sleutel: weggegeven tokens worden bij gebruik van je saldo afgetrokken. Acties lopen 3 maanden, weggegeven sleutels zijn na inwisseling 1 maand geldig.',
        'name' => 'Naam (alleen voor jou zichtbaar)',
        'tokens_per_key' => 'Tokens per weggegeven sleutel',
        'total_volume' => 'Maximaal totaal aantal tokens',
        'total_volume_hint' => 'Je sleutel bevat op dit moment :charge tokens. Je kunt nooit meer weggeven dan je saldo.',
        'voucher_count' => 'Aantal vouchers (optioneel)',
        'voucher_count_hint' => 'Standaard: maximaal totaal gedeeld door tokens per sleutel.',
        'submit' => 'Actie aanmaken',
        'error' => [
            'tokens_per_key_too_high' => 'Tokens per sleutel mogen het maximale totaal niet overschrijden.',
            'voucher_count_out_of_range' => 'Het aantal vouchers past niet bij tokens per sleutel en het maximale totaal.',
            'over_budget' => 'Het maximale totaal overschrijdt je beschikbare saldo.',
            'too_many_active' => 'Je hebt al het maximale aantal actieve acties bereikt.',
            'invalid' => 'De actie kon niet worden aangemaakt. Controleer je gegevens.',
            'unreachable' => 'De actie kon op dit moment niet worden aangemaakt. Probeer het later opnieuw.',
        ],
    ],

    /**
     * /c — App\Http\Controllers\VoucherController.
     */
    'redeem' => [
        'enter' => [
            'heading' => 'Voucher inwisselen',
            'description' => 'Heb je een vouchercode ontvangen voor gratis MetaGer-zoekopdrachten? Voer hem hier in om je persoonlijke MetaGer-sleutel te krijgen.',
            'label' => 'Je vouchercode',
            'submit' => 'Code inwisselen',
            'invalid_code' => 'Deze code is ongeldig. Controleer je invoer.',
            'rate_limited' => 'Te veel pogingen. Probeer het later opnieuw.',
        ],
        'teaser' => [
            'heading' => 'Jouw MetaGer-cadeau',
            'tokens' => 'Tokens',
            'description' => 'Deze code geeft je een eigen MetaGer-sleutel met :tokens tokens - zoek reclamevrij en zonder getrackt te worden op het web.',
            'validity' => 'De sleutel is :days dagen geldig na inwisseling.',
            'submit' => 'Sleutel ophalen',
        ],
        'redeemed' => [
            'heading' => 'Hier is je MetaGer-sleutel!',
            'description' => 'Je nieuwe sleutel bevat :tokens tokens.',
            'save' => [
                'heading' => '1. Sla je sleutel op',
                'description' => 'Je sleutel is je inlog - hij wordt alleen hier getoond en kan niet worden hersteld. Sla hem op in je wachtwoordmanager, download de QR-code of print deze pagina.',
            ],
            'copy_key' => 'Sleutel kopiëren',
            'validity' => 'De sleutel is geldig tot :date.',
            'use' => [
                'heading' => '2. Begin met zoeken',
                'description' => 'Open deze link om de sleutel in je browser te activeren. Voeg hem toe aan je favorieten om ingelogd te blijven.',
            ],
            'copy_url' => 'Link kopiëren',
            'start_searching' => 'Nu beginnen met zoeken',
            'to_account' => 'Naar mijn account',
            'qr_alt' => 'QR-code voor de sleutel',
            'no_cookies' => 'Deze browser lijkt geen cookies te bewaren. Bewaar in plaats daarvan de sleutel of de QR-code hierboven.',
        ],
        'error' => [
            'heading' => 'Dit is niet gelukt',
            'invalid_code' => 'Deze code bestaat niet. Controleer je invoer.',
            'invalid_token' => 'Deze link is ongeldig of verlopen.',
            'already_redeemed' => 'Deze code is al ingewisseld.',
            'campaign_inactive' => 'Deze actie is beëindigd. De code kan niet meer worden ingewisseld.',
            'budget_exhausted' => 'Alle cadeaus van deze actie zijn al uitgedeeld.',
            'rate_limited' => 'Te veel pogingen. Probeer het later opnieuw.',
            'unreachable' => 'De voucher kon op dit moment niet worden ingewisseld. Probeer het later opnieuw.',
            'unknown' => 'Er is een onverwachte fout opgetreden. Probeer het later opnieuw.',
            'retry' => 'Voer een code in',
        ],
    ],
];
