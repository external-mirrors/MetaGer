<?php

/**
 * Aufladen (/konto/aufladen) — App\Http\Controllers\ChargeController.
 *
 * `cash`, `consent` und micropayment.prepay sind wortgleich aus dem Keymanager
 * übernommen (dessen checkout.json unter payments.cash/payments.prepay/
 * micropayment, dessen order.json unter agb und revocation, je Sprache) —
 * dieselbe Kasse, jetzt hier gerendert. `page`, `manual`,
 * micropayment.lastschrift/directbanking (dort nur Logos, keine Texte),
 * `returned` und vrpayment.label/submit/error.failed sind neu; vrpayment.privacy
 * ist wortgleich aus dem Keymanager übernommen wie cash/consent/micropayment.
 */
return [
    'page' => [
        'change' => 'Modifier la quantité',
        'methods' => [
            'heading' => 'Choisir le mode de paiement',
            'more' => 'Autres modes de paiement',
            'back' => 'Choisir un autre mode de paiement',
            'cash_note' => 'Anonyme',
        ],
        'cancel' => 'Retour au compte',
    ],

    'cash' => [
        'label' => 'Espèces',
        'description' => 'Vous pouvez également charger votre clé contre de l\'argent. Pour ce faire, il vous suffit de nous envoyer par courrier le numéro de commande suivant, accompagné du montant souhaité. Veuillez noter que le numéro de commande doit être lisible pour que nous puissions le traiter.',
        'note' => 'Veuillez noter ce qui suit :',
        'no_large_values' => 'Pour votre propre sécurité, ne nous envoyez pas plus de 100 € par la poste. Nous n\'assumons aucune responsabilité quant à l\'itinéraire de transport. Il vous incombe de veiller à ce que la lettre nous parvienne.',
        'no_coins' => 'Nous n\'acceptons que les billets de banque. N\'envoyez pas de pièces de monnaie !',
        'accepted_currencies' => 'Nous n\'acceptons que les devises suivantes : EUR, USD, CAD, GBP.',
        'currency_translation' => 'Nous facturons toujours les montants en EUR. Si vous nous envoyez une autre devise, le montant envoyé sera converti au taux de change du jour',
        'no_refund' => 'En raison des lois applicables en matière de blanchiment d\'argent, un remboursement ou un retour n\'est malheureusement pas possible. Cependant, une fois que le paiement a été comptabilisé par nos soins, vous pouvez saisir l\'identifiant de paiement envoyé dans la rubrique "Commandes" pour obtenir un aperçu de la commande et/ou demander une facture.',
        'generate' => 'Générer l\'identifiant de paiement',
        'error' => [
            'unreachable' => 'Un problème est survenu lors de la création de votre commande. Veuillez réessayer plus tard.',
        ],
        'order' => [
            'heading' => 'Votre identifiant de paiement',
            'copy' => 'Copie de l\'identifiant de paiement',
            'address_heading' => 'Envoyez la lettre à l\'adresse suivante et notez l\'identifiant du paiement pour vos propres dossiers',
            'address' => 'SUMA-EV
Postfach 51 01 43
30631 Hannover
Allemagne',
            'expiration' => 'L\'identifiant de paiement est valable jusqu\'à :date. Après cette date, il ne pourra plus être utilisé pour une recharge.',
            'unique' => 'L\'identifiant de paiement ne doit être utilisé que pour une seule recharge. Vous en recevrez un nouveau chaque fois que vous visiterez cette page !',
        ],
    ],

    'consent' => [
        'agb' => 'En poursuivant votre achat, vous acceptez nos <a href=":agblink" target="_blank">conditions générales</a>.',
        'label' => 'J\'accepte expressément que le contrat soit exécuté avant l\'expiration du délai de rétractation. Je comprends que le <a href=":revocation_link" target="_blank">droit de rétractation</a> expire dès le début de l\'exécution du contrat. En revanche, nous vous accordons un droit de retour volontaire <a href=":refundlink" target="_blank">de 30 jours</a>.',
        'error' => 'Ce champ est obligatoire',
    ],

    'manual' => [
        'label' => 'Manuel (dev)',
        'description' => 'Passez outre un paiement réel. Disponible uniquement dans un environnement de développement.',
        'submit' => 'Terminer le paiement',
    ],

    'micropayment' => [
        'prepay' => [
            'label' => 'Virement bancaire',
            'email' => [
                'label' => 'Adresse électronique',
                'description' => 'À cette adresse, vous recevrez des informations ponctuelles sur nos coordonnées bancaires et une notification lorsque le paiement est effectué.',
            ],
        ],
        'lastschrift' => ['label' => 'Prélèvement SEPA'],
        'directbanking' => ['label' => 'Virement bancaire instantané'],
        'submit' => 'Effectuer le paiement',
        'privacy' => 'En cliquant sur "Effectuer le paiement", vous serez redirigé vers notre prestataire de services de paiement <a href="https://micropayment.de" target="_blank">MicroPayment</a> pour effectuer l\'achat. Pour en savoir plus sur la protection de la vie privée <a href=":link" target="_blank">, consultez le site :link_text</a>.',
    ],

    'vrpayment' => [
        'label' => 'Wero',
        'submit' => 'Effectuer le paiement',
        'privacy' => 'En cliquant sur "Effectuer le paiement", vous serez redirigé vers notre prestataire de services de paiement <a href="https://www.vr-payment.de" target="_blank">VR Payment</a> pour finaliser l\'achat. Pour en savoir plus sur la protection de la vie privée, consultez le site <a href=":link" target="_blank">VR Payment</a>.',
        'error' => [
            'failed' => 'VR Payment a refusé ce paiement. Veuillez réessayer ou choisir un autre mode de paiement.',
            'onion' => 'Wero n’est pas disponible via notre adresse onion : le prestataire de paiement ne peut pas vous renvoyer ici ensuite. Veuillez choisir un autre mode de paiement.',
        ],
    ],

    'paypal' => [
        'heading' => 'Effectuer le paiement',
        'submit' => 'Effectuer le paiement',
        'loading' => 'Le mode de paiement est chargé',
        'cancel' => 'Le processus de paiement a été annulé. Si votre paiement a été effectué avant l\'annulation, votre commande sera traitée dès que le paiement aura été confirmé par l\'organisme de paiement. Dans le cas contraire, veuillez réessayer.',
        'privacy' => 'Les méthodes de paiement de ce groupe ne nécessitent généralement pas de compte PayPal, mais y sont traitées. Pour en savoir plus sur la confidentialité <a href="https://www.paypal.com/us/legalhub/privacy-full" target="_blank">, consultez le site PayPal</a>.',
        'noscript' => 'Ce mode de paiement nécessite JavaScript. Veuillez choisir un autre mode de paiement ou activer JavaScript.',
        'funding' => [
            'paypal' => 'PayPal',
            'card' => 'Carte de crédit / débit',
            'p24' => 'Przelewy24',
            'bancontact' => 'Bancontact',
            'blik' => 'BLIK',
            'eps' => 'EPS',
            'mybank' => 'MyBank',
        ],
        'error' => [
            'not_available' => 'Désolé, le mode de paiement sélectionné n\'est pas disponible dans votre région.',
            'generic' => 'Le processus de paiement a été annulé en raison d\'une erreur.  Si votre paiement a été effectué avant l\'annulation, votre commande sera traitée dès que le paiement sera confirmé par le processeur de paiement. Dans le cas contraire, veuillez réessayer.',
        ],
        'card' => [
            'label' => 'Carte de crédit / débit',
            'name' => 'Nom du titulaire de la carte (facultatif)',
            'number' => 'Numéro de la carte',
            'expiration' => 'Valable jusqu\'au',
            'cvv' => 'CVV',
            'error' => [
                '9500' => 'Carte de crédit rejetée comme frauduleuse',
                '5100' => 'La carte de crédit a été refusée par l\'établissement de crédit',
                '00N7' => 'CVV erroné. Veuillez vérifier la saisie',
                '5400' => 'Carte de crédit expirée',
                '5180' => 'Le contrôle de Luhn a échoué',
                '5120' => 'Carte de crédit refusée pour cause de fonds insuffisants.',
                '9520' => 'Carte de crédit rejetée comme perdue ou volée',
                '0500' => 'Carte de crédit refusée par l\'établissement de crédit',
                '1330' => 'Carte de crédit invalide. Veuillez vérifier votre inscription',
                '3ds' => 'Échec de l\'authentification 3D',
                'generic' => 'Carte de crédit refusée par l\'établissement de crédit',
            ],
        ],
    ],
    'returned' => [
        'heading' => 'Rechargement terminé',
        'paid' => 'Merci ! Votre clé a été rechargée de :amount jetons.',
        'pending' => 'Votre paiement est encore en cours de traitement. Dès qu\'il nous parviendra, votre clé sera rechargée automatiquement.',
    ],
];
