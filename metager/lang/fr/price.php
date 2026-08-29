<?php

/**
 * Was ein MetaGer-Schlüssel kostet — /preise.
 *
 * Aus pass/lang/<locale>/cost.json des Keymanagers übernommen, wo diese Seite
 * bis zum Umzug lag. Die Preiszahlen selbst stehen bewusst nicht hier: sie
 * kommen über App\Landing\KeyPrice vom Keymanager, weil der Checkout sie
 * ausgibt.
 */

return [
    "headings" => [
        "Voici ce que coûte votre clé MetaGer",
        "Le résumé le plus important",
    ],
    "texts" => [
        "Pour chaque recherche web sans publicité sur MetaGer avec les paramètres par défaut, il vous sera facturé <b>1 jeton</b>. Vous pouvez à tout moment recharger votre clé avec l'un de ces paquets de jetons.",
    ],
    "short-info" => [
        [
            "heading" => "Les jetons restent valables pendant 2 ans",
            "text" => "Les jetons que vous achetez sont conçus pour rester valables jusqu'à ce qu'ils soient épuisés. Il n'y a pas d'ordre permanent.",
        ],
        [
            "heading" => "Garantie de remboursement de 30 jours",
            "text" => "Si vous n'êtes pas satisfait de votre clé, vous disposez de 30 jours après l'achat pour retourner le crédit non utilisé.",
        ],
        [
            "heading" => "La clé est automatiquement configurée et utilisée dans le navigateur.",
            "text" => "Vous n'avez rien d'autre à faire pour utiliser votre clé MetaGer dans la recherche. Après l'avoir chargée, elle est automatiquement installée dans votre navigateur et vous recevrez des informations sur la manière de l'installer facilement sur d'autres appareils.",
        ],
        [
            "heading" => "Pas de suivi",
            "text" => "Utilisez notre <a href=\":linkapp\">application Android</a>, ou notre extension de navigateur et soyez anonyme de manière prouvée en utilisant des <a href=\":linktokens\">jetons anonymes</a>.",
        ],
    ],
    "pricing" => [
        "heading" => "Voici comment se composent nos prix",
        "texts" => [
            "La majeure partie de nos revenus est directement reversée aux services de recherche que vous interrogez. Nous voulons offrir un concept durable, ce qui implique que les moteurs de recherche interrogés ne subissent aucun préjudice financier en fournissant à MetaGer des résultats de recherche anonymes et sans publicité. En outre, une part est destinée à couvrir nos frais de personnel et de serveur et, bien entendu, les frais des prestataires de services de paiement et les taxes sont inclus dans les prix.",
            "Ainsi, en sélectionnant les services de recherche à interroger, vous pouvez non seulement fixer vos propres coûts, mais aussi décider en même temps des projets que vous souhaitez soutenir. D'où la facturation par jeton.",
        ],
    ],
    "payment-methods" => [
        "texts" => [
            "Les clés MetaGer ont été conçues par nous de telle sorte qu'elles ne requièrent aucune donnée personnelle. Néanmoins, au plus tard lors de l'exécution d'un paiement, certaines données sont généralement requises. Il peut s'agir de l'IBAN du compte de paiement ou de l'adresse électronique du compte PayPal utilisé. La SUMA-EV ne traite pas elle-même ces données et ne les stocke pas. En revanche, le prestataire de services de paiement le fait en fonction du mode de paiement.",
            "C'est pourquoi nos méthodes de paiement sont configurées de manière à ce que la collecte des données utilisateur soit la plus limitée possible, voire inexistante dans certains cas.",
        ],
        "heading" => "Modes de paiement",
        "anonymous" => "Méthodes de paiement anonymes",
        "more" => "Autres modes de paiement",
    ],
    /**
     * Die Namen der Zahlungsarten. Standen im "checkout"-Namensraum des
     * Keymanagers, der dort bleibt — hierher kopiert, weil diese Seite die
     * einzige war, die sie außerhalb des Bezahlvorgangs gebraucht hat.
     */
    "methods" => [
        "cash" => "Espèces",
        "prepay" => "Virement bancaire",
        "card" => "Carte de crédit / débit",
    ],
];
