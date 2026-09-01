<?php

return [
    'heading' => 'Gavekortkampagner',
    'description' => 'Del nøgler ud fra din egen token-saldo, for eksempel til venner eller kolleger. Uddelte nøgler trækker først deres tokens fra din nøgle, når de rent faktisk bruges – ubrugte gaver koster dig ingenting.',
    'unreachable' => 'Dine gavekortkampagner kunne ikke indlæses lige nu. Prøv igen senere.',
    'copy_link' => 'Kopiér link',
    'public_link' => 'Offentligt link',
    'delete_note' => 'Udløbne og deaktiverede kampagner slettes automatisk.',
    'print_cards' => 'Udskriv kort (PDF)',
    'disable' => 'Deaktivér',
    'delete' => 'Slet nu',

    'status' => [
        'active' => 'aktiv',
        'disabled' => 'deaktiveret',
        'expired' => 'udløbet',
    ],

    'facts' => [
        'tokens_per_key' => ':tokens tokens pr. nøgle',
        'redeemed' => ':redeemed af :total indløst',
        'budget' => ':left af :total tokens tilbage',
        'expires' => 'slutter :date',
    ],

    'create' => [
        'heading' => 'Opret en kampagne',
        'info' => 'Kampagnen understøttes af denne nøgle: uddelte tokens trækkes fra din saldo, når de bruges. Kampagner løber i 3 måneder, uddelte nøgler er gyldige i 1 måned efter indløsning.',
        'name' => 'Navn (kun synligt for dig)',
        'tokens_per_key' => 'Tokens pr. uddelt nøgle',
        'total_volume' => 'Maksimalt antal tokens i alt',
        'total_volume_hint' => 'Din nøgle indeholder i øjeblikket :charge tokens. Du kan aldrig dele mere ud, end din saldo tillader.',
        'voucher_count' => 'Antal gavekort (valgfrit)',
        'voucher_count_hint' => 'Standard: maksimalt antal i alt divideret med tokens pr. nøgle.',
        'submit' => 'Opret kampagne',
        'error' => [
            'tokens_per_key_too_high' => 'Tokens pr. nøgle må ikke overstige det maksimale antal i alt.',
            'voucher_count_out_of_range' => 'Antallet af gavekort passer ikke til tokens pr. nøgle og det maksimale antal i alt.',
            'over_budget' => 'Det maksimale antal i alt overstiger din tilgængelige saldo.',
            'too_many_active' => 'Du har allerede det maksimale antal aktive kampagner.',
            'invalid' => 'Kampagnen kunne ikke oprettes. Tjek venligst dine oplysninger.',
            'unreachable' => 'Kampagnen kunne ikke oprettes lige nu. Prøv igen senere.',
        ],
    ],
];
