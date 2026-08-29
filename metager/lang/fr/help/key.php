<?php

/**
 * Fragen zum MetaGer-Schlüssel — /hilfe/schluessel.
 *
 * Aus dem "faq"-Zweig von pass/lang/<locale>/help.json des Keymanagers.
 */

return [
    "heading" => "Questions sur la clé MetaGer",
    "faqs" => [
        [
            "summary" => "Comment fonctionne la clé MetaGer ?",
            "description" => "Avec une clé MetaGer, vous effectuez des recherches sans publicité. Vous recevez des jetons dont une recherche est déduite par recherche. Lorsque vous utilisez une clé MetaGer, toutes les fonctions qui protègent MetaGer des appels automatisés sont désactivées. Cela signifie que vous ne verrez pas de demandes captcha et que votre adresse IP ne sera pas conservée pendant une durée limitée. En d'autres termes, MetaGer sera plus rapide, plus fiable et plus sûr.",
        ],
        [
            "summary" => "Comment fonctionne le jeton anonyme ?",
            "description" => "Vous pouvez utiliser le jeton anonyme avec notre extension de navigateur ou notre application. Cela vous permettra d'effectuer des recherches encore plus sûres avec MetaGer. Lorsque vous utilisez le jeton anonyme, une partie de votre crédit, sous la forme de mots de passe aléatoires, sera stockée sur votre appareil. Grâce à un <a href=\":tokenlink\">processus cryptographique complexe</a>, il nous est impossible d'associer les recherches que vous avez effectuées entre elles ou avec votre clé.",
        ],
        [
            "steps" => [
                [
                    "heading" => "Copier l'URL",
                    "description" => "Lorsque vous êtes sur la page de gestion de la clé MetaGer, il y a une option pour copier une URL. Avec cette URL, tous les paramètres de MetaGer, ainsi que la clé MetaGer, peuvent être sauvegardés sur un autre appareil.",
                ],
                [
                    "heading" => "Enregistrer le fichier",
                    "description" => "Lorsque vous êtes sur la page de gestion des clés MetaGer, vous avez la possibilité d'enregistrer un fichier. Cette option permet d'enregistrer votre clé MetaGer dans un fichier. Vous pouvez ensuite utiliser ce fichier sur un autre appareil pour vous y connecter avec votre clé.",
                ],
                [
                    "heading" => "Scanner le code QR",
                    "description" => "Vous pouvez également scanner le code QR affiché sur la page d'administration pour vous connecter à un autre appareil.",
                ],
                [
                    "heading" => "Saisir manuellement la clé MetaGer",
                    "description" => "Bien entendu, vous pouvez également saisir la clé manuellement sur un autre appareil.",
                ],
            ],
            "summary" => "Comment utiliser la clé MetaGer ?",
            "description" => "La clé MetaGer est automatiquement configurée et utilisée dans le navigateur. Vous n'avez donc rien d'autre à faire. Si vous souhaitez utiliser la clé MetaGer sur d'autres appareils, il existe plusieurs façons de configurer la clé MetaGer :",
        ],
        [
            "summary" => "Je dois introduire ma clé régulièrement. Que dois-je faire ?",
            "description" => "Nous demandons à votre navigateur de stocker de manière permanente la clé une fois qu'elle a été générée ou que vous vous êtes connecté. Selon la configuration de votre navigateur, il se peut que vous l'ayez paramétré pour supprimer régulièrement les cookies et les données du site web, ce qui vous déconnectera également de MetaGer. Vous avez les options suivantes :",
            "steps" => [
                [
                    "heading" => "Ajouter une exception",
                    "description" => "Dans les paramètres de Firefox, vous pouvez placer MetaGer sur une liste blanche pour une exemption de suppression des cookies et des données du site web qui vous permettra de rester connecté.",
                ],
                [
                    "heading" => "Installer notre extension de navigateur",
                    "description" => "Notre extension de navigateur pour <a href=\"https://addons.mozilla.org/firefox/addon/metager-suche/\" target=\"_blank\" rel=\"noopener\">Firefox</a> et <a href=\"https://chrome.google.com/webstore/detail/gjfllojpkdnjaiaokblkmjlebiagbphd\" target=\"_blank\" rel=\"noopener\">Chrome</a> peut stocker vos paramètres de recherche, y compris votre clé, sans utiliser de cookies, de sorte que vous pouvez supprimer toutes les données de navigation sans être déconnecté de MetaGer.",
                ],
                [
                    "description" => "Si vous utilisez un gestionnaire de mots de passe, vous pouvez y stocker la clé afin de vous connecter automatiquement. Nous proposons également une <a href=\":keylink\">URL de paramétrage</a> qui peut être stockée, par exemple sous forme de signet. Lorsque vous ouvrirez l'URL de configuration, vous vous connecterez sans avoir à saisir manuellement la clé.",
                    "heading" => "Se connecter sans saisir la clé de 36 caractères",
                ],
            ],
        ],
        [
            "summary" => "Je ne suis pas satisfait de la clé MetaGer. Que dois-je faire ?",
            "description" => "Dans ce cas, vous pouvez demander le remboursement des jetons non utilisés dans les 30 jours suivant l'achat. Pour ce faire, vous aurez besoin de votre identifiant de paiement. Pour demander un remboursement, ouvrez la page de gestion des clés de MetaGer. Cliquez sur l'élément de menu \"Commandes\" et entrez votre numéro d'identification de paiement. Vous pouvez ensuite cliquer sur le bouton \"Request refund\" et envoyer la demande de remboursement.",
        ],
        [
            "summary" => "Comment effectuer une recherche de manière totalement anonyme ?",
            "description" => "Votre vie privée et votre anonymat sont très importants pour nous. C'est pourquoi nous proposons des modes de paiement anonymes (en espèces). Nous proposons également l'utilisation de <a href=\":tokenlink\">jetons anonymes</a>, qui peuvent même être utilisés pour effectuer des recherches de manière vérifiable et anonyme.",
        ],
        [
            "summary" => "J'ai besoin d'une facture. Comment puis-je l'obtenir ?",
            "description" => "Pour ce faire, vous n'avez besoin que de votre identifiant de paiement. Pour demander la facture, ouvrez la page d'administration de la clé MetaGer. Cliquez sur l'élément de menu \"Commandes\" et saisissez votre numéro d'identification de paiement. Vous pouvez maintenant cliquer sur le bouton \"Demander une facture\" et lancer la demande de facture. Pour la facture, nous avons besoin de votre nom complet, de votre adresse e-mail et de votre adresse.",
        ],
        [
            "summary" => "Je souhaite charger automatiquement ma clé MetaGer. Comment faire ?",
            "description" => "Pour nos membres, la clé incluse dans l'adhésion est automatiquement rechargée tous les mois. Le montant du jeton dépend de la cotisation payée.",
        ],
        [
            "summary" => "J'ai reçu une carte ou un lien avec un code de bon. Qu'en faire ?",
            "description" => "Certaines organisations offrent des clés MetaGer avec un crédit fixe, par carte promotionnelle ou par lien. Ouvrez <a href=\":voucherlink\">notre page d'utilisation</a>, saisissez le code imprimé ou scannez le QR code de la carte. Vous recevez aussitôt une nouvelle clé MetaGer avec le crédit offert, valable pour une durée limitée. Chaque code ne peut être utilisé qu'une seule fois.",
        ],
    ],
    "more-questions" => "Vous avez d'autres questions ? N'hésitez pas à utiliser notre <a href=\":contactlink\" target=\"_blank\">formulaire de contact</a>.",
];
