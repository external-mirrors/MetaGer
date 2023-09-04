<?php
return [
    'description' => [
        'useragent' => [
            'description' => 'Lorsque vous consultez un site web, votre navigateur envoie automatiquement un identifiant, qui contient généralement des données sur le navigateur et le système d\'exploitation utilisés. Cet identifiant du navigateur (appelé agent utilisateur) peut être utilisé par les sites web, par exemple, pour reconnaître les appareils mobiles et leur présenter un résultat personnalisé.',
            'example' => 'Exemple',
            'title' => 'Identifiant de l\'agent utilisateur',
        ],
        'payment' => [
            'title' => 'Modalités de paiement',
            'name' => 'Max Mustermann, mail@example.com',
            'card' => 'Derniers chiffres du numéro de la carte de crédit',
            'description' => 'Lors de l\'achat d\'une clé MetaGer, différentes données de paiement sont requises en fonction du fournisseur de paiement.',
            'examples' => 'Exemples',
        ],
        'query' => [
            'title' => 'Requête de recherche saisie',
            'description' => 'Les termes de recherche saisis sont absolument nécessaires pour une recherche sur le web. En règle générale, aucune donnée à caractère personnel ne peut être obtenue à partir de ces termes, notamment parce qu\'ils n\'ont pas de structure fixe.',
            'examples' => 'Exemples',
            'example_1' => 'consommation d\'eau douche',
            'example_2' => 'Paroles Sur un arbre un coucou',
        ],
        'preferences' => [
            'title' => 'Préférences de l\'utilisateur',
            'description' => 'Outre les données de formulaire et les agents utilisateurs, le navigateur transfère souvent d\'autres données. Il s\'agit notamment du choix de la langue, des paramètres de recherche, des en-têtes acceptés, des en-têtes "do not track", etc.',
        ],
        'contact' => [
            'title' => 'Coordonnées',
            'description' => 'Vous trouverez ici le nom (prénom et nom de famille) et l\'adresse électronique de votre interlocuteur. Nous prenons ces données très au sérieux pour répondre à vos questions et vous donner, sans exception, d\'autres informations.',
        ],
        'message' => [
            'title' => 'Message',
            'description' => 'Le message saisi ici nous sera transmis et utilisé pour traiter votre demande.',
        ],
        'title' => 'Description des données obtenues',
        'ip' => [
            'title' => 'Adresse de protocole Internet',
            'description' => 'L\'adresse de protocole Internet (ci-après dénommée IP) est obligatoire pour pouvoir utiliser des services web tels que MetaGer. Cette IP, combinée à une date - similaire à un numéro de téléphone - identifie clairement un accès à l\'internet et son propriétaire. En général, les trois premiers blocs (sur un total de quatre) d\'une IP ne sont pas personnels. Si les blocs arrière de l\'IP sont raccourcis, l\'adresse raccourcie identifie la zone géographique approximative autour de la connexion Internet.',
            'example_full' => 'Exemples (adresse IP complète)',
            'example_partial' => 'Exemples (deux premiers blocs uniquement)',
        ],
    ],
    'base' => [
        'title' => 'Base juridique du traitement',
        'description' => 'La base juridique du traitement de vos données personnelles identifiables est soit l\'art. 6 (1) (a) GDPR si vous consentez au traitement en utilisant nos services, ou l\'Art. 6 (1) (f) GDPR si le traitement est nécessaire pour protéger nos intérêts légitimes, ou une autre base juridique si nous vous en informons séparément.',
    ],
    'rights' => [
        'title' => 'Vos droits en tant qu\'utilisateur (et nos obligations)',
        'description' => 'Afin que vous puissiez également protéger vos données personnelles, nous précisons (conformément à l\'article 13 de la DSGVO) que vous disposez des droits suivants :',
        'information' => [
            'title' => 'Droit de fournir des informations',
            'description' => 'Vous avez le droit (article 15 du RGPD) de nous demander à tout moment si nous (metager.de et SUMA-EV) possédons des données vous concernant et, le cas échéant, lesquelles. Nous vous enverrons dès que possible, c\'est-à-dire dans un délai de quelques jours, une copie complète des données que nous avons enregistrées ou que nous avons enregistrées d\'une autre manière à votre sujet, conformément à l\'article 15, paragraphe 3, sous-section 1 du RGPD. Pour ce faire, nous préférons la méthode électronique conformément à l\'article 15, paragraphe 3, alinéa 3, du RGPD ; à cette fin, nous enregistrons votre adresse électronique pour la durée du traitement. Veuillez nous informer si vous souhaitez expressément recevoir les informations sur papier.',
        ],
        'correction' => [
            'description' => 'Conformément à l\'article 16 du RGPD. Si nous avons enregistré des données incorrectes à votre sujet, vous pouvez demander qu\'elles soient corrigées. Il en va de même pour les éléments manquants, que vous avez le droit de compléter.',
            'title' => 'Droit à la correction et au complément',
        ],
        'deletion' => [
            'title' => 'Droit à l\'effacement',
            'description' => 'Conformément à l\'article 17 du GDPR',
        ],
        'processing' => [
            'title' => 'Droit à la limitation du traitement',
            'description' => 'Conformément à l\'article 18 du GDPR ; Par exemple, si vous nous avez demandé d\'effacer ou de modifier des données vous concernant, vous pouvez nous imposer une interdiction de traitement pendant le temps qu\'il nous faut pour le faire. Cela est possible indépendamment du fait que nous modifions, supprimons, etc. les données en question.',
        ],
        'complaint' => [
            'title' => 'Droit de plainte',
            'description' => 'Conformément à l\'article 13, paragraphe 2, lettre d) du RGPD, vous pouvez déposer une plainte contre nous auprès du délégué à la protection des données de l\'État de Basse-Saxe. En ligne : <a href="https://www.lfd.niedersachsen.de/startseite/">Délégué à la protection des données</a>',
        ],
        'opposition' => [
            'title' => 'Droit d\'opposition au traitement',
            'description' => 'Selon l\'article 21 du GDPR, par exemple, si vous figurez sur une liste et que vous souhaitez y figurer, vous pouvez toujours interdire le traitement ou le traitement ultérieur de ces données.',
        ],
        'portability' => [
            'title' => 'Droit à la portabilité des données',
            'description' => 'Conformément à l\'article 20 du GDPR, cela signifie que nous sommes tenus de vous fournir les données demandées d\'une manière lisible, éventuellement lisible par machine ou usuelle, afin que vous puissiez rendre les données accessibles à une autre personne en l\'état (transfert).',
        ],
        'obligation_notify' => [
            'title' => 'Obligation de notification en cas de rectification ou d\'effacement de données à caractère personnel ou de limitation du traitement :',
            'description' => 'Conformément à l\'article 19 du RGPD, si nous avions rendu les données que vous nous avez confiées accessibles à des tiers (ce que nous ne faisons jamais), nous serions tenus de les informer que, à votre demande, nous les supprimerions, les modifierions, etc.',
        ],
        'perception' => 'Pour exercer ces droits, il suffit de nous contacter en utilisant notre <a href=":contact_link">formulaire de contact</a></b>. Si vous préférez la forme épistolaire, envoyez-nous un courrier à l\'adresse de nos bureaux :',
    ],
    'changes' => [
        'title' => 'Modifications de la présente déclaration',
        'description' => 'Tout comme nos offres, cette déclaration de protection des données est également sujette à des changements constants. Nous vous conseillons donc de la relire régulièrement.',
        'date' => 'Cette version de notre politique de confidentialité est datée du : :date',
    ],
    'data' => [
        'ip' => 'Adresse IP',
        'useragent' => 'User-Agent',
        'query' => 'Requête de recherche',
        'preferences' => 'Préférences de l\'utilisateur',
        'contact' => 'Coordonnées',
        'message' => 'Message',
        'payment' => 'Données de paiement',
        'referrer' => 'le référent que vous avez envoyé',
        'gps' => 'Données de localisation',
        'optional' => 'facultatif',
        'unused' => 'Ne sera ni sauvegardé ni partagé.',
    ],
    'title' => 'Politique de confidentialité',
    'responsible_party' => [
        'title' => 'Personnes responsables et personnes de contact',
        'description' => 'MetaGer et les services associés sont exploités par <a href="https://suma-ev.de">SUMA-EV</a>, qui est également l\'auteur de cette déclaration. Dans cette déclaration, le terme "nous" désigne généralement SUMA-EV. Vous trouverez nos coordonnées dans notre site <a href=":link_impress">Mentions légales</a>. Vous pouvez nous contacter par courrier électronique en utilisant notre formulaire de contact <a href=":link_contact"></a> .',
    ],
    'principles' => [
        'title' => 'Principes',
        'description' => 'En tant qu\'association sans but lucratif, nous nous engageons en faveur du libre accès à la connaissance. Comme nous savons que la recherche libre n\'est pas compatible avec la surveillance de masse, nous prenons également la protection des données très au sérieux. Depuis toujours, nous ne traitons que les données absolument nécessaires au fonctionnement de nos services. La protection des données est toujours notre norme. Nous ne pratiquons pas le profilage, c\'est-à-dire la création automatique de profils d\'utilisateurs.',
    ],
    'contexts' => [
        'title' => 'Données entrantes par contexte',
        'metager' => [
            'title' => 'Utilisation du moteur de recherche MetaGer',
            'description' => 'Lors de l\'utilisation de notre moteur de recherche MetaGer via son formulaire web ou son interface OpenSearch, les données suivantes sont générées :',
            'query' => 'En tant que partie intégrante de la métarecherche, la requête de recherche est transmise à nos partenaires afin d\'obtenir des résultats de recherche à afficher sur la page de résultats. Les résultats reçus, y compris le terme de recherche, sont conservés pour affichage pendant quelques heures.',
            'preferences' => 'Nous utilisons ces données (par exemple, les paramètres linguistiques) pour répondre à la demande de recherche correspondante. Nous stockons certaines de ces données sur une base non personnelle à des fins statistiques.',
            'additionally' => 'Les données suivantes sont également collectées si vous utilisez notre version avec support publicitaire :',
            'botprotection' => 'Pour protéger notre service de la surcharge, nous devons limiter le nombre de recherches par connexion internet. Dans ce seul but, nous enregistrons l\'adresse IP complète et un horodatage pour une durée maximale de 96 heures. Si un grand nombre de recherches sont effectuées à partir d\'une adresse IP, celle-ci est temporairement (au maximum 96 heures après la dernière recherche) enregistrée dans une liste noire. L\'IP est ensuite supprimée.',
            'clarity' => 'Nous travaillons avec Microsoft Clarity et Microsoft Advertising pour vous offrir gratuitement des résultats de recherche et de la publicité sur Yahoo. À cette fin, des données d\'utilisation à des fins statistiques, y compris votre adresse IP, sont enregistrées sur la page de résultats de MetaGer.',
        ],
        'contact' => [
            'title' => 'Utilisation du formulaire de contact',
            'description' => 'Lorsque vous utilisez le formulaire de contact de MetaGer, les données suivantes sont générées. Nous les conservons à des fins de référence jusqu\'à deux mois après la finalisation de votre demande :',
            'contact' => 'Elle sera conservée à des fins de référence jusqu\'à deux mois après l\'achèvement de votre demande.',
        ],
        'donate' => [
            'title' => 'Utilisation du formulaire de don',
            'description' => 'Les données suivantes, transmises dans le formulaire de don, seront conservées pendant deux mois pour traitement :',
            'contact' => 'Nous utilisons ces données exclusivement pour d\'éventuelles requêtes et ne les transmettons en aucun cas à des tiers.',
            'payment' => 'Les données de paiement ne seront utilisées que pour le traitement du don et ne seront en aucun cas transmises à des tiers. Pour des raisons fiscales, nous sommes tenus de conserver ces données pendant 10 ans. Elles seront ensuite automatiquement effacées et ne feront plus l\'objet d\'aucun traitement.',
            'message' => 'Le message que vous saisissez ici nous sera transmis et pris en compte lors du traitement de votre don.',
        ],
        'key' => [
            'title' => 'Vérifier la clé MetaGer',
            'contact' => 'Nous utilisons ces données exclusivement pour d\'éventuelles demandes de renseignements ou pour la facturation et ne les transmettons en aucun cas à des tiers.',
            'payment' => 'Les données de paiement ne seront utilisées que pour le traitement du don et ne seront en aucun cas transmises à des tiers. Pour des raisons fiscales, nous sommes tenus de conserver ces données pendant 10 ans. Elles seront ensuite automatiquement effacées et ne feront plus l\'objet d\'aucun traitement.',
        ],
        'suma' => [
            'title' => 'Utilisation du site <a href="https://suma-ev.de">suma-ev.de</a>',
            'description' => 'Lors de la visite des sites web du domaine "suma-ev.de", les données suivantes sont collectées et stockées pour une durée maximale d\'une semaine :',
            'function' => 'Lors de la visite des sites web du domaine "suma-ev.de", les données suivantes sont collectées et stockées pour une durée maximale d\'une semaine :',
            'other' => 'Sur les autres sites web de nos domaines, nous ne traitons les données collectées que pour répondre aux demandes et dans le cadre des autres points de la présente déclaration de protection des données.',
            'startpage' => 'Sur la page d\'accueil de notre service MetaGer, nous utilisons l\'agent utilisateur que vous avez transmis pour vous montrer les instructions d\'installation du plug-in approprié pour votre navigateur.',
        ],
        'newsletter' => [
            'title' => 'S\'inscrire à la lettre d\'information de SUMA-EV',
            'description' => 'Afin de vous tenir informé de nos activités, nous vous proposons une lettre d\'information par courrier électronique. Nous conservons les données suivantes jusqu\'à ce que vous vous désinscriviez :',
            'contact' => 'Nous utilisons ces données exclusivement pour vous envoyer notre bulletin d\'information et ne les transmettons en aucun cas à des tiers.',
        ],
        'maps' => [
            'title' => 'Utilisation de Maps.MetaGer.de',
            'description' => 'Lors de l\'utilisation du service de cartographie MetaGer, les données suivantes sont générées :',
        ],
        'proxy' => [
            'title' => 'Utilisation du proxy d\'anonymisation',
            'description' => 'Lors de l\'utilisation du proxy d\'anonymisation, les données suivantes sont générées :',
        ],
        'quote' => [
            'title' => 'Utilisation de la recherche de citations',
            'description' => 'Le terme de recherche saisi est utilisé pour rechercher des résultats dans la base de données de citations. Contrairement à la recherche sur le web avec MetaGer, il n\'est pas nécessaire de transmettre le terme de recherche à des tiers, car la base de données de citations se trouve sur notre serveur. Aucune autre donnée n\'est enregistrée ou transmise.',
        ],
        'asso' => [
            'title' => 'Utilisation de l\'associateur',
            'description' => 'L\'associateur utilise le terme de recherche pour déterminer et afficher les termes qui lui sont associés. Les autres données ne sont ni sauvegardées ni transmises.',
        ],
        'mapsapp' => [
            'title' => 'Utilisation de l\'application MetaGer',
            'description' => 'L\'utilisation de l\'application MetaGer est la même que l\'utilisation de MetaGer via un navigateur web.',
        ],
        'plugin' => [
            'title' => 'Utilisation du plugin MetaGer',
            'description' => 'Lors de l\'utilisation du plugin MetaGer, les données suivantes sont générées :',
        ],
    ],
    'introduction' => 'Pour une transparence maximale, nous énumérons les données que nous collectons auprès de vous et l\'usage que nous en faisons. La protection de vos données est importante pour nous et devrait l\'être pour vous aussi. <strong>Veuillez lire attentivement cette déclaration ; il en va de votre intérêt.</strong>',
    'hosting' => [
        'title' => 'Hébergement',
        'description' => 'Nos services sont administrés par nous, la SUMA-EV, et exploités sur du matériel loué à Hetzner Online GmbH.',
    ],
];
