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
        'error' => [
            'description' => 'Lorsqu\'une erreur technique survient dans notre application, une description de l\'erreur accompagnée d\'une trace de pile est automatiquement générée. Cela nous permet de localiser l\'endroit où l\'erreur s\'est produite dans le code source afin de pouvoir la corriger.',
            'title' => 'Rapport d\'erreur (trace de pile)',
        ],
        'reduction' => [
            'title' => 'Justificatif permettant de bénéficier d\'une réduction sur la cotisation',
            'description' => 'Un document que vous téléchargez pour justifier votre droit à une cotisation réduite, par exemple un avis confirmant la réception de prestations sociales.',
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
        'error' => 'Rapport d\'erreur',
        'reduction' => 'Preuve de réduction',
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
            'suggest' => 'Si les suggestions de recherche sont activées dans la barre d\'adresse de votre navigateur (facultatif), nous devons stocker vos paramètres de suggestion sur notre serveur. La clé sera une chaîne concaténée de votre adresse IP, de votre useragent et de l\'en-tête accept-language de votre navigateur, qui est hachée en sha1. Si le paramètre est désactivé (par défaut), rien ne sera stocké.',
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
            'title' => 'Paiement d\'une clé MetaGer',
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
            'ip' => 'Nous utilisons votre adresse IP pour estimer un bon emplacement de départ sur lequel concentrer la carte dans un premier temps. Pour ce faire, votre adresse IP est traitée localement. Les résultats ne sont stockés nulle part et seront immédiatement effacés après votre demande.',
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
        'membership' => [
            'title' => 'Demande d\'adhésion à SUMA-EV et gestion de l\'adhésion',
            'description' => 'Lors de votre demande d\'adhésion à SUMA-EV, et pendant toute la durée de votre adhésion, les données suivantes sont générées :',
            'contact' => 'Pour les particuliers : nom et adresse e-mail ; pour les organisations : nom de l\'organisation, taille approximative, ainsi que nom et adresse e-mail d\'une personne de contact. L\'adresse postale est facultative et, si elle est fournie, elle sert notamment à établir un reçu de don. Nous ne transmettons en aucun cas ces données à des tiers.',
            'payment' => 'Nous utilisons vos informations de paiement exclusivement pour le prélèvement récurrent de votre cotisation. La manière dont nous les traitons, les destinataires de ces informations et la durée de leur conservation sont décrites dans la rubrique « Traitement des paiements ».',
            'reduction' => 'Si vous demandez à bénéficier d\'une réduction de votre cotisation, nous traitons le justificatif que vous téléchargez (par exemple, un relevé de virement) dans le seul but de vérifier que vous remplissez les conditions requises pour bénéficier de cette réduction. Ce justificatif peut contenir des données particulièrement sensibles au sens de l\'article 9 du RGPD ; nous ne le conservons donc que pendant la durée nécessaire à cette vérification et ne le transmettons pas à des tiers.',
            'crm' => 'Pour gérer les adhésions, nous utilisons notre propre système de gestion des adhésions, également exploité par SUMA-EV. Les données mentionnées ci-dessus y sont conservées pendant toute la durée de votre adhésion, puis pendant la durée des délais de conservation prévus par la loi. Votre adhésion est également associée à une clé MetaGer, grâce à laquelle votre cotisation est créditée sous forme de crédit d\'utilisation pour MetaGer (voir « Clé MetaGer de paiement »).',
            'cookies' => 'Le formulaire de demande et le portail des membres n\'enregistrent aucun cookie lorsque vous vous contentez de les ouvrir. Ce n\'est que lorsque vous envoyez un formulaire ou que vous vous connectez au portail des membres que nous installons un cookie de session techniquement nécessaire, afin, par exemple, de vous signaler des erreurs de saisie ou de maintenir votre connexion. Ce cookie expire après deux heures d\'inactivité et est supprimé lorsque vous vous déconnectez. Si vous ouvrez le formulaire via un lien indiquant une langue différente de celle définie dans les paramètres de votre navigateur, nous enregistrons cette langue dans un cookie qui est supprimé lorsque vous fermez votre navigateur. La base juridique est l\'article 25, paragraphe 2, point 2, de la TDDDG.',
        ],
        'payments' => [
            'title' => 'Traitement des paiements',
            'description' => 'Les paiements versés à SUMA-EV – qui concernent actuellement les cotisations, mais concerneront à l\'avenir également les dons et les clés MetaGer – sont traités par notre propre système de paiement, également géré par SUMA-EV. Les données suivantes sont générées :',
            'ip' => 'Uniquement en cas de paiement par prélèvement SEPA : dans le cadre de l\'enregistrement de votre mandat de prélèvement (voir ci-dessous). Dans le cas contraire, cette information n\'est pas conservée.',
            'useragent' => 'Uniquement en cas de paiement par prélèvement SEPA : dans le cadre de l\'enregistrement de votre mandat de prélèvement (voir ci-dessous). Dans le cas contraire, cette information n\'est pas conservée.',
            'contact' => 'Nous recevons votre nom et votre adresse e-mail de la part du service pour lequel vous effectuez un paiement (par exemple, la demande d\'adhésion). Nous les utilisons pour vous envoyer des e-mails concernant votre paiement, tels que les coordonnées bancaires, les liens de paiement ou les rappels de paiement.',
            'payment' => 'En fonction du mode de paiement, du titulaire du compte et de l\'IBAN, une référence à votre compte PayPal ou à votre carte auprès de l\'opérateur concerné, ainsi que le montant, la date et le statut de chaque paiement.',
            'methods' => 'Nous traitons nous-mêmes les prélèvements SEPA et les virements bancaires ; les prélèvements sont transmis à notre banque avec les informations relatives au titulaire du compte, l\'IBAN, le montant et la référence du mandat. Lorsque vous payez via PayPal, vous saisissez vos informations directement auprès de PayPal (Europe) S.à r.l. et Cie, S.C.A. ; ce n’est qu’une fois que vous avez choisi PayPal comme moyen de paiement que la page de paiement charge le code de programme de PayPal, ce qui communique votre adresse IP à PayPal et lui permet d’installer ses propres cookies. Lorsque vous payez par carte bancaire ou via Wero, vous saisissez vos informations directement auprès de VR Payment GmbH. Nous ne recevons jamais de numéros de carte ni d’identifiants PayPal ou Wero, mais uniquement une référence qui nous permet de prélever les frais récurrents. La base juridique est l’article 6, paragraphe 1, point b) du RGPD.',
            'mandate' => 'Si vous nous donnez un mandat de prélèvement SEPA en ligne, nous conservons une trace du contenu de ce mandat (titulaire du compte, IBAN, référence du mandat, montant et fréquence, texte du mandat qui vous a été présenté), ainsi que l\'heure à laquelle il a été donné, votre adresse IP et votre agent utilisateur. Nous avons besoin de cet enregistrement pour prouver à notre banque que vous avez donné le mandat en cas de contestation d’un prélèvement ; ce n’est que dans ce cas que nous le communiquons à la banque. La base juridique est l’article 6, paragraphe 1, point b) du RGPD et notre intérêt légitime à pouvoir prouver l’existence du mandat (article 6, paragraphe 1, point f) du RGPD).',
            'cookies' => 'Les pages de paiement elles-mêmes n\'installent aucun cookie lorsque vous les ouvrez. Ce n\'est que si les informations que vous avez saisies sont incomplètes ou non valides que nous installons brièvement un cookie de session techniquement nécessaire afin de vous signaler les erreurs ; celui-ci est supprimé dès que ces erreurs ont été signalées (article 25, paragraphe 2, point 2, de la TDDDG).',
            'retention' => 'Si un paiement n\'est pas effectué parce que vous y avez renoncé, nous supprimons votre nom et votre adresse e-mail une semaine après l\'expiration du délai de paiement. Si, par exemple, une demande d\'adhésion est refusée avant qu\'un prélèvement n\'ait été effectué, nous supprimons immédiatement vos informations de paiement et l\'enregistrement de l\'autorisation de prélèvement. Toutes les autres informations de paiement et tous les enregistrements de mandat sont conservés tant que la relation de paiement existe, puis pendant la durée des délais de conservation légaux (jusqu’à 10 ans).',
        ],
    ],
    'introduction' => 'Pour une transparence maximale, nous énumérons les données que nous collectons auprès de vous et l\'usage que nous en faisons. La protection de vos données est importante pour nous et devrait l\'être pour vous aussi. <strong>Veuillez lire attentivement cette déclaration ; il en va de votre intérêt.</strong>',
    'hosting' => [
        'title' => 'Hébergement',
        'description' => 'Nos services sont administrés par nous, la SUMA-EV, et exploités sur du matériel loué à Hetzner Online GmbH.',
    ],
    'monitoring' => [
        'description' => 'Afin de garantir la fiabilité de nos services, nous utilisons l\'outil open source de suivi des erreurs GlitchTip, tant au niveau du backend que du frontend de nos applications. GlitchTip fonctionne exclusivement sur notre propre infrastructure ; aucune donnée n\'est partagée avec ni transmise à un prestataire tiers spécialisé dans l\'analyse ou la surveillance.',
        'collected' => [
            'title' => 'Quelles sont les données générées ?',
            'description' => 'Lorsqu\'une erreur survient dans notre application web ou sur nos serveurs, nous collectons automatiquement :',
            'error' => 'Une description de l\'erreur et une trace de pile permettant d\'identifier l\'emplacement concerné dans le code.',
            'useragent' => 'Type de navigateur, système d\'exploitation et type approximatif d\'appareil, déterminés à partir de votre agent utilisateur.',
            'url' => 'L\'adresse (URL) de la page ou de la fonction où l\'erreur s\'est produite.',
        ],
        'not_collected' => [
            'title' => 'Quelles sont les données qui ne sont pas générées ?',
            'description' => 'Votre adresse IP est supprimée avant même d\'être transmise à GlitchTip et n\'atteint donc jamais le système. Nous n\'associons pas les rapports d\'erreur à une personne ou à un compte utilisateur en particulier, même pas pour les utilisateurs connectés. Nous ne collectons pas non plus les mots de passe, les informations de paiement ni le contenu des formulaires ou des messages que vous envoyez via nos services.',
        ],
        'retention' => 'Nous conservons les rapports d\'erreur pendant 30 jours ; ils sont ensuite automatiquement supprimés.',
        'base' => 'La base juridique de ce traitement est notre intérêt légitime à assurer un fonctionnement fiable et sécurisé de nos services (art. 6, paragraphe 1, point f) du RGPD).',
        'title' => 'Suivi des erreurs et surveillance des applications',
    ],
];
