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

    /**
     * /c — App\Http\Controllers\VoucherController.
     */
    'redeem' => [
        'enter' => [
            'heading' => 'Indløs din gavekode',
            'description' => 'Har du modtaget en gavekode til gratis MetaGer-søgninger? Indtast den her for at få din personlige MetaGer-nøgle.',
            'label' => 'Din gavekode',
            'submit' => 'Indløs kode',
            'invalid_code' => 'Denne kode er ikke gyldig. Tjek venligst din indtastning.',
            'rate_limited' => 'For mange forsøg. Prøv igen senere.',
        ],
        'teaser' => [
            'heading' => 'Din MetaGer-gave',
            'tokens' => 'Tokens',
            'description' => 'Denne kode giver dig din egen MetaGer-nøgle med :tokens tokens - søg reklamefrit og uden sporing på nettet.',
            'validity' => 'Nøglen er gyldig i :days dage efter indløsning.',
            'submit' => 'Hent min nøgle',
        ],
        'redeemed' => [
            'heading' => 'Her er din MetaGer-nøgle!',
            'description' => 'Din nye nøgle er opladet med :tokens tokens.',
            'save' => [
                'heading' => '1. Gem din nøgle',
                'description' => 'Din nøgle er dit login - den vises kun her og kan ikke gendannes. Gem den i din adgangskodemanager, download QR-koden, eller udskriv denne side.',
            ],
            'copy_key' => 'Kopiér nøgle',
            'validity' => 'Nøglen er gyldig indtil :date.',
            'use' => [
                'heading' => '2. Kom i gang med at søge',
                'description' => 'Åbn dette link for at aktivere nøglen i din browser. Sæt bogmærke for at forblive logget ind.',
            ],
            'copy_url' => 'Kopiér link',
            'start_searching' => 'Begynd at søge nu',
            'to_account' => 'Gå til min konto',
            'qr_alt' => 'QR-kode til nøglen',
            'no_cookies' => 'Denne browser ser ikke ud til at gemme cookies. Gem i stedet nøglen eller QR-koden ovenfor.',
        ],
        'error' => [
            'heading' => 'Det virkede ikke',
            'invalid_code' => 'Denne kode findes ikke. Tjek venligst din indtastning.',
            'invalid_token' => 'Dette link er ugyldigt eller er udløbet.',
            'already_redeemed' => 'Denne kode er allerede indløst.',
            'campaign_inactive' => 'Denne kampagne er afsluttet. Koden kan ikke længere indløses.',
            'budget_exhausted' => 'Alle gaver fra denne kampagne er allerede uddelt.',
            'rate_limited' => 'For mange forsøg. Prøv igen senere.',
            'unreachable' => 'Gavekortet kunne ikke indløses lige nu. Prøv igen senere.',
            'unknown' => 'Der opstod en uventet fejl. Prøv igen senere.',
            'retry' => 'Indtast en kode',
        ],
    ],
];
