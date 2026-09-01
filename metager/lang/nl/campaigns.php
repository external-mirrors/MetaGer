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
];
