<?php
return [
    'selist' => [
        'explanation_a' => 'Essayez d\'abord d\'installer le dernier plugin disponible. Il suffit d\'utiliser le lien situé sous le champ de recherche, qui détecte automatiquement le navigateur.',
        'title' => 'Je souhaite ajouter metager.de à la liste des moteurs de recherche de mon navigateur.',
        'explanation_b' => 'Certains navigateurs ont besoin d\'une URL. Veuillez utiliser "https://metager.org/meta/meta.ger3?eingabe=%s" sans les points d\'interrogation. Si vous rencontrez encore des problèmes, veuillez écrire un courriel à <a href="/en/kontakt" target="_blank" rel="noopener">.</a>',
    ],
    'title' => 'MetaGer - Aide',
    'backarrow' => ' Retour',
    'suchfunktion' => [
        'title' => 'Fonctions de recherche',
    ],
    'stopworte' => [
        'title' => 'Exclure les mots isolés',
        '1' => 'Si vous souhaitez exclure des mots dans le résultat de la recherche, vous devez mettre un "-" devant ce mot.',
        '2' => 'Exemple : Vous cherchez une nouvelle voiture, mais pas de BMW. Dans ce cas, votre recherche devrait être la suivante : <div class="well well-sm">nouvelle voiture -bmw</div>',
        '3' => 'voiture neuve -bmw',
    ],
    'mehrwortsuche' => [
        'title' => 'Recherche de plusieurs mots',
        '1' => 'Sans guillemets, vous obtiendrez des résultats contenant un ou plusieurs mots de votre recherche. Utilisez les guillemets pour la recherche d\'expressions exactes, de citations....',
        '2' => 'Exemple : la recherche de Shakespears <div class="well well-sm">to be or not to be</div> donnera de nombreux résultats, mais la phrase exacte ne sera trouvée qu\'en utilisant <div class="well well-sm">"to be or nor to be".</div>',
        '3' => [
            'example' => '"table ronde" "décision"',
            'text' => 'Veuillez utiliser des guillemets pour vous assurer que les mots recherchés figurent dans la liste des résultats.',
        ],
        '4' => [
            'example' => '"décision de table ronde"',
            'text' => 'Mettez des mots ou des phrases entre guillemets pour rechercher des combinaisons exactes.',
        ],
    ],
    'urls' => [
        'title' => 'Exclure des URL',
        'explanation' => 'Utilisez "-url :" pour exclure les résultats de recherche contenant les mots spécifiés.',
        'example_b' => 'Tapez <i>mes mots de recherche</i> -url:dog',
        'example_a' => 'Exemple : Vous ne voulez pas que le mot "chien" figure dans les résultats :',
    ],
    'bang' => [
        'title' => '!bangs',
        '1' => 'MetaGer utilise une orthographe un peu spéciale appelée "!bang syntax". Un !bang commence par un " !" et ne contient pas de blancs ("!twitter", "!facebook" par exemple). Si vous utilisez un !bang supporté par MetaGer, vous verrez une nouvelle entrée dans les "Quicktips". Nous vous dirigeons alors vers le service spécifié (cliquez sur le bouton).',
    ],
    'faq' => [
        '18' => [
            'h' => 'Pourquoi les !bangs ne sont-ils pas ouverts directement ?',
            'b' => 'Les !bang -\"redirections\" font partie de nos quicktips et nécessitent un clic supplémentaire. Nous avons dû choisir entre la facilité d\'utilisation et le contrôle des données. Nous estimons qu\'il est nécessaire de montrer que les liens sont la propriété de tiers (DuckDuckGo). Il y a donc une double protection : d\'une part, nous ne transférons pas vos mots de recherche, mais seulement le !bang à DuckDuckGo. D\'autre part, l\'utilisateur confirme explicitement le !bang-cible. Nous n\'avons pas les ressources nécessaires pour maintenir tous ces !bangs, nous en sommes désolés.',
        ],
    ],
    'searchinsearch' => [
        'title' => 'Recherche dans la recherche',
        '1' => 'Le résultat sera stocké dans un nouvel onglet apparaissant à droite de l\'écran. Il s\'appelle "Résultats enregistrés". Vous pouvez y enregistrer les résultats de plusieurs recherches. L\'onglet persiste. En entrant dans cet onglet, vous obtenez votre liste de résultats personnelle avec des outils pour filtrer et trier les résultats. Cliquez sur un autre onglet pour revenir en arrière et effectuer d\'autres recherches. Vous n\'aurez pas cette possibilité si l\'écran est trop petit. Plus d\'informations (seulement en allemand pour l\'instant) : <a href="http://blog.suma-ev.de/node/225" target="_blank" rel="noopener"> Blog SUMA</a>.',
    ],
];
