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

    /**
     * /c — App\Http\Controllers\VoucherController.
     */
    'redeem' => [
        'enter' => [
            'heading' => 'Utiliser votre bon',
            'description' => 'Vous avez reçu un code de bon pour des recherches MetaGer gratuites ? Saisissez-le ici pour obtenir votre clé MetaGer personnelle.',
            'label' => 'Votre code de bon',
            'submit' => 'Utiliser le code',
            'invalid_code' => 'Ce code n\'est pas valide. Veuillez vérifier votre saisie.',
            'rate_limited' => 'Trop de tentatives. Veuillez réessayer plus tard.',
        ],
        'teaser' => [
            'heading' => 'Votre cadeau MetaGer',
            'tokens' => 'Jetons',
            'description' => 'Ce code vous donne votre propre clé MetaGer chargée de :tokens jetons - recherchez sur le web sans publicité et sans être suivi.',
            'validity' => 'La clé est valable :days jours après utilisation.',
            'submit' => 'Obtenir ma clé',
        ],
        'redeemed' => [
            'heading' => 'Voici votre clé MetaGer !',
            'description' => 'Votre nouvelle clé est chargée de :tokens jetons.',
            'save' => [
                'heading' => '1. Enregistrez votre clé',
                'description' => 'Votre clé est votre identifiant de connexion - elle n\'est affichée qu\'ici et ne peut pas être récupérée. Enregistrez-la dans votre gestionnaire de mots de passe, téléchargez le code QR ou imprimez cette page.',
            ],
            'copy_key' => 'Copier la clé',
            'validity' => 'La clé est valable jusqu\'au :date.',
            'use' => [
                'heading' => '2. Commencez à rechercher',
                'description' => 'Ouvrez ce lien pour activer la clé dans votre navigateur. Ajoutez-le aux favoris pour rester connecté.',
            ],
            'copy_url' => 'Copier le lien',
            'start_searching' => 'Commencer à rechercher maintenant',
            'to_account' => 'Aller à mon compte',
            'qr_alt' => 'Code QR de la clé',
            'no_cookies' => 'Ce navigateur ne semble pas conserver les cookies. Enregistrez plutôt la clé ou le code QR ci-dessus.',
        ],
        'error' => [
            'heading' => 'Cela n\'a pas fonctionné',
            'invalid_code' => 'Ce code n\'existe pas. Veuillez vérifier votre saisie.',
            'invalid_token' => 'Ce lien est invalide ou a expiré.',
            'already_redeemed' => 'Ce code a déjà été utilisé.',
            'campaign_inactive' => 'Cette campagne est terminée. Le code ne peut plus être utilisé.',
            'budget_exhausted' => 'Tous les cadeaux de cette campagne ont déjà été distribués.',
            'rate_limited' => 'Trop de tentatives. Veuillez réessayer plus tard.',
            'unreachable' => 'Le bon n\'a pas pu être utilisé pour le moment. Veuillez réessayer plus tard.',
            'unknown' => 'Une erreur inattendue s\'est produite. Veuillez réessayer plus tard.',
            'retry' => 'Saisir un code',
        ],
    ],
];
