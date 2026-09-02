<?php

return [
    'heading' => 'Presentkortskampanjer',
    'description' => 'Dela ut nycklar från ditt eget token-saldo, till exempel till vänner eller kollegor. Utdelade nycklar drar av sina tokens från din nyckel först när de faktiskt används – oanvända gåvor kostar dig ingenting.',
    'unreachable' => 'Dina presentkortskampanjer kunde inte laddas just nu. Försök igen senare.',
    'copy_link' => 'Kopiera länk',
    'public_link' => 'Offentlig länk',
    'delete_note' => 'Utgångna och inaktiverade kampanjer tas bort automatiskt.',
    'print_cards' => 'Skriv ut kort (PDF)',
    'disable' => 'Inaktivera',
    'delete' => 'Ta bort nu',

    'status' => [
        'active' => 'aktiv',
        'disabled' => 'inaktiverad',
        'expired' => 'utgången',
    ],

    'facts' => [
        'tokens_per_key' => ':tokens tokens per nyckel',
        'redeemed' => ':redeemed av :total inlösta',
        'budget' => ':left av :total tokens kvar',
        'expires' => 'slutar :date',
    ],

    'create' => [
        'heading' => 'Skapa en kampanj',
        'info' => 'Kampanjen backas upp av den här nyckeln: utdelade tokens dras av från ditt saldo när de används. Kampanjer pågår i 3 månader, utdelade nycklar är giltiga i 1 månad efter inlösen.',
        'name' => 'Namn (synligt endast för dig)',
        'tokens_per_key' => 'Tokens per utdelad nyckel',
        'total_volume' => 'Maximalt antal tokens totalt',
        'total_volume_hint' => 'Din nyckel innehåller för närvarande :charge tokens. Du kan aldrig dela ut mer än ditt saldo.',
        'voucher_count' => 'Antal presentkort (valfritt)',
        'voucher_count_hint' => 'Standard: maximalt totalt delat med tokens per nyckel.',
        'submit' => 'Skapa kampanj',
        'error' => [
            'tokens_per_key_too_high' => 'Tokens per nyckel får inte överstiga det maximala totala antalet.',
            'voucher_count_out_of_range' => 'Antalet presentkort matchar inte tokens per nyckel och det maximala totala antalet.',
            'over_budget' => 'Det maximala totala antalet överstiger ditt tillgängliga saldo.',
            'too_many_active' => 'Du har redan det maximala antalet aktiva kampanjer.',
            'invalid' => 'Kampanjen kunde inte skapas. Kontrollera dina uppgifter.',
            'unreachable' => 'Kampanjen kunde inte skapas just nu. Försök igen senare.',
        ],
    ],

    /**
     * /c — App\Http\Controllers\VoucherController.
     */
    'redeem' => [
        'enter' => [
            'heading' => 'Lös in ditt presentkort',
            'description' => 'Har du fått en presentkortskod för gratis MetaGer-sökningar? Ange den här för att få din personliga MetaGer-nyckel.',
            'label' => 'Din presentkortskod',
            'submit' => 'Lös in kod',
            'invalid_code' => 'Den här koden är inte giltig. Kontrollera det du har angett.',
            'rate_limited' => 'För många försök. Försök igen senare.',
        ],
        'teaser' => [
            'heading' => 'Din MetaGer-present',
            'tokens' => 'Tokens',
            'description' => 'Den här koden ger dig en egen MetaGer-nyckel laddad med :tokens tokens - sök på webben utan reklam och utan spårning.',
            'validity' => 'Nyckeln är giltig i :days dagar efter inlösen.',
            'submit' => 'Hämta min nyckel',
        ],
        'redeemed' => [
            'heading' => 'Här är din MetaGer-nyckel!',
            'description' => 'Din nya nyckel är laddad med :tokens tokens.',
            'save' => [
                'heading' => '1. Spara din nyckel',
                'description' => 'Din nyckel är din inloggning - den visas bara här och kan inte återställas. Spara den i din lösenordshanterare, ladda ner QR-koden eller skriv ut den här sidan.',
            ],
            'copy_key' => 'Kopiera nyckel',
            'validity' => 'Nyckeln är giltig till och med :date.',
            'use' => [
                'heading' => '2. Börja söka',
                'description' => 'Öppna den här länken för att aktivera nyckeln i din webbläsare. Bokmärk den för att förbli inloggad.',
            ],
            'copy_url' => 'Kopiera länk',
            'start_searching' => 'Börja söka nu',
            'to_account' => 'Gå till mitt konto',
            'qr_alt' => 'QR-kod för nyckeln',
            'no_cookies' => 'Den här webbläsaren verkar inte spara cookies. Spara i stället nyckeln eller QR-koden ovan.',
        ],
        'error' => [
            'heading' => 'Det här fungerade inte',
            'invalid_code' => 'Den här koden finns inte. Kontrollera det du har angett.',
            'invalid_token' => 'Den här länken är ogiltig eller har gått ut.',
            'already_redeemed' => 'Den här koden har redan lösts in.',
            'campaign_inactive' => 'Den här kampanjen har avslutats. Koden kan inte längre lösas in.',
            'budget_exhausted' => 'Alla presenter från den här kampanjen har redan delats ut.',
            'rate_limited' => 'För många försök. Försök igen senare.',
            'unreachable' => 'Presentkortet kunde inte lösas in just nu. Försök igen senare.',
            'unknown' => 'Ett oväntat fel inträffade. Försök igen senare.',
            'retry' => 'Ange en kod',
        ],
    ],
];
