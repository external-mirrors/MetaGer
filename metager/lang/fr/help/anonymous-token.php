<?php

/**
 * Anonyme Token — /hilfe/anonyme-token.
 *
 * Aus dem "anonymous-token"-Zweig von pass/lang/<locale>/help.json.
 * Der Pfad /keys/help/anonymous-token wird dauerhaft hierher weitergeleitet:
 * er steht in bereits versandten Mitglieds-Willkommensmails.
 */

return [
    "heading" => "Jetons anonymes",
    "description" => [
        "heading" => "Qu'est-ce qu'un jeton anonyme ?",
        "text" => "Si vous utilisez une clé MetaGer, vous recevrez un mot de passe généré de manière aléatoire que votre navigateur nous envoie à chaque requête de recherche afin que nous puissions activer la recherche sans publicité. Si vous utilisez notre <a href=\"/app\" target=\"_blank\">application Android</a>, ou notre extension web pour <a href=\"https://chrome.google.com/webstore/detail/gjfllojpkdnjaiaokblkmjlebiagbphd\" target=\"_blank\" rel=\"noopener\">Chrome</a> et <a href=\"https://addons.mozilla.org/firefox/addon/metager-suche/\" target=\"_blank\" rel=\"noopener\">Firefox</a>, au lieu du mot de passe, votre navigateur nous envoie un mot de passe généré de manière aléatoire (jeton anonyme) avec chaque demande de recherche pour l'authentification, qui est généré localement. Cela garantit que chaque mot de passe est unique et n'a aucun lien avec la clé MetaGer actuelle, ni entre les différents mots de passe.",
    ],
    "meaning" => [
        "heading" => "Qu'est-ce que cela signifie pour vos recherches authentifiées ?",
        "texts" => [
            "En utilisant l'algorithme décrit, vous et nous pouvons garantir qu'un nouveau mot de passe aléatoire sans rapport avec votre clé MetaGer est utilisé à chaque fois pour vos recherches authentifiées.",
            "La particularité de cet algorithme est que tous les composants qui garantissent l'anonymat sont exécutés localement sur votre appareil. Ce code source exécuté peut être vu et vérifié par n'importe qui à tout moment.",
            "Mieux encore, vous n'avez pas besoin de configurer quoi que ce soit pour utiliser les jetons anonymes. Il suffit d'installer/utiliser notre extension de navigateur/application Android pour que votre appareil utilise des jetons anonymes pour toutes les recherches.",
        ],
    ],
    "technical-function" => [
        "heading" => "L'algorithme sous-jacent :",
        "texts" => [
            "Dans une signature RSA classique, nous prendrions le jeton anonyme <code>m</code>, l'exposant secret <code>d</code>, et le module public <code>N</code> de notre clé privée et créerions la signature en utilisant <code>m^d (mod N)</code>. Cependant, nous voulons que <code>m</code> reste secret.",
            "Par conséquent, votre terminal crée un nombre aléatoire <code>r</code> à l'aide d'un générateur de nombres aléatoires, qui n'est pas lié au diviseur de <code>N</code>. Le plus grand diviseur commun de <code>r</code> et de <code>N</code> doit donc être <code>1</code>.",
            "Comme <code>r</code> est un nombre aléatoire, il s'ensuit que <code>m'</code> ne révèle aucune information sur le jeton anonyme stocké localement <code>m</code>.",
            "Notre serveur reçoit maintenant le jeton anonyme obscurci <code>m'</code> de votre appareil final ainsi que la clé MetaGer à utiliser. Nous soustrayons un jeton de la clé et renvoyons la signature également obscurcie <code>s'&Congruent; (m')^d (mod N)</code> à votre terminal.",
            "Votre terminal peut maintenant calculer la signature RSA valide <code>s</code> pour le jeton anonyme non chiffré : <code>s&Congruent; s' r^-1 (mod N)</code>. Cela fonctionne car pour les clés RSA, <code>r^(e*d)&Congruent; r (mod N)</code>. Et donc aussi : <code>s &Congruent; s' * r^-1 &Congruent; (m')^d*r^-1 &Congruent; m^d*r^(e*d)*r^-1 &Congruent; m^d*r*r^-1 &Congruent; m^d (mod N)</code>.",
            "Votre appareil final nous envoie désormais le jeton anonyme non crypté avec la signature associée pour autorisation lors d'une recherche. La clé elle-même ne nous est plus envoyée pendant la recherche.",
        ],
    ],
    "problem" => [
        "heading" => "Quel problème les jetons anonymes sont-ils censés résoudre ?",
        "text" => "Si votre navigateur nous envoie toujours le même mot de passe à chaque requête de recherche, nous aurions au moins théoriquement la possibilité d'établir une corrélation entre toutes les recherches effectuées avec la même clé. Même si nous n'y parvenons pas, la confiance reste bien entendu nécessaire pour garantir l'anonymat de votre recherche. Pour que nous ne soyons pas seulement obligés de promettre la recherche anonyme, mais que nous puissions aussi la prouver, nous avons introduit les jetons anonymes.",
    ],
    "general-function" => [
        "heading" => "Comment cela fonctionne-t-il ?",
        "texts" => [
            "Nous voulons donc que des mots de passe à usage unique soient générés directement à partir de votre terminal, que vous nous envoyez ensuite pour authentification lors de vos recherches. Cependant, pour chaque jeton anonyme sur votre terminal, nous devons nous assurer qu'un jeton normal a été soustrait de votre clé MetaGer pour ce jeton, sans (et c'est là le point essentiel) nous dire quelle clé MetaGer a été utilisée pour générer le jeton anonyme.",
            "Traditionnellement, nous utilisons une forme de signature cryptographique à cette fin. Dans ce cas, nous signons le jeton anonyme généré. Lorsque vous nous envoyez ultérieurement le jeton anonyme accompagné de la signature, nous pouvons être sûrs que le jeton anonyme est valide. Toutefois, pour obtenir la signature, vous auriez dû nous envoyer le jeton anonyme avec votre véritable clé, ce qui aurait annulé l'anonymat.",
            "C'est pourquoi nous utilisons à la place une forme modifiée de signature cryptographique, la <a href=\"https://fr.wikipedia.org/wiki/Signature_aveugle\" target=\"_blank\">signature aveugle</a>. Pour créer une analogie avec la vie réelle, c'est comme si vous nous envoyiez votre jeton anonyme dans une enveloppe en papier carbone. Dans cet exemple, nous ne pourrions pas ouvrir l'enveloppe, mais nous pourrions signer de l'extérieur, de sorte que notre signature serait transférée au jeton anonyme à l'intérieur. Lorsque vous recevrez l'enveloppe, vous pourrez la retirer et nous renvoyer le mot de passe et la signature plus tard. Nous pourrions alors confirmer qu'il s'agit bien de notre signature.",
            "En fait, cette analogie est un peu trompeuse, car dans le processus réel, au moment où vous nous envoyez le jeton anonyme et la signature, non seulement nous n'avons jamais vu le jeton anonyme auparavant, mais nous n'avons jamais vu la signature elle-même. Et pourtant, nous pouvons vérifier que la signature a été générée par nous.",
        ],
    ],
];
