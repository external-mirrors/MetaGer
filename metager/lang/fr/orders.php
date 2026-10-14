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
        'documents' => 'Justificatif et facture',
        'request_refund' => 'Demander un remboursement',
    ],


    'refund' => [
        'heading' => 'Remboursement',
        'breadcrumb' => 'Commande :reference',
        'unavailable' => 'Il ne reste plus de solde remboursable pour cette commande — soit un remboursement a déjà été demandé, soit le mode de paiement utilisé ne prend pas en charge une demande de remboursement via ce formulaire.',
        'success' => 'Votre demande nous a été transmise avec succès. Nous la traiterons dès que possible. En fonction du mode de paiement, il peut s\'écouler quelques jours avant que le remboursement ne soit visible dans vos ventes.',
        'description' => 'Vous n\'êtes pas satisfait de votre clé ? Nous sommes désolés de l\'apprendre ! Dans ce cas, nous vous remboursons bien entendu le montant de la facture. Le remboursement est toujours effectué sur le même compte que celui utilisé pour le paiement initial. Nous sommes également heureux de recevoir vos critiques.',
        'partial_note' => 'Remarque : une partie du crédit que vous avez acheté a déjà été utilisée. Par conséquent, nous ne pouvons vous rembourser que <span class="bold">:count</span> sur <span class="bold">:total</span> recherches.',
        'message' => [
            'label' => 'Votre message (facultatif)',
        ],
        'submit' => ':amount € Demande de remboursement',
        'error' => [
            'not_allowed' => 'Un remboursement n\'est plus possible pour cette commande.',
            'unreachable' => 'Erreur dans l\'envoi de votre message. Veuillez réessayer plus tard.',
        ],
    ],
];
