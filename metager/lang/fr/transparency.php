<?php
return [
    'head' => [
        '1' => 'Déclaration de transparence',
        '2' => 'MetaGer est transparent',
        '3' => 'Qu\'est-ce qu\'un métamoteur ?',
        '4' => 'Quel est l\'avantage d\'un métamoteur ?',
        '5' => 'Comment se compose notre classement ?',
        'compliance' => 'Comment MetaGer répond-il aux demandes des autorités ?',
    ],
    'text' => [
        '1' => 'MetaGer est transparent. Notre code source <a href=":sourcecode"></a> est sous licence libre et accessible à tous. Nous ne stockons pas les données des utilisateurs et nous accordons une grande importance à la protection des données et à la vie privée. C\'est pourquoi nous accordons un accès anonyme aux résultats de la recherche. Cela est possible grâce à un proxy anonyme et à un accès caché par TOR. En outre, MetaGer dispose d\'une structure organisationnelle transparente, puisqu\'il est soutenu par l\'association sans but lucratif <a href=":sumalink">SUMA-EV</a>, dont tout le monde peut devenir membre.',
        '2' => [
            '1' => 'Pour expliquer ce que sont les métamoteurs, il convient d\'abord d\'expliquer brièvement comment fonctionne l\'indexation des moteurs de recherche classiques. Les moteurs de recherche ordinaires obtiennent leurs résultats de recherche à partir d\'une base de données de pages web, également appelée index. Les moteurs de recherche utilisent des "crawlers", qui collectent les pages web et les ajoutent à l\'index (base de données). Le crawler part d\'un ensemble de pages web et ouvre toutes les pages web qui y sont liées. Celles-ci sont indexées, c\'est-à-dire ajoutées à l\'index. Le crawler ouvre ensuite les pages web liées à ces pages web et continue ainsi.',
            '2' => 'Un métamoteur combine les résultats de plusieurs moteurs de recherche et les évalue à nouveau selon ses propres critères. Cela signifie que le métamoteur n\'a pas son propre index. Les métamoteurs n\'utilisent donc pas de crawlers. Ils utilisent l\'index d\'autres moteurs de recherche.',
        ],
        '3' => 'Un avantage évident des métamoteurs est que l\'utilisateur n\'a besoin que d\'une seule requête pour accéder aux résultats de plusieurs moteurs de recherche. Le métamoteur fournit les résultats pertinents sous la forme d\'une liste de résultats à nouveau triée. MetaGer n\'est pas un métamoteur pur, car nous utilisons également nos propres petits index.',
        '4' => 'Nous prenons les classements de nos moteurs de recherche sources et les pondérons. Ces classements sont ensuite convertis en scores. Des points supplémentaires sont attribués ou déduits en fonction de l\'occurrence des termes de recherche dans l\'URL et dans l\'extrait, ainsi que de l\'occurrence excessive de caractères spéciaux (par exemple, d\'autres jeux de caractères tels que le cyrillique). Nous utilisons également une liste de blocage pour supprimer certaines pages de la liste des résultats. Nous bloquons des pages web dans l\'affichage si nous sommes légalement obligés de le faire. Nous nous réservons également le droit de bloquer des pages web contenant des informations manifestement incorrectes, des pages web de très mauvaise qualité et d\'autres pages web particulièrement douteuses.',
        '5' => 'Si vous avez d\'autres questions ou incertitudes, n\'hésitez pas à utiliser notre formulaire de contact <a href=":contact"></a> et à nous poser vos questions !',
        'compliance' => 'Nous répondons aux demandes des autorités si nous sommes légalement obligés de le faire et si nous arrivons à la conclusion que notre respect ne viole pas les libertés fondamentales. Nous prenons cet examen très au sérieux. En outre, nous conservons le moins de données personnelles possible afin de réduire le risque de devoir les divulguer. Dans le tableau ci-dessous, vous trouverez des données sur les demandes des autorités que nous avons traitées au cours des cinq dernières années. D\'autres informations suivront prochainement.',
    ],
    'table' => [
        'compliance' => [
            'th' => [
                'authinfocomp' => 'Demandes d\'information satisfaites',
                'authblockcomp' => 'Demandes de blocage satisfaites',
            ],
        ],
    ],
    'alt' => [
        'text' => [
            '1' => 'Représentation visuelle de deux index qui se complètent pour former un méta-index',
        ],
    ],
];
