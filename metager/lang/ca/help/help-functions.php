<?php

return [
    "urls" => [
        'title' => 'Excloure URL',
        'explanation' => 'Podeu excloure resultats de cerca que continguin paraules concretes en el seu enllaç fent servir «-url:» a la consulta.',
        'example_b' => '<i>la meva cerca</i> -url:gos',
        'example_a' => 'Exemple: voleu excloure els resultats en què la paraula «gos» aparegui a l\'enllaç del resultat:',
    ],
    'title' => 'MetaGer - Ajuda',
    "selist" => [
        'title' => 'Afegir MetaGer a la llista de cercadors del navegador <a title="Per a l\'ajuda fàcil, feu clic aquí" href="/hilfe/easy-language/functions#eh-selist"><img class="easy-help-icon lm-only" src="/img/help-questionmark-icon-lm.svg"/><img class="easy-help-icon dm-only" src="/img/help-questionmark-icon-dm.svg"/></a>',
        'explanation_b' => 'Alguns navegadors demanen que introduïu un URL; ha de ser «https://metager.de/meta/meta.ger3?input=%s», sense cometes. Podeu generar l\'URL vosaltres mateixos cercant alguna cosa a metager.de i substituint després el que hi ha darrere d\'«input=» a la barra d\'adreces per %s. Si tot i així teniu problemes, poseu-vos en contacte amb nosaltres: <a href="/kontalt" target="_blank" rel="noopener">formulari de contacte</a>',
        'explanation_a' => 'Proveu primer d\'instal·lar el complement actual. Per instal·lar-lo, només cal que feu clic a l\'enllaç que hi ha just sota el camp de cerca. El vostre navegador ja hi hauria d\'haver estat detectat.',
    ],

    "searchfunction" => [
        "title" => "Funcions de cerca"
    ],
    "stopwords" => [
        "title" => 'Paraules d\'exclusió <a title="Per a l\'ajuda fàcil, feu clic aquí" href="/hilfe/easy-language/functions#eh-stopwordsearch"><img class="easy-help-icon lm-only" src="/img/help-questionmark-icon-lm.svg"/><img class="easy-help-icon dm-only" src="/img/help-questionmark-icon-dm.svg"/></a>',
        "3" => "cotxe nou -bmw",
        "2" => "Exemple: busqueu un cotxe nou, però en cap cas un BMW. La vostra consulta seria:",
        "1" => "Si voleu excloure de MetaGer els resultats que continguin paraules concretes (paraules d'exclusió), podeu fer-ho posant un signe menys davant d'aquestes paraules.",
    ],
    "key"    => [
        "title" => 'Afegir la clau de MetaGer <a title="Per a l\'ajuda fàcil, feu clic aquí" href="/hilfe/easy-language/functions#eh-keyexplain"><img class="easy-help-icon lm-only" src="/img/help-questionmark-icon-lm.svg"/><img class="easy-help-icon dm-only" src="/img/help-questionmark-icon-dm.svg"/></a>',
        "1" => 'La clau de MetaGer es configura i es fa servir automàticament al vostre navegador. No cal que feu res més. Si voleu fer servir la clau de MetaGer en altres dispositius, hi ha diverses maneres de configurar-la:',
        "2"=>'Codi d\'inici de sessió <br>A la <a href = "/keys/key/enter">pàgina de gestió</a> de la clau de MetaGer podeu fer servir el codi d\'inici de sessió per afegir la clau a un altre dispositiu. Només cal que introduïu el codi numèric de sis xifres en iniciar la sessió. El codi d\'inici de sessió només es pot fer servir un cop i només és vàlid mentre la finestra estigui oberta.',
        "3"=>'Copia l\'URL <br>Quan sou a la <a href = "/keys/key/enter">pàgina de gestió</a> de la clau de MetaGer, hi ha l\'opció de copiar un URL. Aquest URL es pot fer servir per desar tota la configuració de MetaGer, inclosa la clau, en un altre dispositiu.',
        '4'=>'Desa un fitxer <br>Quan sou a la <a href = "/keys/key/enter">pàgina de gestió</a> de la clau de MetaGer, hi ha l\'opció de desar un fitxer. Això desa la vostra clau de MetaGer com a fitxer. Després podeu fer servir aquest fitxer en un altre dispositiu per iniciar la sessió amb la vostra clau.',
        '5'=>'Escaneja el codi QR <br>Alternativament, també podeu escanejar el codi QR que es mostra a la <a href = "/keys/key/enter">pàgina de gestió</a> per iniciar la sessió amb un altre dispositiu.',
        '6'=>'Introdueix la clau de MetaGer manualment <br>També podeu introduir la clau manualment en un altre dispositiu.',
        'colors'=> [
            'title'=>'La clau de MetaGer en colors',
            '1'=>'Perquè pugueu reconèixer fàcilment si esteu cercant sense publicitat, hem donat colors al símbol de la clau. A continuació trobareu què vol dir cada color:',
            'grey'=>'Gris: no heu configurat cap clau. Feu servir la cerca gratuïta.',
            'red'=>'Vermell: si el símbol de la clau és vermell, vol dir que la clau és buida. Heu esgotat totes les cerques sense publicitat. Podeu recarregar la clau a la pàgina de gestió de claus.',
            'green'=>'Verd: si el símbol de la clau és verd, vol dir que feu servir una clau amb saldo.',
            'yellow'=>'Groc: si veieu la clau groga, us queda un saldo de 30 fitxes. Les vostres cerques s\'estan acabant. És recomanable recarregar la clau aviat.',
        ],
    ],
    "multiwordsearch" => [
        "title" => 'Cerca de diverses paraules <a title="Per a l\'ajuda fàcil, feu clic aquí" href="/hilfe/easy-language/functions#eh-severalwords"><img class="easy-help-icon lm-only" src="/img/help-questionmark-icon-lm.svg"/><img class="easy-help-icon dm-only" src="/img/help-questionmark-icon-dm.svg"/></a>',
        "4" => [
            "example" => '"la taula rodona"',
            "text" => "Amb una cerca de frase podeu cercar combinacions de paraules en lloc de paraules soltes. Només cal que poseu entre cometes les paraules que han d'aparèixer juntes.",
        ],
        "3" => [
            "example" => '"la" "taula" "rodona"',
            "text" => "Si voleu assegurar-vos que les paraules de la vostra consulta també apareguin als resultats, les heu de posar entre cometes.",
        ],
        "2" => "Si això no us basta, teniu 2 opcions per fer la cerca més precisa:",
        "1" => "Quan cerqueu més d'una paraula a MetaGer, intentem automàticament oferir-vos resultats en què apareguin totes les paraules o que s'hi acostin al màxim.",
    ],
    "exactsearch" =>[
        "title" => 'Cerca exacta <a title="Per a l\'ajuda fàcil, feu clic aquí" href="/hilfe/easy-language/functions#exactsearch"><img class="easy-help-icon lm-only" src="/img/help-questionmark-icon-lm.svg"/><img class="easy-help-icon dm-only" src="/img/help-questionmark-icon-dm.svg"/></a>',
        "1" =>"Si voleu trobar una paraula concreta als resultats de MetaGer, podeu posar-hi un signe més al davant. Si feu servir el signe més i cometes, es cerca la frase exactament tal com l'heu escrita.",
        "2" =>"Exemple: ",
        "3" =>'Exemple: ',
        "example" => [
            "1" => "+paraulaexemple",
            "2" => '+"frase d\'exemple"',
        ],
    ],
    "bang"  => [
        "title" => '!bangs <a title="Per a l\'ajuda fàcil, feu clic aquí" href="/hilfe/easy-language/functions#eh-bangs"><img class="easy-help-icon lm-only" src="/img/help-questionmark-icon-lm.svg"/><img class="easy-help-icon dm-only" src="/img/help-questionmark-icon-dm.svg"/></a>',
        "1" => "MetaGer admet, en certa mesura, una manera d'escriure sovint anomenada sintaxi «!bang».<br>Un «!bang» comença sempre amb un signe d'admiració i no conté espais. Alguns exemples són «!twitter» o «!facebook».<br>Quan feu servir un !bang admès a la consulta, apareix una entrada als nostres consells ràpids que us permet continuar la cerca amb el servei corresponent (Twitter o Facebook) amb un sol clic.",
        "2" => 'Per què els !bangs no s\'obren directament?',
        "3" => 'Les «redireccions» dels !bangs formen part dels nostres consells ràpids i requereixen un clic addicional. Va ser una decisió difícil per a nosaltres, perquè fa els !bangs menys útils. Tanmateix, malauradament és necessari, perquè els enllaços cap als quals es redirigeix no són nostres, sinó d\'un tercer, DuckDuckGo.<p>Sempre ens assegurem que els nostres usuaris mantinguin el control en tot moment. Per això protegim de dues maneres: primer, el terme de cerca introduït no es transmet mai a DuckDuckGo, només el !bang. Segon, l\'usuari confirma explícitament la visita a la destinació del !bang. Per motius de personal, malauradament ara mateix no podem comprovar ni mantenir tots aquests !bangs nosaltres mateixos.',
    ],
    "backarrow" => 'Enrere',
    "easy-help"=> 'Fent clic al símbol <a title="Per a l\'ajuda fàcil, feu clic aquí" href="/hilfe/easy-language/functions"><img class="easy-help-icon lm-only" src="/img/help-questionmark-icon-lm.svg"/><img class="easy-help-icon dm-only" src="/img/help-questionmark-icon-dm.svg"/></a> accedireu a una versió simplificada de l\'ajuda.',
];
