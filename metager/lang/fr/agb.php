<?php

/**
 * Allgemeine Geschäftsbedingungen für die Token-Aufladung — /agb.
 *
 * Vertragstext, aus pass/lang/<locale>/agb.json des Keymanagers übernommen.
 * Tests\Feature\AgbTest vergleicht die gerenderte deutsche Fassung Zeile für
 * Zeile mit einem Abzug der alten Seite; jede Abweichung steht dort
 * ausgeschrieben, damit sie mit rechtlichem Blick nachlesbar bleibt. Es sind
 * drei:
 *
 *   - Der Text nennt seine eigene Fundstelle. Die stand wörtlich als
 *     "metager.de/keys/agb" im Vertrag und ist jetzt der Platzhalter :agburl.
 *   - Die Paketliste in §4 nannte 12000 Token, die es nicht zu kaufen gibt,
 *     und verschwieg die 500, die es gibt. Sie zählt jetzt genau das auf, was
 *     der Checkout verkauft — AgbTest::testTheTokenPackagesAreTheOnesThatCanBeBought
 *     vergleicht sie in allen Sprachen mit App\Landing\KeyPrice.
 *   - Weil sich der Vertragstext damit geändert hat, ist auch das "Stand:"-
 *     Datum weitergerückt.
 */

return [
    "heading" => "Conditions générales pour le rechargement de jetons (sur la clé)",
    "date" => "Statut : Août 2026",
    "translationNotice" => "Note : Il s'agit d'une traduction des conditions générales allemandes en vigueur. La version juridiquement contraignante peut être consultée à l'adresse suivante : <a href=\":linkGerman\">.</a>",
    "paragraphs" => [
        [
            "heading" => "Prestataire, champ d'application et modifications",
            "paragraphs" => [
                "Les conditions générales suivantes s'appliquent aux relations commerciales entre les utilisateurs des services des sites web metager.de et metager.org, en particulier le rechargement de jetons sur la clé, et l'opérateur SUMA-EV. Dans ce qui suit, les \"utilisateurs\" de la recharge de jetons / de la clé sont également appelés \"utilisateurs\", et SUMA-EV est ci-après dénommée \"MetaGer\".",
                "Ces CGV sont disponibles à tout moment sur le site :agburl et peuvent être consultées, sauvegardées et imprimées à tout moment. Les commandes passées peuvent être consultées dans l'espace client sous \"Gérer la clé - Commandes\" en saisissant l'identifiant de paiement. Cela n'est possible que dans un délai de 30 jours à compter de la date d'achat.",
                "Ces conditions s'appliquent exclusivement aux utilisateurs qui sont des consommateurs au sens de l'article 13 du code civil allemand. Un consommateur est toute personne physique qui conclut un acte juridique à des fins qui ne sont principalement ni commerciales ni indépendantes.",
                "MetaGer se réserve le droit d'élargir ou de restreindre le groupe d'utilisateurs et le groupe de participants éligibles et se réserve en outre le droit de modifier ou de compléter les présentes conditions générales pour les \"utilisateurs\" à tout moment si cela s'avère nécessaire dans l'intérêt d'un traitement simple et sûr ou pour prévenir les abus. Les modifications des conditions générales seront annoncées par publication sur le site web de MetaGer. Si l'utilisateur n'est pas d'accord avec ces modifications ou compléments des CGV, il doit s'y opposer par écrit auprès de MetaGer dans un délai de 4 semaines. Dans le cas contraire, les CG modifiées sont considérées comme approuvées et deviennent ainsi une partie effective du contrat.",
                "Le moteur de recherche en ligne metager.de, ses sites partenaires et les logiciels associés sont exploités par SUMA-EV. Le siège social de SUMA-EV est situé à Henniesruh 28D, 30655 Hanovre. SUMA-EV est représentée par le conseil d'administration, lui-même représenté par le directeur général Dominik Hebeler. Numéro d'enregistrement : VR200033, tribunal d'enregistrement : Amtsgericht Hannover.",
                "Les coordonnées suivantes s'appliquent :\nTéléphone : +49 511 34000070\nFax : +49 511 34001023\nFormulaire de contact : metager.de/kontakt\n*Numéro de téléphone fixe national.\n",
                "Conformément au règlement sur la résolution des litiges en ligne en matière de consommation, nous renvoyons au lien suivant : http://ec.europa.eu/consumers/odr/",
            ],
        ],
        [
            "heading" => "Conclusion du contrat et conditions de paiement",
            "paragraphs" => [
                "La mise à disposition des différents paquets de jetons par MetaGer ne constitue pas une offre contractuelle juridiquement contraignante, mais seulement une invitation non contraignante à l'utilisateur à effectuer une recharge ou un achat. En cliquant sur le bouton \"Effectuer le paiement\" ou sur un texte comparable, l'utilisateur soumet une offre juridiquement contraignante de conclure un contrat d'achat avec MetaGer.",
                "Avant de soumettre la commande de manière contraignante, l'utilisateur peut retourner sur le site Web où les informations sont enregistrées et corriger les erreurs de saisie ou annuler le processus en fermant le navigateur Internet en appuyant sur le bouton \"Retour\" dans le navigateur Internet utilisé après avoir vérifié ses données.",
                "Les prix indiqués incluent la TVA légale et d'autres composantes du prix. Comme il s'agit d'un service, aucun envoi n'est nécessaire et les jetons sont mis à disposition immédiatement après la fin du processus de paiement. Le paiement anticipé est possible. Si l'utilisateur a choisi le paiement anticipé, il s'engage à payer le prix d'achat immédiatement après la conclusion du contrat.",
            ],
        ],
        [
            "heading" => "Garantie, langage contractuel et service clientèle",
            "paragraphs" => [
                "Les dispositions légales en matière de garantie s'appliquent.",
                "La langue du contrat est l'allemand.",
                "Un service clientèle pour les questions, les réclamations et les objections est disponible en semaine de 9h00 à 16h00 aux coordonnées de SUMA-EV.",
            ],
        ],
        [
            "heading" => "Clé, options de paiement et recharge",
            "paragraphs" => [
                "L'utilisateur peut ouvrir un compte de crédit, ci-après dénommé \"clé\", le créditer et ainsi acheter des jetons. Les options de paiement comprennent entre autres les cartes de crédit et PayPal. Un paiement en espèces par courrier à l'adresse de MetaGer mentionnée ci-dessus est également possible.",
                "Pour utiliser une clé MetaGer et y recharger des jetons, la clé individuelle correspondante doit d'abord être créée sur le site Web de MetaGer.",
                "En fonction du forfait choisi, l'utilisateur reçoit exactement les jetons achetés pour une utilisation gratuite (illimitée). Les options d'achat suivantes sont disponibles :",
                [
                    "500 jetons : 5 euros",
                    "1000 jetons : 10 euros",
                    "2000 jetons : 20 euros",
                    "3000 jetons : 30 euros",
                    "4000 jetons : 40 euros",
                    "6000 jetons : 60 euros",
                ],
                "Par le biais de campagnes de marketing avec des tiers dans le cadre de campagnes de partenariat et de programmes de fidélisation de la clientèle, l'utilisateur peut également recevoir des clés. Dans ce cas, les présentes CGV et, le cas échéant, les conditions de la campagne en question sont toujours d'application.",
            ],
        ],
        [
            "heading" => "Validité et remboursement des jetons",
            "paragraphs" => [
                "Les jetons peuvent être échangés par chaque utilisateur dans l'intervalle de validité spécifié, sans limitation. La disponibilité des jetons achetés et le nombre de fois qu'ils peuvent être échangés au cours d'une période donnée sont indiqués sur la page d'aperçu de la clé.",
                "Les jetons sont valables pendant deux années civiles à partir de la date d'achat. La date de validité est toujours indiquée dans l'aperçu. Après l'expiration de la validité, l'offre expire également.",
                "Après l'achat d'un paquet de jetons, celui-ci est chargé directement sur la clé.",
                "Tous les rechargements ainsi que l'ensemble du processus, de la création de la clé à l'échange du jeton, sont totalement anonymes. La seule exception concerne les données nécessaires au traitement du paiement.",
                "MetaGer a le droit de vérifier le processus de paiement comme preuve de la recharge.",
                "L'utilisateur n'est à aucun moment obligé de fournir ses données personnelles lorsqu'il recharge la clé. Toutes les informations qu'il fournit à cet égard sont volontaires. Toutefois, certaines données personnelles peuvent être nécessaires pour la facturation et le traitement des paiements. Par conséquent, l'utilisateur doit fournir toutes les informations en toute sincérité.",
                "Les paquets de jetons achetés et les jetons résultants sur une clé MetaGer ne sont pas transférables. Cependant, le transfert de la clé respective par l'utilisateur est expressément autorisé par MetaGer.",
            ],
        ],
        [
            "heading" => "Responsabilité",
            "paragraphs" => [
                "MetaGer n'est pas responsable des dommages résultant de l'utilisation du service. MetaGer ne garantit ni n'assume aucune responsabilité quant à l'exactitude, l'exhaustivité, la fiabilité, la qualité et l'actualité d'autres sites résultant de l'utilisation des services.",
                "MetaGer propose un service en ligne.",
                "MetaGer offre volontairement la possibilité de rembourser le prix d'achat des jetons non utilisés, à condition que la méthode de paiement utilisée par l'utilisateur le permette. Les transactions de paiement en espèces sont exclues. Le remboursement doit être demandé par l'utilisateur dans les 30 jours suivant la fin du processus d'achat. À cette fin, l'identifiant de paiement correspondant doit être saisi sur la page d'aperçu.",
                "Les jetons qui ont expiré en raison du temps écoulé ne sont pas remboursables.",
                "MetaGer s'efforce toujours de maintenir les fonctions aussi disponibles que possible. MetaGer n'assume aucune garantie ou responsabilité quant à la disponibilité de l'Internet ou du réseau mobile.",
                "MetaGer n'est responsable que de l'intention et de la négligence grave. Ces limitations de responsabilité et celles mentionnées ci-dessus ne s'appliquent pas à la responsabilité en cas de dommages corporels, à la responsabilité en vertu de la loi sur la responsabilité du fait des produits ou à la responsabilité en cas de violation d'obligations contractuelles essentielles. Les obligations contractuelles essentielles sont celles qui sont absolument nécessaires à la bonne exécution d'un contrat afin que la réalisation de l'objet du contrat ne soit pas compromise et sur le respect desquelles le client peut régulièrement compter. En cas de violation fautive d'une obligation contractuelle essentielle, la responsabilité est limitée au dommage contractuel typique et prévisible au moment de la conclusion du contrat.",
                "Toutes les limitations et exclusions de responsabilité s'appliquent également aux représentants, employés exécutifs, organes et autres agents d'exécution et assistants de MetaGer.",
                "L'utilisateur s'engage à ne pas utiliser les services offerts à des fins abusives. En particulier, il est abusif de fournir des données personnelles de tiers dans le but de tromper ou d'obtenir des avantages.",
                "Si l'utilisateur a l'intention d'utiliser le service au-delà du cadre domestique habituel, il doit le signaler à MetaGer de manière informelle, de préférence via le formulaire de contact, au début de cette utilisation.",
            ],
        ],
        [
            "heading" => "Dispositions finales",
            "paragraphs" => [
                "Le droit allemand s'applique. L'application de la Convention des Nations unies sur les contrats de vente internationale de marchandises est exclue.",
                "La nullité d'une ou de plusieurs dispositions des présentes conditions générales n'affecte pas la validité des autres dispositions. Les parties s'engagent à remplacer les dispositions invalides ou nulles par de nouvelles dispositions qui respectent juridiquement le contenu économique des dispositions invalides ou nulles. Il en va de même si une lacune apparaît dans le contrat. Pour combler cette lacune, les parties s'engagent à travailler à l'établissement de dispositions appropriées dans le présent contrat qui se rapprochent le plus possible de ce que les parties auraient déterminé conformément au sens et à l'objet du présent contrat si elles avaient examiné ce point. À défaut d'accord, la loi s'applique à titre complémentaire.",
            ],
        ],
    ],
];
