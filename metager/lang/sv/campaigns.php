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
];
