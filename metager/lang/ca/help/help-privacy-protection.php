<?php

return [
    "title" => 'MetaGer - Ajuda',
    "backarrow" => 'Enrere',
    "easy-help" => 'Fent clic al símbol <a title="Per a l\'ajuda fàcil, feu clic aquí" href="/hilfe/easy-language/privacy-protection" ><img class="easy-help-icon lm-only" src="/img/help-questionmark-icon-lm.svg"/><img class="easy-help-icon dm-only" src="/img/help-questionmark-icon-dm.svg"/></a> accedireu a una versió simplificada de l\'ajuda.',

    "privacy" => [
        "title" => "Anonimat i seguretat de les dades",
        "1" => 'Galetes de rastreig, identificadors de sessió i adreces IP <a title="Per a l\'ajuda fàcil, feu clic aquí" href="/hilfe/easy-language/privacy-protection#eh-tracking" ><img class="easy-help-icon lm-only" src="/img/help-questionmark-icon-lm.svg"/><img class="easy-help-icon dm-only" src="/img/help-questionmark-icon-dm.svg"/></a>',
        "2" => 'A MetaGer no en fem servir, no en desem, no en conservem ni en tractem cap (excepció: emmagatzematge de curta durada per protegir-nos d\'atacs de pirateria i de bots). Com que considerem aquest tema extremament important, també hem creat maneres d\'ajudar-vos a assolir el màxim nivell de seguretat: el servei ocult de TOR de MetaGer i el nostre navegador anònim.',
        "3" => "A sota en trobareu més informació. Les funcions són accessibles des de «Serveis», a la barra de navegació.",
    ],

    "tor" => [
        "title" => 'Servei ocult de TOR <a title="Per a l\'ajuda fàcil, feu clic aquí" href="/hilfe/easy-language/privacy-protection#eh-torhidden" ><img class="easy-help-icon lm-only" src="/img/help-questionmark-icon-lm.svg"/><img class="easy-help-icon dm-only" src="/img/help-questionmark-icon-dm.svg"/></a>',
        "1" => "Fa molts anys que MetaGer amaga les adreces IP i no les desa. Tanmateix, aquestes adreces són visibles temporalment al servidor de MetaGer mentre s'executa una cerca: si MetaGer es veiés compromès, un atacant podria llegir-les i desar-les. Per complir els requisits de seguretat més alts, gestionem una instància de MetaGer a la xarxa Tor: el servei ocult de TOR de MetaGer, accessible a través de: <a href=\"/tor/\" target=\"_blank\" rel=\"noopener\">https://metager.de/tor/</a>. Per fer-lo servir necessiteu un navegador especial, que podeu baixar de <a href=\"https://www.torproject.org/\" target=\"_blank\" rel=\"noopener\">https://www.torproject.org/</a>.",
        "2" => "Podeu accedir a MetaGer des del navegador Tor a: http://metagerv65pwclop2rsfzg4jwowpavpwd6grhhlvdgsswvo6ii4akgyd.onion .",
    ],

    'proxy' => [
        'title' => 'Obre anònimament (SafeBrowse) <a title="Per a l\\\\\\\'ajuda fàcil, feu clic aquí" href="/hilfe/easy-language/privacy-protection#eh-proxy" ><img class="easy-help-icon lm-only" src="/img/help-questionmark-icon-lm.svg"/><img class="easy-help-icon dm-only" src="/img/help-questionmark-icon-dm.svg"/></a>',
        'intro' => 'Amb «Obre anònimament» visiteu un lloc web sense que aquest vegi mai el vostre dispositiu. La pàgina s’obre en un navegador que funciona als nostres servidors: SafeBrowse. El lloc web només tracta amb aquest navegador i no coneix ni la vostra adreça IP ni res del vostre dispositiu, del vostre navegador o de les vostres galetes.',
        'use' => [
            'title' => 'Com obrir una pàgina anònimament',
            'text' => [
                'Feu clic a «Obre anònimament» (la icona de l’escut) en un resultat. La pàgina s’obre en una pestanya nova.',
                'Si voleu obrir tots els resultats anònimament, activeu «Obre els resultats de manera anònima» a la configuració. Aleshores cada enllaç d’un resultat passa per SafeBrowse, i amb «Obre directament» visiteu una pàgina sense SafeBrowse.',
            ],
        ],
        'session' => [
            'title' => 'La sessió',
            'text' => [
                'El primer resultat que obriu anònimament inicia una sessió. Cadascun dels següents s’obre en la mateixa sessió, en una pestanya pròpia. Allà hi navegueu com sempre: seguiu enllaços, empleneu formularis, escriviu una adreça nova o un terme de cerca a la barra d’adreces. Continueu protegits mentre ho feu.',
                'Una sessió costa fitxes de la vostra clau de MetaGer; quantes i per quant de temps es mostra abans de començar. Mentre feu servir la sessió, s’allarga automàticament. Quan deixeu de fer-la servir, s’acaba sola poc després, o la tanqueu amb «Tanca la sessió».',
                'Quan la sessió s’acaba, el navegador dels nostres servidors s’esborra amb tot el que hi havia: pàgines visitades, galetes, inicis de sessió i fitxers baixats. La sessió següent comença amb un navegador nou i buit. Per això, deseu abans els fitxers baixats al vostre dispositiu des de «Baixades».',
            ],
        ],
        'views' => [
            'title' => 'Dues vistes',
            'text' => [
                '«Pàgina» mostra una còpia del lloc web sense els seus scripts. Hi llegiu, desplaceu, cerqueu i copieu com a qualsevol pàgina; els vostres clics i el que escriviu van al navegador dels nostres servidors. «Navegador complet» mostra aquest navegador com a imatge, amb totes les seves pestanyes. Allà funciona tot, també els vídeos i les pàgines que «Pàgina» no pot mostrar. Canvieu entre totes dues a la barra de dalt.',
            ],
        ],
        'nojs' => [
            'title' => 'Sense JavaScript',
            'text' => [
                'Encara que tingueu el JavaScript desactivat al navegador, «Obre anònimament» obre la pàgina a SafeBrowse. Aleshores veieu una còpia de la pàgina en què funcionen els enllaços, els botons i els formularis. Es torna a carregar després de cada clic, però no canvia sola. Els vídeos, els diàlegs de la pàgina i la pujada de fitxers no funcionen d’aquesta manera. Si el vostre navegador bloqueja les galetes, els resultats que obriu des d’una pàgina de resultats continuen anant a la mateixa sessió, també si seguiu cercant des d’allà.',
            ],
        ],
        'limits' => [
            'title' => 'El que «Obre anònimament» no fa',
            'text' => [
                'El lloc web no us reconeix pel vostre dispositiu, però sí pel que li dieu vosaltres: si inicieu la sessió o hi introduïu dades personals, sap qui sou. Si tampoc no voleu mostrar-vos a MetaGer, feu servir MetaGer a través de Tor (vegeu més amunt).',
            ],
        ],
    ],

    "maps" => [
        "title" => "MetaGer Maps",
        "1" => 'Preservar la privadesa en l\'era dels gegants globals de les dades també ens ha portat a desenvolupar <a href="https://maps.metager.de" target="_blank">https://maps.metager.de</a>: el (que sapiguem) únic planificador de rutes que ofereix funcionalitat completa des del navegador i des d\'una aplicació sense desar les ubicacions dels usuaris. Tot això és verificable perquè el nostre programari és de codi obert. Per fer servir maps.metager.de us recomanem la nostra versió d\'aplicació, més ràpida. Podeu baixar les nostres aplicacions des d\'<a href="/app" target="_blank">aquí</a> (o, és clar, també des de la Play Store).',
        "2" => "També podeu accedir a aquesta funció de mapes des de la cerca de MetaGer (i a l'inrevés). Un cop hàgiu cercat un terme a MetaGer, veureu un nou focus de cerca «Mapes» a la cantonada superior dreta. Si hi feu clic, arribareu al mapa corresponent.",
        "3" => "En carregar-se, el mapa mostra els punts (POI = punts d'interès) trobats per MetaGer, que també apareixen a la columna dreta. En fer zoom, aquesta llista s'adapta a la part del mapa visible. Si poseu el ratolí damunt d'un marcador del mapa o de la llista, es destaca l'element corresponent. Feu clic a «Detalls» per obtenir més informació sobre aquest punt a la base de dades de sota.",
    ],

    "content" => [
        'title' => 'Contingut qüestionable / protecció de menors <a title="Per a l\'ajuda fàcil, feu clic aquí" href="/help/easy-language/privacy-protection#eh-content" ><img class="easy-help-icon lm-only" src="/img/help-questionmark-icon-lm.svg"/><img class="easy-help-icon dm-only" src="/img/help-questionmark-icon-dm.svg"/></a>',
        "explanation" => [
            '1' => 'He obtingut «resultats» que no només trobo molestos, sinó que, al meu parer, contenen contingut il·legal!',
            '2' => 'Si trobeu a internet alguna cosa que considereu il·legal o perjudicial per als menors, podeu escriure a <a href="mailto:hotline@jugendschutz.net" target="_blank" rel="noopener">hotline@jugendschutz.net</a> per correu electrònic o visitar <a href="http://www.jugendschutz.net/" target="_blank" rel="noopener">www.jugendschutz.net</a> i emplenar el formulari de denúncia que hi trobareu. És útil que hi afegiu una nota breu sobre què considereu inadmissible i com heu arribat a aquest contingut. També ens podeu comunicar el contingut qüestionable directament a nosaltres. Per fer-ho, escriviu un correu a la nostra responsable de protecció de menors (<a href="mailto:jugendschutz@metager.de" target="_blank" rel="noopener">jugendschutz@metager.de</a>).',
        ],
    ],
];
