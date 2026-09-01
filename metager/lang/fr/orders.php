<?php

return [
    'lookup' => [
        'heading' => 'Rechercher une commande',
        'description' => 'Saisissez l\'identifiant de paiement de l\'une de vos commandes pour en afficher les détails.',
        'placeholder' => 'ID de paiement',
        'submit' => 'Afficher la commande',
        'error' => [
            'invalid' => 'Cet identifiant de paiement n\'est pas valide.',
            'not_found' => 'Aucune commande sur votre clé ne correspond à cet identifiant de paiement.',
        ],
    ],

    'show' => [
        'heading' => 'Commande :reference',
        'breadcrumb' => 'Commandes',
        'thanks' => 'Merci pour votre achat !',
        'pending' => 'Vos jetons seront crédités dès que votre paiement nous parviendra. Vous recevrez un e-mail de confirmation dès que ce sera le cas.',
        'lookup_hint' => 'Vous pouvez rouvrir cet aperçu à tout moment en saisissant votre identifiant de paiement (:reference).',
        'order_line' => 'Commande :id du :date',
        'item' => 'Clé MetaGer : jetons',
        'count' => 'Quantité',
        'price' => 'Prix',
        'vat' => 'TVA (:rate %)',
        'total' => 'Montant total',
        'exchange_rate' => 'Taux de change',
        'download_confirmation' => 'Télécharger la confirmation de commande',
    ],
];
