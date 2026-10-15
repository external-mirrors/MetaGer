<?php

/**
 * Aufladen (/konto/aufladen) — App\Http\Controllers\ChargeController.
 *
 * Bezahlt wird bei suma-payments: hier stehen nur noch die Zustimmung
 * (`suma`, `consent`), die Schrittleiste und Kurzfassung (`page`) und die
 * Rückkehrseite (`returned`). Die Texte der eigenen Seiten für Bar, Wero,
 * micropayment und PayPal sind mit ihnen gegangen (suma-crm
 * docs/keymanager-migration-plan.md, Schritt 7).
 */
return [
    'page' => [
        'change' => 'Modifier la quantité',
        'cancel' => 'Retour au compte',
        'steps' => [
            'aria_two' => 'Rechargement en deux étapes',
            'amount' => 'Quantité',
            'pay' => 'Payer',
        ],
        'summary' => [
            'buying' => 'Vous achetez',
            'after' => 'Solde ensuite',
            'price' => 'Prix',
        ],
    ],
    'consent' => [
        'agb' => 'En poursuivant votre achat, vous acceptez nos <a href=":agblink" target="_blank">conditions générales</a>.',
        'label' => 'J\'accepte expressément que le contrat soit exécuté avant l\'expiration du délai de rétractation. Je comprends que le <a href=":revocation_link" target="_blank">droit de rétractation</a> expire dès le début de l\'exécution du contrat. En revanche, nous vous accordons un droit de retour volontaire <a href=":refundlink" target="_blank">de 30 jours</a>.',
        'error' => 'Ce champ est obligatoire',
    ],
    'suma' => [
        'label' => 'Virement, prélèvement et plus',
        'heading' => 'Payer',
        'lede' => 'Sur la page suivante, vous choisissez comment payer : :methods. Rester anonyme reste possible – le nom et l’e-mail sont facultatifs, sauf pour le prélèvement.',
        'submit' => 'Continuer vers le paiement',
        'privacy' => 'Le paiement passe par l’espace de paiement de SUMA-EV (payments.suma-ev.de) et, selon le moyen choisi, par VR Payment ou PayPal.',
        'methods' => [
            'banktransfer' => 'Virement bancaire',
            'sepa_directdebit' => 'Prélèvement SEPA',
            'card' => 'Carte de crédit/débit',
            'wero' => 'Wero',
            'paypal' => 'PayPal',
            'cash' => 'Espèces par courrier',
        ],
        'error' => [
            'unreachable' => 'Un problème est survenu lors de la création de votre commande. Veuillez réessayer plus tard.',
        ],
    ],
    'returned' => [
        'heading' => 'Rechargement terminé',
        'pending_heading' => 'Paiement en cours de traitement',
        'paid' => 'Merci ! Votre clé a été rechargée de :amount jetons.',
        'next' => 'Votre solde est disponible immédiatement : vous pouvez poursuivre vos recherches.',
        'details' => 'Voir les détails de la commande',
        'pending' => 'Votre paiement est encore en cours de traitement. Dès qu\'il nous parviendra, votre clé sera rechargée automatiquement.',
    ],
];
