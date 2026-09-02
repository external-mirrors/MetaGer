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
        'request_invoice' => 'Créer une facture',
        'request_refund' => 'Demander un remboursement',
    ],

    'invoice' => [
        'heading' => 'Facture',
        'breadcrumb' => 'Commande :reference',
        'description' => 'Si vous avez besoin d\'une facture, veuillez saisir vos informations de facturation dans le formulaire ci-dessous.',
        'ready' => 'Une facture existe déjà pour cette commande.',
        'download' => 'Télécharger la facture',
        'submit' => 'Créer une facture',
        'storage' => 'Nous sommes légalement tenus de conserver les factures émises une fois <span class="bold">10 ans</span>. Étant donné qu\'une facture doit vous être délivrée personnellement, elle contient nécessairement des données à caractère personnel (nom, adresse).',
        'error' => [
            'invalid' => 'Veuillez vérifier vos informations — certains champs obligatoires sont manquants ou trop longs.',
        ],
        'field' => [
            'company' => 'Nom de l\'entreprise (facultatif)',
            'first_name' => 'Prénom',
            'last_name' => 'Nom de famille',
            'address1' => 'Adresse 1',
            'address2' => 'Adresse 2 (facultatif)',
            'zip' => 'Code postal',
            'city' => 'Ville',
            'state' => 'État (facultatif)',
        ],
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
