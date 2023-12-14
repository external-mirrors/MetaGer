<?php
return [
    'selist' => [
        'explanation_a' => 'Veuillez d\'abord essayer d\'installer le plugin actuel. Pour l\'installer, il suffit de cliquer sur le lien situé directement sous la boîte de recherche. Votre navigateur devrait déjà avoir été détecté à cet endroit.',
        'title' => 'Ajoutez MetaGer à la liste des moteurs de recherche de votre navigateur <a title="For easy help, click here" href="/hilfe/easy-language/functions#eh-selist"><img class="easy-help-icon lm-only" src="/img/help-questionmark-icon-lm.svg"/><img class="easy-help-icon dm-only" src="/img/help-questionmark-icon-dm.svg"/></a>',
        'explanation_b' => 'Certains navigateurs vous demandent d\'entrer une URL, qui doit être "https://metager.de/meta/meta.ger3?input=%s" sans guillemets. Vous pouvez générer l\'URL vous-même en recherchant quelque chose avec metager.de, puis en remplaçant ce qui se trouve derrière "input=" dans la barre d\'adresse par %s. Si vous rencontrez encore des problèmes, n\'hésitez pas à nous contacter : <a href="/kontalt" target="_blank" rel="noopener">Formulaire de contact</a>',
    ],
    'title' => 'MetaGer - Aide',
    'backarrow' => 'Retour',
    'stopworte' => [
        '2' => 'Exemple : Vous cherchez une nouvelle voiture, mais pas de BMW. Dans ce cas, votre recherche devrait être la suivante : <div class="well well-sm">nouvelle voiture -bmw</div>',
    ],
    'mehrwortsuche' => [
        '2' => 'Exemple : la recherche de Shakespears <div class="well well-sm">to be or not to be</div> donnera de nombreux résultats, mais la phrase exacte ne sera trouvée qu\'en utilisant <div class="well well-sm">"to be or nor to be".</div>',
        '4' => [
            'text' => 'Mettez des mots ou des phrases entre guillemets pour rechercher des combinaisons exactes.',
        ],
    ],
    'urls' => [
        'title' => 'Exclure des URL',
        'explanation' => 'Vous pouvez exclure les résultats de recherche qui contiennent des mots spécifiques dans leurs liens de résultats en utilisant "-url :" dans votre recherche.',
        'example_b' => '<i>ma recherche</i> -url:dog',
        'example_a' => 'Exemple : Vous souhaitez exclure les résultats où le mot "chien" apparaît dans le lien du résultat :',
    ],
    'bang' => [
        'title' => 'Cartes MetaGer <a title="For easy help, click here" href="/hilfe/easy-language/services#eh-maps" ><img class="easy-help-icon lm-only" src="/img/help-questionmark-icon-lm.svg"/><img class="easy-help-icon dm-only" src="/img/help-questionmark-icon-dm.svg"/></a>',
        '1' => 'MetaGer supporte dans une certaine mesure un style d\'écriture souvent appelé syntaxe "!bang".<br>Un "bang" commence toujours par un point d\'exclamation et ne contient pas d\'espaces. Lorsqu\'un !bang pris en charge est utilisé dans la requête de recherche, une entrée apparaît dans nos conseils rapides, vous permettant de poursuivre la recherche avec le service concerné (Twitter ou Facebook) en appuyant sur un bouton.',
        '2' => 'Pourquoi les !bangs ne sont-ils pas ouverts directement ?',
        '3' => 'Les "redirections" de !bang font partie de nos conseils rapides et nécessitent un "clic" supplémentaire, ce qui a été une décision difficile à prendre pour nous, car cela rend !bangs moins utile. Cependant, elle est malheureusement nécessaire car les liens vers lesquels la redirection se produit ne proviennent pas de nous mais d\'un tiers, DuckDuckGo. Nous veillons toujours à ce que nos utilisateurs gardent le contrôle : Premièrement, le terme de recherche saisi n\'est jamais transmis à DuckDuckGo, seulement le !bang. Deuxièmement, l\'utilisateur confirme explicitement la visite de la cible !bang. Malheureusement, pour des raisons de personnel, nous ne pouvons pas actuellement vérifier ou maintenir tous ces !bangs nous-mêmes.',
    ],
    'searchinsearch' => [
        '1' => 'Le résultat sera stocké dans un nouvel onglet apparaissant à droite de l\'écran. Il s\'appelle "Résultats enregistrés". Vous pouvez y enregistrer les résultats de plusieurs recherches. L\'onglet persiste. En entrant dans cet onglet, vous obtenez votre liste de résultats personnelle avec des outils pour filtrer et trier les résultats. Cliquez sur un autre onglet pour revenir en arrière et effectuer d\'autres recherches. Vous n\'aurez pas cette possibilité si l\'écran est trop petit. Plus d\'informations (seulement en allemand pour l\'instant) : <a href="http://blog.suma-ev.de/node/225" target="_blank" rel="noopener"> Blog SUMA</a>.',
    ],
    'searchfunction' => [
        'title' => "Fonctions de recherche",
    ],
    'stopwords' => [
        'title' => 'Mots vides <a title="For easy help, click here" href="/hilfe/easy-language/functions#eh-stopwordsearch"><img class="easy-help-icon lm-only" src="/img/help-questionmark-icon-lm.svg"/><img class="easy-help-icon dm-only" src="/img/help-questionmark-icon-dm.svg"/></a>',
        '3' => "voiture neuve -bmw",
        '2' => "Exemple : Vous êtes à la recherche d'une nouvelle voiture, mais certainement pas d'une BMW. Votre contribution serait la suivante :",
        '1' => "Si vous souhaitez exclure les résultats de recherche dans MetaGer qui contiennent des mots spécifiques (mots d'exclusion / stopwords), vous pouvez le faire en faisant précéder ces mots d'un signe moins.",
    ],
    'key' => [
        'title' => 'Ajouter la clé MetaGer <a title="For easy help, click here" href="/hilfe/easy-language/functions#eh-keyexplain"><img class="easy-help-icon lm-only" src="/img/help-questionmark-icon-lm.svg"/><img class="easy-help-icon dm-only" src="/img/help-questionmark-icon-dm.svg"/></a>',
        '1' => 'La clé MetaGer est automatiquement installée dans votre navigateur et utilisée. Vous n\'avez rien d\'autre à faire. Si vous souhaitez utiliser la clé MetaGer sur d\'autres appareils, il existe plusieurs façons de configurer la clé MetaGer :',
        '2' => 'Code de connexion <br>Sur la <a href = "/keys/key/enter">page de gestion</a> de la clé MetaGer, vous pouvez utiliser le code de connexion pour ajouter votre clé à un autre appareil. Il suffit d\'entrer le code à six chiffres lors de la connexion. Le code de connexion ne peut être utilisé qu\'une seule fois et n\'est valable que tant que la fenêtre est ouverte.',
        '3' => 'Copier l\'URL <br>Lorsque vous êtes sur la <a href = "/keys/key/enter">page de gestion</a> de la clé MetaGer, il y a une option pour copier une URL. Cette URL peut être utilisée pour sauvegarder tous les paramètres de MetaGer, y compris la clé MetaGer, sur un autre appareil.',
        '4' => 'Enregistrer un fichier <br>Lorsque vous êtes sur la <a href = "/keys/key/enter">page de gestion</a> de la clé MetaGer, vous avez la possibilité d\'enregistrer un fichier. Cette option permet d\'enregistrer votre clé MetaGer dans un fichier. Vous pouvez ensuite utiliser ce fichier sur un autre appareil pour vous connecter avec votre clé.',
        '5' => 'Scanner le code QR <br>Vous pouvez également scanner le code QR affiché sur la <a href = "/keys/key/enter">page de gestion</a> pour vous connecter à l\'aide d\'un autre appareil.',
        '6' => 'Saisir manuellement la clé MetaGer <br>Vous pouvez également saisir manuellement la clé sur un autre appareil.',
        'colors' => [
            '1' => 'Pour que vous puissiez facilement savoir si votre recherche est exempte de publicité, nous avons donné à nos symboles clés des couleurs. Vous trouverez ci-dessous des explications sur les couleurs correspondantes :',
            'grey' => 'Gris : Vous n\'avez pas configuré de clé. Vous utilisez la recherche libre.',
            'red' => 'Rouge : Si le symbole de votre clé est rouge, cela signifie que cette clé est vide. Vous avez utilisé toutes les recherches sans publicité. Vous pouvez recharger la clé sur la page de gestion des clés.',
            'green' => 'Vert : Si le symbole de votre clé est vert, vous utilisez une clé chargée.',
            'yellow' => 'Jaune : Si vous voyez une clé jaune, il vous reste un solde de 30 jetons. Vos recherches sont en train de s\'épuiser. Il est recommandé de recharger la clé rapidement.',
            'title' => 'Clé colorée MetaGer',
        ],
    ],
    'multiwordsearch' => [
        'title' => 'Recherche multi-mots <a title="For easy help, click here" href="/hilfe/easy-language/functions#eh-severalwords"><img class="easy-help-icon lm-only" src="/img/help-questionmark-icon-lm.svg"/><img class="easy-help-icon dm-only" src="/img/help-questionmark-icon-dm.svg"/></a>',
        '4' => [
            'example' => '"La table ronde',
            'text' => "La recherche de phrases permet de rechercher des combinaisons de mots plutôt que des mots isolés. Il suffit de mettre entre guillemets les mots qui doivent apparaître ensemble.",
        ],
        '3' => [
            'example' => '"la" "ronde" "table"',
            'text' => "Si vous voulez vous assurer que les mots de votre recherche apparaissent également dans les résultats, vous devez les mettre entre guillemets.",
        ],
        '2' => "Si cela ne vous suffit pas, vous avez deux options pour rendre votre recherche plus précise :",
        '1' => "Lorsque vous recherchez plus d'un mot dans MetaGer, nous essayons automatiquement de fournir des résultats dans lesquels tous les mots apparaissent ou sont aussi proches que possible.",
    ],
    'exactsearch' => [
        'title' => 'Recherche exacte <a title="For easy help, click here" href="/hilfe/easy-language/functions#exactsearch"><img class="easy-help-icon lm-only" src="/img/help-questionmark-icon-lm.svg"/><img class="easy-help-icon dm-only" src="/img/help-questionmark-icon-dm.svg"/></a>',
        '1' => "Si vous souhaitez trouver un mot spécifique dans les résultats de la recherche MetaGer, vous pouvez faire précéder ce mot d'un signe plus. Lorsque vous utilisez un signe plus et des guillemets, une phrase est recherchée exactement comme vous l'avez saisie.",
        '2' => "Exemple : S",
        '3' => 'Exemple : ',
        'example' => [
            '1' => "+mot d'exemple",
            '2' => '+"exemple de phrase"',
        ],
    ],
    'easy-help' => 'En cliquant sur le symbole <a title="For easy help, click here" href="/hilfe/easy-language/services" ><img class="easy-help-icon lm-only" src="/img/help-questionmark-icon-lm.svg"/><img class="easy-help-icon dm-only" src="/img/help-questionmark-icon-dm.svg"/></a> , vous accéderez à une version simplifiée de l\'aide.',
];
