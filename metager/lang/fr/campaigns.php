<?php

return [
    'heading' => 'Campagnes de bons',
    'description' => 'Distribuez des clés à partir de votre propre solde de jetons, par exemple à des amis ou des collègues. Les clés distribuées ne déduisent leurs jetons de votre clé qu\'une fois réellement utilisées : les cadeaux non utilisés ne vous coûtent rien.',
    'unreachable' => 'Vos campagnes de bons n\'ont pas pu être chargées pour le moment. Veuillez réessayer plus tard.',
    'copy_link' => 'Copier le lien',
    'public_link' => 'Lien public',
    'delete_note' => 'Les campagnes expirées et désactivées sont supprimées automatiquement.',
    'print_cards' => 'Imprimer les cartes (PDF)',
    'disable' => 'Désactiver',
    'delete' => 'Supprimer maintenant',

    'status' => [
        'active' => 'active',
        'disabled' => 'désactivée',
        'expired' => 'expirée',
    ],

    'facts' => [
        'tokens_per_key' => ':tokens jetons par clé',
        'redeemed' => ':redeemed sur :total utilisés',
        'budget' => ':left jetons restants sur :total',
        'expires' => 'se termine le :date',
    ],

    'create' => [
        'heading' => 'Créer une campagne',
        'info' => 'La campagne est financée par cette clé : les jetons distribués sont déduits de votre solde lorsqu\'ils sont utilisés. Les campagnes durent 3 mois, les clés distribuées sont valables 1 mois après leur utilisation.',
        'name' => 'Nom (visible uniquement par vous)',
        'tokens_per_key' => 'Jetons par clé distribuée',
        'total_volume' => 'Nombre maximal de jetons au total',
        'total_volume_hint' => 'Votre clé contient actuellement :charge jetons. Vous ne pouvez jamais distribuer plus que votre solde.',
        'voucher_count' => 'Nombre de bons (facultatif)',
        'voucher_count_hint' => 'Par défaut : le total maximal divisé par les jetons par clé.',
        'submit' => 'Créer la campagne',
        'error' => [
            'tokens_per_key_too_high' => 'Les jetons par clé ne peuvent pas dépasser le total maximal.',
            'voucher_count_out_of_range' => 'Le nombre de bons ne correspond pas aux jetons par clé et au total maximal.',
            'over_budget' => 'Le total maximal dépasse votre solde disponible.',
            'too_many_active' => 'Vous avez déjà le nombre maximal de campagnes actives.',
            'invalid' => 'La campagne n\'a pas pu être créée. Veuillez vérifier vos informations.',
            'unreachable' => 'La campagne n\'a pas pu être créée pour le moment. Veuillez réessayer plus tard.',
        ],
    ],
];
