<?php
return [
    'title' => 'MetaGer - Aide',
    'maps' => [
        'title' => 'MetaGer Maps',
        '3' => 'Au chargement, la carte affiche les points (POI = Points d\'Intérêt) trouvés par MetaGer, qui sont également listés dans la colonne de droite. Lors d\'un zoom, cette liste s\'adapte à la section de la carte. En passant la souris sur un marqueur de la carte ou de la liste, l\'élément correspondant est mis en évidence. Cliquez sur "Détails" pour obtenir plus d\'informations sur ce point à partir de la base de données ci-dessous.',
        '1' => 'La préservation de la vie privée à l\'ère des géants mondiaux de l\'information nous a également conduits à développer <a href="https://maps.metager.de" target="_blank">https://maps.metager.de</a>: le (à notre connaissance) seul planificateur d\'itinéraires qui offre une fonctionnalité complète via un navigateur et une application sans stocker les emplacements de l\'utilisateur. Tout cela est vérifiable car notre logiciel est open source. Pour l\'utilisation de maps.metager.de, nous recommandons notre version rapide de l\'application. Vous pouvez télécharger nos applications à partir de <a href="/app" target="_blank">ici</a> (ou bien sûr aussi à partir du Play Store).',
        '2' => 'Cette fonction cartographique est également accessible à partir de la recherche MetaGer (et vice versa). Une fois que vous avez recherché un terme dans MetaGer, vous verrez apparaître un nouvel axe de recherche "Cartes" dans le coin supérieur droit. En cliquant dessus, vous accéderez à la carte correspondante.',
    ],
    'backarrow' => 'Retour',
    'tor' => [
        'title' => 'Service caché TOR <a title="For easy help, click here" href="/hilfe/easy-language/privacy-protection#eh-torhidden" ><img class="easy-help-icon lm-only" src="/img/help-questionmark-icon-lm.svg"/><img class="easy-help-icon dm-only" src="/img/help-questionmark-icon-dm.svg"/></a>',
        '1' => 'Depuis de nombreuses années, MetaGer cache et ne stocke pas les adresses IP. Cependant, ces adresses sont temporairement visibles sur le serveur MetaGer pendant qu\'une recherche est en cours : si MetaGer était compromis, un attaquant pourrait lire et stocker vos adresses. Pour répondre aux exigences de sécurité les plus élevées, nous exploitons une instance de MetaGer sur le réseau Tor : le service caché MetaGer TOR - accessible via : <a href="/tor/" target="_blank" rel="noopener">https://metager.de/tor/</a>. Pour l\'utiliser, vous avez besoin d\'un navigateur spécial, que vous pouvez télécharger à partir de <a href="https://www.torproject.org/" target="_blank" rel="noopener">https://www.torproject.org/</a>.',
        '2' => 'Vous pouvez accéder à MetaGer dans le navigateur Tor à l\'adresse suivante : http://metagerv65pwclop2rsfzg4jwowpavpwd6grhhlvdgsswvo6ii4akgyd.onion .',
    ],
    'proxy' => [
        'title' => 'Ouvrir de manière anonyme (SafeBrowse) <a title="For easy help, click here" href="/hilfe/easy-language/privacy-protection#eh-proxy" ><img class="easy-help-icon lm-only" src="/img/help-questionmark-icon-lm.svg"/><img class="easy-help-icon dm-only" src="/img/help-questionmark-icon-dm.svg"/></a>',
        'intro' => 'Avec « Ouvrir de manière anonyme », vous visitez un site web sans qu’il voie jamais votre appareil. La page est ouverte dans un navigateur qui fonctionne sur nos serveurs : SafeBrowse. Le site n’a affaire qu’à ce navigateur et n’apprend ni votre adresse IP ni quoi que ce soit sur votre appareil, votre navigateur ou vos cookies.',
        'use' => [
            'title' => 'Comment ouvrir une page de manière anonyme',
            'text' => [
                'Cliquez sur « Ouvrir de manière anonyme » (l’icône du bouclier) sous un résultat. La page s’ouvre dans un nouvel onglet.',
                'Si vous voulez ouvrir chaque résultat de manière anonyme, activez « Ouvrir les résultats de manière anonyme » dans les paramètres. Chaque lien d’un résultat passe alors par SafeBrowse, et « Ouvrir directement » ouvre une page sans SafeBrowse.',
            ],
        ],
        'session' => [
            'title' => 'La session',
            'text' => [
                'Le premier résultat ouvert de manière anonyme démarre une session. Chacun des suivants s’ouvre dans la même session, dans un onglet à lui. Vous y naviguez comme d’habitude : suivre des liens, remplir des formulaires, saisir une nouvelle adresse ou un terme de recherche dans la barre d’adresse. Vous restez protégé pendant ce temps.',
                'Une session coûte des jetons de votre clé MetaGer ; combien et pour quelle durée est indiqué avant le démarrage. Tant que vous utilisez la session, elle est prolongée automatiquement. Lorsque vous ne l’utilisez plus, elle se termine d’elle-même peu après, ou vous la terminez avec « Fermer la session ».',
                'Lorsque la session se termine, le navigateur sur nos serveurs est supprimé avec tout ce qu’il contenait : pages visitées, cookies, connexions et fichiers téléchargés. La session suivante commence avec un nouveau navigateur vide. Enregistrez donc auparavant les fichiers téléchargés sur votre appareil via « Téléchargements ».',
            ],
        ],
        'views' => [
            'title' => 'Deux affichages',
            'text' => [
                '« Page » montre une copie du site web sans ses scripts. Vous y lisez, faites défiler, recherchez et copiez comme sur n’importe quelle page ; vos clics et vos saisies vont au navigateur sur nos serveurs. « Navigateur complet » montre ce navigateur sous forme d’image, avec tous ses onglets. Tout y fonctionne, y compris les vidéos et les pages que « Page » ne peut pas afficher. Vous passez de l’un à l’autre dans la barre du haut.',
            ],
        ],
        'nojs' => [
            'title' => 'Sans JavaScript',
            'text' => [
                'Même si JavaScript est désactivé dans votre navigateur, « Ouvrir de manière anonyme » ouvre la page dans SafeBrowse. Vous voyez alors une copie de la page dans laquelle les liens, les boutons et les formulaires fonctionnent. Elle est rechargée après chaque clic, mais ne change pas d’elle-même. Les vidéos, les boîtes de dialogue de la page et l’envoi de fichiers ne fonctionnent pas de cette manière. Si votre navigateur bloque les cookies, les résultats que vous ouvrez depuis une page de résultats arrivent quand même dans la même session, même si vous poursuivez votre recherche depuis celle-ci.',
            ],
        ],
        'limits' => [
            'title' => 'Ce que « Ouvrir de manière anonyme » ne fait pas',
            'text' => [
                'Le site web ne vous reconnaît pas à votre appareil, mais bien à ce que vous lui dites vous-même : si vous vous connectez ou saisissez des données personnelles, il sait qui vous êtes. Si vous ne voulez pas non plus vous montrer à MetaGer, utilisez MetaGer via Tor (voir plus haut).',
            ],
        ],
    ],
    'content' => [
        'title' => 'Contenu douteux / Protection de la jeunesse <a title="For easy help, click here" href="/help/easy-language/privacy-protection#eh-content" ><img class="easy-help-icon lm-only" src="/img/help-questionmark-icon-lm.svg"/><img class="easy-help-icon dm-only" src="/img/help-questionmark-icon-dm.svg"/></a>',
        'explanation' => [
            '1' => 'J\'ai reçu des "hits" que je trouve non seulement ennuyeux, mais qui contiennent aussi, à mon avis, des contenus illégaux !',
            '2' => 'Si vous trouvez sur l\'internet un contenu que vous considérez comme illégal ou préjudiciable aux mineurs, vous pouvez contacter <a href="mailto:hotline@jugendschutz.net" target="_blank" rel="noopener">hotline@jugendschutz.net</a> par courrier électronique ou visiter <a href="http://www.jugendschutz.net/" target="_blank" rel="noopener">www.jugendschutz.net</a> et remplir le formulaire de plainte qui y est disponible. Il est utile de fournir une brève note sur ce que vous considérez comme inadmissible et sur la manière dont vous êtes tombé sur ce contenu. Vous pouvez également nous signaler directement tout contenu douteux. Pour ce faire, envoyez un courriel à notre responsable de la protection de la jeunesse (<a href="mailto:jugendschutz@metager.de" target="_blank" rel="noopener">jugendschutz@metager.de</a>).',
        ],
    ],
    'privacy' => [
        'title' => "Anonymat et sécurité des données",
        '2' => 'Aucune de ces données n\'est utilisée, stockée, conservée ou traitée d\'une autre manière chez MetaGer (exception : stockage à court terme pour la protection contre le piratage et les attaques de robots). Parce que nous considérons ce sujet extrêmement important, nous avons également créé des moyens pour vous aider à atteindre le plus haut niveau de sécurité : le service caché MetaGer TOR et notre navigateur anonyme.',
        '3' => "De plus amples informations sont disponibles ci-dessous. Les fonctions sont accessibles sous \"Services\" dans la barre de navigation.",
        '1' => 'Cookies de suivi, identifiants de session et adresses IP <a title="For easy help, click here" href="/hilfe/easy-language/privacy-protection#eh-tracking" ><img class="easy-help-icon lm-only" src="/img/help-questionmark-icon-lm.svg"/><img class="easy-help-icon dm-only" src="/img/help-questionmark-icon-dm.svg"/></a>',
    ],
    'easy-help' => 'En cliquant sur le symbole <a title="For easy help, click here" href="/hilfe/easy-language/privacy-protection" ><img class="easy-help-icon lm-only" src="/img/help-questionmark-icon-lm.svg"/><img class="easy-help-icon dm-only" src="/img/help-questionmark-icon-dm.svg"/></a> , vous accéderez à une version simplifiée de l\'aide.',
];
