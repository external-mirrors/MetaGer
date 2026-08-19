<?php

return [
    "easy-help" => 'Fent clic al símbol <a title="Per a l\'ajuda fàcil, feu clic aquí" href="/hilfe/easy-language/mainpages" ><img class="easy-help-icon lm-only" src="/img/help-questionmark-icon-lm.svg"/><img class="easy-help-icon dm-only" src="/img/help-questionmark-icon-dm.svg"/></a> accedireu a una versió simplificada de l\'ajuda.',
    "backarrow" => 'Enrere',
    "result" => [
        "info" => [
            "1" => 'Tots els resultats es presenten amb el format següent:',
            "2" => 'Les opcions noves són:',
            "anonym" => '«OBRE ANÒNIMAMENT» vol dir que el resultat s\'obre sota la protecció del nostre servidor intermediari. En trobareu informació a l\'apartat <a href = "/hilfe/datensicherheit#h-proxy">Servidor intermediari anònim de MetaGer</a>.',
            "domainnewsearch" => '«Inicia una cerca nova en aquest domini»: es fa una cerca més detallada dins el domini del resultat.',
            "hideresult" => '«amaga»: permet amagar resultats d\'aquest domini. També podeu escriure aquest modificador directament després del terme de cerca i encadenar-lo; també s\'admet el comodí «*». Vegeu també la configuració per a una solució permanent.',
            "more" => '<img class="help-ellipsis-image lm-only" src="/img/ellipsis.svg" alt="Més"/> <img class="help-ellipsis-image dm-only" src="/img/ellipsis-dm.svg" alt="Més"/>: quan feu clic a <img class="help-ellipsis-image lm-only" src="/img/ellipsis.svg" alt="Més"/> <img class="help-ellipsis-image dm-only" src="/img/ellipsis-dm.svg" alt="Més"/>, obtindreu opcions noves i l\'aspecte del resultat canvia:',
            "newtab" => '«OBRE EN UNA PESTANYA NOVA» obre el resultat en una pestanya nova. Alternativament, també podeu obrir una pestanya nova amb Ctrl i clic esquerre o amb el botó central del ratolí.',
            "open" => '«OBRE»: feu clic al títol o a l\'enllaç de sota (l\'URL) per obrir el resultat a la mateixa pestanya.',
        ],
        "title" => 'Resultats <a title="Per a l\'ajuda fàcil, feu clic aquí" href="/hilfe/easy-language/mainpages#help-results" ><img class="easy-help-icon lm-only" src="/img/help-questionmark-icon-lm.svg"/><img class="easy-help-icon dm-only" src="/img/help-questionmark-icon-dm.svg"/></a>',
    ],
    "resultpage" => [
        "choice" => 'A sota hi veureu dos elements: «Filtre» i, si escau, «Configuració».',
        "filter" => 'Filtre: aquí podeu mostrar i amagar les opcions de filtre i aplicar filtres. En cada focus de cerca teniu opcions de selecció diferents. Algunes funcions només estan disponibles si feu servir una clau de MetaGer.',
        "foci" => 'Sota el camp de cerca hi ha 3 focus de cerca diferents (Web, Imatges, Notícies), cadascun associat a uns cercadors concrets.',
        "settings" => 'Configuració: aquí podeu establir opcions de cerca permanents per a la vostra cerca a MetaGer dins el focus actual. També podeu seleccionar i desseleccionar els cercadors associats al focus. La vostra configuració es desa en una galeta de text pla que no permet identificar-vos personalment. També podeu accedir a la pàgina de configuració des del menú de la cantonada superior dreta.',
        "title" => 'La pàgina de resultats <a title="Per a l\'ajuda fàcil, feu clic aquí" href="/hilfe/easy-language/mainpages#help-resultpage" ><img class="easy-help-icon lm-only" src="/img/help-questionmark-icon-lm.svg"/><img class="easy-help-icon dm-only" src="/img/help-questionmark-icon-dm.svg"/></a>',
    ],
    "searchfield" => [
        "info" => 'El camp de cerca consta de diverses parts:',
        "memberkey" => 'el símbol de la clau: aquí podeu introduir la vostra clau per fer servir la cerca sense publicitat. També hi podeu consultar el saldo de fitxes i gestionar la clau.',
        "morefunctions" => 'Trobareu funcions addicionals a l\'apartat «<a href = "/hilfe/funktionen">Funcions de cerca</a>»',
        "search" => 'la lupa: comenceu la cerca fent-hi clic o prement Retorn.',
        "slot" => 'el camp de cerca: escriviu-hi el vostre terme de cerca. No es distingeix entre majúscules i minúscules.',
        "title" => 'El camp de cerca <a title="Per a l\'ajuda fàcil, feu clic aquí" href="/hilfe/easy-language/mainpages#eh-searchfield" ><img class="easy-help-icon lm-only" src="/img/help-questionmark-icon-lm.svg"/><img class="easy-help-icon dm-only" src="/img/help-questionmark-icon-dm.svg"/></a>',
        "suggest" => [
            "title" => "Suggeriments de cerca",
            "description" => "Mentre escriviu al nostre camp de cerca podeu rebre una llista de consultes autocompletades per triar. A la configuració teniu diverses opcions per adaptar-ne el funcionament. Aquesta funció està desactivada de manera predeterminada.",
            "provider" => "<b>Proveïdor:</b> seleccioneu el proveïdor de suggeriments de cerca que voleu que consulti MetaGer. Els proveïdors tenen costos diferents. També podeu desactivar completament els suggeriments de cerca.",
            "delay" => "<b>Retard:</b> qualsevol altra petició que arribi dins l'interval seleccionat cancel·larà totes les peticions anteriors pendents. Les peticions cancel·lades no tenen cap cost i només veureu els suggeriments de l'última petició. Podeu triar entre Curt (:short), Mitjà (:medium) i Llarg (:long)",
            "addressbar" => "<b>Barra d'adreces:</b> si MetaGer és el cercador predeterminat del vostre navegador, pot demanar suggeriments de cerca mentre escriviu a la barra d'adreces. Si aquesta opció està activada, MetaGer respondrà aquestes peticions amb suggeriments. Per com implementen els navegadors aquest mecanisme, hem de desar la vostra configuració de suggeriments als nostres servidors juntament amb un identificador de dispositiu pseudonimitzat temporal. Per això aquesta funció està desactivada de manera predeterminada.",
        ],
    ],
    "settings" => [
        "1" => 'Cerca sense publicitat <br> Aquí podeu consultar el saldo de la vostra clau i la clau mateixa. També teniu l\'opció de recarregar-la o eliminar-la.',
        "2" => 'Cercadors utilitzats <br> Aquí podeu consultar i ajustar els cercadors que feu servir. Fent clic al nom corresponent, els podeu activar o desactivar.',
        "3" => 'Filtres de cerca <br> Els filtres de cerca us permeten filtrar la cerca de manera permanent.',
        "4" => 'Llista negra <br> Aquí podeu crear una llista negra personal. La podeu fer servir per filtrar dominis concrets i crear la vostra pròpia configuració de cerca. Fent clic a «Afegeix», aquesta configuració s\'afegirà a l\'enllaç de l\'apartat «Nota».',
        "5" => 'Canvia al mode fosc <br> Aquí podeu passar fàcilment al mode fosc.',
        "6" => 'Obre els resultats en una pestanya nova <br> Aquí podeu activar permanentment la funció d\'obrir els resultats en una pestanya nova.',
        "7" => 'Citacions <br> Aquí podeu activar i desactivar la visualització de citacions.',
        "8" => 'Restaura tota la configuració actual <br> Es mostrarà un enllaç que podeu establir com a pàgina d\'inici o desar a les adreces d\'interès per conservar la configuració que teniu ara.',
        '9' => 'Publicitat discreta del nostre propi servei <br> Us mostrem publicitat discreta dels nostres propis serveis. Aquí podeu desactivar la nostra autopromoció.',
        "title" => 'Configuració <a title="Per a l\'ajuda fàcil, feu clic aquí" href="/hilfe/easy-language/mainpages#help-settings" ><img class="easy-help-icon lm-only" src="/img/help-questionmark-icon-lm.svg"/><img class="easy-help-icon dm-only" src="/img/help-questionmark-icon-dm.svg"/></a>',
    ],
    "startpage" => [
        "info" => 'La pàgina d\'inici inclou el camp de cerca, un botó a la cantonada superior dreta per accedir al menú i dos enllaços sota el camp de cerca per afegir MetaGer al navegador i cercar sense publicitat. A la part inferior hi trobareu informació sobre MetaGer i l\'associació SUMA-EV. A més, a baix de tot es mostren els nostres eixos: <i>Privadesa garantida, Associació sense ànim de lucre, Divers i lliure</i> i <i>100 % energia verda</i>. Fent clic als apartats corresponents o desplaçant-vos hi trobareu més informació. ',
        "title" => 'La pàgina d\'inici <a title="Per a l\'ajuda fàcil, feu clic aquí" href="/hilfe/easy-language/mainpages#help-startpage" ><img class="easy-help-icon lm-only" src="/img/help-questionmark-icon-lm.svg"/><img class="easy-help-icon dm-only" src="/img/help-questionmark-icon-dm.svg"/></a>',
    ],
    "title" => [
        "1" => 'MetaGer - Ajuda',
        "2" => 'Ús de les pàgines principals',
    ],
];
