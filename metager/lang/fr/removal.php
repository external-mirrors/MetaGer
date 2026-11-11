<?php
return [
    'title' => 'Faire retirer un résultat de recherche – MetaGer',
    'meta_description' => 'Comment faire retirer un résultat de recherche : droit à l’oubli, contenu obsolète, droit d’auteur. Où agir : Google, Brave et d’autres moteurs.',
    'heading' => 'Faire retirer un résultat de recherche',
    'lead' => 'MetaGer est un métamoteur de recherche : nous n’avons pas d’index propre, nous affichons ce que trouvent d’autres moteurs de recherche. Un résultat ne peut donc être réellement retiré que là d’où il vient : sur le site web et auprès des moteurs de recherche dont MetaGer affiche les résultats. Cette page vous indique où.',
    'toc' => [
        'label' => 'Sur cette page',
    ],
    'website' => [
        'heading' => 'Adressez-vous au site web lui-même',
        'text' => 'C’est le seul moyen qui agit partout. Une fois la page supprimée ou modifiée, chaque moteur de recherche la retire ou la met à jour lors de sa prochaine visite, MetaGer compris, sans que vous ayez à les solliciter un par un.',
        'tips' => [
            'La plupart des sites web indiquent une adresse de contact dans leurs mentions légales ou leur politique de confidentialité. Demandez-y de supprimer le contenu, de le corriger ou au moins de retirer votre nom.',
            'Pour les données personnelles, les sites web dans l’UE doivent répondre à une demande fondée sur l’art. 17 du RGPD (droit à l’effacement).',
            'Si vous gérez le site web vous-même : supprimez la page pour qu’elle réponde « 404 » ou « 410 », ou marquez-la « noindex ». Les moteurs de recherche la retirent alors d’eux-mêmes.',
        ],
        'sources' => 'Pour les résultats provenant de :sources, c’est le seul moyen.',
    ],
    'kind' => [
        'heading' => 'De quoi s’agit-il ?',
        'intro' => 'Si le site web ne vous aide pas, ou si vous ne pouvez pas attendre, les moteurs de recherche ont leurs propres procédures. Celle qui convient dépend de ce que vous voulez obtenir.',
        'personal_data' => [
            'summary' => 'Données personnelles : le droit à l’oubli',
            'text' => 'Un résultat pour votre nom renvoie à des informations vous concernant qui sont inexactes, obsolètes, non pertinentes ou excessives. En vertu de l’art. 17 du RGPD, vous pouvez demander à un moteur de recherche de ne plus afficher ce résultat lors des recherches sur votre nom. Le moteur met en balance votre intérêt et celui du public à accéder à l’information ; toutes les demandes n’aboutissent donc pas. Dans les fiches ci-dessous, cela figure sous « Données personnelles ».',
        ],
        'outdated' => [
            'summary' => 'Contenu obsolète',
            'text' => 'Le site web a déjà supprimé ou modifié la page, mais le résultat affiche encore l’ancien titre ou l’ancien texte. Cela se règle tout seul à la prochaine visite du moteur de recherche ; la plupart permettent aussi de demander une mise à jour anticipée. Dans les fiches ci-dessous, cela figure sous « Contenu obsolète ».',
        ],
        'legal' => [
            'summary' => 'Droit d’auteur et autres motifs juridiques',
            'text' => 'Un résultat porte atteinte à votre droit d’auteur ou à d’autres droits. Les moteurs de recherche reçoivent ces signalements via leurs propres formulaires juridiques ; le plus souvent, seul le titulaire des droits ou une personne agissant pour lui peut les envoyer. Dans les fiches ci-dessous, cela figure sous « Droit d’auteur et juridique ».',
        ],
        'illegal' => [
            'summary' => 'Contenus illicites',
            'text' => 'Représentations d’abus sexuels sur des enfants, contenus préjudiciables aux mineurs, incitation à la haine et contenus similaires : signalez-les d’abord à un service de signalement. Il agit contre le contenu lui-même, auprès de son hébergeur, et travaille avec la police. Si vous souhaitez en plus que MetaGer n’affiche plus un tel résultat, envoyez-nous uniquement son adresse (URL) sous forme de texte, par e-mail. Ne nous envoyez jamais le contenu lui-même, ni captures d’écran ni pièces jointes.',
            'hotlines' => 'Services de signalement :',
        ],
    ],
    'providers' => [
        'heading' => 'Contactez les moteurs de recherche affichés par MetaGer',
        'text' => 'MetaGer ne parcourt pas le web lui-même. Nous interrogeons les moteurs de recherche ci-dessous au moment où vous cherchez, nous affichons ce qu’ils renvoient et nous gardons leur réponse en cache pendant quelques heures. Si l’un d’eux retire un résultat, celui-ci disparaît aussi de ce que MetaGer reçoit de lui.',
        'which' => 'Le moteur de recherche d’où provient un résultat est indiqué sous ce résultat sur la page de résultats de MetaGer. S’il provient de plusieurs moteurs, contactez chacun d’eux.',
        'shows' => 'Sur MetaGer dans :',
        'kinds' => [
            'personal_data' => 'Données personnelles',
            'outdated' => 'Contenu obsolète',
            'legal' => 'Droit d’auteur et juridique',
        ],
        'no_form' => 'Pas de formulaire, contacter :name',
        'contact' => 'Contacter :name',
        'servers' => 'Les serveurs Mastodon dans lesquels TootNews cherche',
        'google' => 'MetaGer reçoit les résultats de Google par le service Serper. Ce que Google retire de son index disparaît ensuite aussi de MetaGer.',
        'brave' => 'Brave Search exploite son propre index. Les demandes concernant des données personnelles s’envoient par courriel à privacy@brave.com avec l’objet « RTBF request » ; la page liée indique ce que le courriel doit contenir.',
        'mojeek' => 'Mojeek exploite son propre index et ne modifie pas ses résultats à la main. Pour des motifs juridiques, Mojeek retire des pages sur demande ; écrivez via sa page de contact et indiquez les adresses exactes.',
        'staan' => 'Staan est l’interface de recherche d’European Search Perspective. Staan reçoit les demandes concernant des données personnelles via son propre formulaire ; remplissez chaque champ et joignez les justificatifs. Le retrait ne concerne que l’index de Staan.',
        'polias' => 'Polias exploite son propre index et n’a pas de formulaire. Écrivez à hallo@polias.eu en indiquant l’adresse de la page et le motif.',
        'pixabay' => 'Pixabay héberge lui-même les images. Utilisez le formulaire de signalement ou le bouton de signalement sur la page de l’image sur Pixabay.',
        'suma' => 'TootNews est exploité par SUMA-EV, l’association derrière MetaGer, et cherche des publications sur quelques serveurs Mastodon. Chaque résultat indique le serveur dont il provient ; demandez aux personnes qui gèrent ce serveur de supprimer ou de masquer la publication.',
        'nolm' => 'Nolm est le moteur de recherche propre à SUMA-EV, distinct de MetaGer. Nolm cherche dans l’Open Web Index. Demandez la suppression avec le formulaire de retrait de l’Open Web Index : ce qui y est supprimé disparaît aussi de Nolm.',
        'website' => 'BASE affiche ce que contiennent les dépôts qu’il interroge. Si le dépôt retire une notice, elle disparaît aussi de BASE.',
    ],
    'questions' => [
        'text' => 'D’autres questions sur le retrait d’un résultat de recherche ?',
        'link' => 'Écrivez-nous un e-mail',
    ],
    'trademarks' => [
        'text' => 'Les noms des moteurs de recherche d’autres fournisseurs sont des marques de leurs titulaires respectifs. Nous ne les citons que pour indiquer où demander un retrait.',
        'ref' => 'Note sur les marques',
    ],
];
