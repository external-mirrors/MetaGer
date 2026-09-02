<?php
return [
    'skip' => [
        'search' => 'Ves al camp de cerca',
        'navigation' => 'Ves a la navegació',
        'fokus' => 'Ves a la selecció del focus de cerca',
    ],
    'lang' => 'canvia la llengua',
    'plugin' => 'Instal·la MetaGer',
    'plugin-title' => 'Afegeix MetaGer al teu navegador',
    'key' => [
        'placeholder' => 'Introduïu la vostra clau de MetaGer per començar a cercar.',
        'tooltip' => [
            'nokey' => 'Configura la cerca sense publicitat',
            'empty' => 'Fitxes exhaurides. Recarregueu-ne ara.',
            'low' => 'Fitxes gairebé exhaurides. Recarregueu-ne ara.',
            'full' => 'Cerca sense publicitat activada.',
        ],
    ],
    'placeholder' => 'MetaGer: cerca i troba amb la privadesa protegida',
    'searchbutton' => 'Inicia la cerca amb MetaGer',
    'searchreset' => 'esborra el text de cerca',
    'foki' => [
        'web' => 'Web',
        'bilder' => 'Imatges',
        'nachrichten' => 'Notícies',
        'science' => 'Ciència',
        'produkte' => 'Productes',
        'maps' => 'Mapes'
    ],
    'adfree' => 'MetaGer sense publicitat',
    'searchbar-replacement' => [
        'tagline' => 'Codi obert. Sense publicitat. Anònim.',
        'message' => 'La clau és el vostre accés: sense compte, sense adreça electrònica. Només el saldo hi va lligat.',
        'have_key' => 'Inicia la sessió amb la meva clau',
        'first_time' => 'És el primer cop que hi sou?',
        'start' => 'Configura una clau',
        // Swapped in by resources/js/accountBreadcrumb.js when this browser has
        // rendered a signed-in page before. Three strings, replacing three
        // elements in place — nothing appears, nothing moves.
        'welcome_back' => 'Ben tornat.',
        'welcome_back_message' => "En aquest dispositiu ja hi havíeu iniciat la sessió. Torneu-hi amb la mateixa clau: el saldo encara hi és.",
        'welcome_back_button' => 'Torna a iniciar la sessió',
        'new_key' => 'Encara no teniu clau?',
        'extension' => 'Mantingueu la sessió iniciada i l\'anonimat amb la nostra extensió web',
        "key_error" => "La clau introduïda no és vàlida. Reviseu el que heu escrit.",
        "login_code_error" => "El codi d'inici de sessió introduït no és vàlid. Consell: els codis d'inici de sessió només són vàlids mentre es mostren en un altre dispositiu.",
        "payment_id_error" => "Heu introduït un identificador de pagament que no és una clau correcta. La vostra clau té 36 caràcters.",
        "login" => "Inicia la sessió",
    ],
    // The landing page shown to a visitor without a key: hero, "how it works",
    // and the five benefit cards. It came from the keymanager's own root page
    // (pass/views/index.ejs, pass/lang/*/index.json), which /keys used to serve
    // and which now redirects here.
    //
    // Placeholders are Laravel's :name, not i18next's {{name}}, and the links
    // are passed in from parts/landing/* so the locale prefix and the /keys
    // paths stay in one place.
    'landing' => [
        'title' => 'Cerqueu i navegueu pel web sense que us vigilin',
        'description' => 'MetaGer respecta la vostra privadesa i us permet navegar per qualsevol lloc web de manera anònima.',
        'advantages' => [
            'ads' => 'Sense publicitat',
            'tracking' => 'Sense rastreig',
            'logging' => 'Sense registres',
            'compromise' => 'Sense concessions',
        ],
        'calltoaction' => 'Com funciona',
        'benefits' => [
            'browsing' => [
                'heading' => 'No només cerca anònima: també navegació anònima',
                'description' => 'Amb la vostra clau de MetaGer també podeu obrir qualsevol lloc web en un navegador privat que funciona amb seguretat als nostres servidors, no al vostre dispositiu. Els llocs web no poden saber qui sou ni des d\'on navegueu, i tot s\'esborra automàticament quan acaba la sessió. Sense instal·lació ni configuració: només cal obrir-lo i endavant.',
                'fingerprinting' => 'Empremtes digitals',
                'tracking' => 'Rastreig',
            ],
            'ads' => [
                'heading' => 'Sense publicitat',
                'description' => 'La publicitat i la privadesa rarament casen. Per això a MetaGer no hi ha absolutament gens de publicitat, de manera que podem protegir la vostra privadesa sense concessions.',
                'ads' => 'Publicitat',
                'tracking' => 'Enllaços de rastreig',
            ],
            'logging' => [
                'heading' => 'Sense registres',
                'description' => 'Cercar a internet acostuma a deixar un rastre de dades. Nosaltres no necessitem conservar-ne cap: el nostre cercador està fet de manera que combatre el correu brossa no requereixi registres. Tampoc no us trobareu ni un sol captcha al nostre lloc, ni tan sols si feu servir una VPN.',
                'logging' => 'Registres',
            ],
            'compromise' => [
                'heading' => 'Sense concessions',
                'description' => 'En lloc d\'un compte lligat a les vostres dades personals, simplement obteniu una clau generada a l\'atzar, sense cap nom ni adreça electrònica. Podeu triar entre diversos <a href=":linkPaymentMethods">mètodes de pagament</a>, inclòs el pagament en efectiu, totalment anònim. Amb la nostra <a href=":linkApp">aplicació per a Android</a> o l\'extensió de navegador, fins i tot podeu demostrar que les vostres cerques són anònimes gràcies als <a href=":linkToken">testimonis anònims</a>.',
                'compromise' => 'Dades personals',
            ],
            'efficiency' => [
                'heading' => 'Cerqueu de manera més eficient',
                'description' => 'Trobeu més de pressa el que busqueu. Quan és útil, afegim enllaços directes clars, notícies rellevants i vídeos dins dels resultats de cerca. La nostra cerca d\'imatges també es nodreix de fonts addicionals.',
            ],
        ],
        'howitworks' => [
            'heading' => 'Com funciona',
            'steps' => [
                [
                    'heading' => 'La clau es genera automàticament',
                    'description' => 'La vostra clau de MetaGer es genera automàticament. Sense registre ni dades personals. És l\'única cosa que necessiteu per fer servir MetaGer.',
                ],
                [
                    'heading' => 'Activeu el vostre accés',
                    'description' => 'Un <a href=":linkCost">pagament</a> únic afegeix saldo a la vostra clau, que anomenem fitxes. Això activa la cerca sense publicitat ni rastreig i la navegació anònima, a més de totes les funcions actuals i futures de MetaGer. Unes 500 fitxes (5 €) solen durar uns 2 mesos.',
                    'membership' => 'Nota: els membres de la nostra associació sense ànim de lucre <a href="https://suma-ev.de" target="_blank">SUMA-EV</a> poden fer servir MetaGer sense cap cost addicional. <a href=":linkMembership" target="_blank">Feu-vos-en membre ara</a>',
                ],
                [
                    'heading' => 'Feu servir MetaGer arreu',
                    'description' => 'Feu servir la mateixa clau en tants dispositius com vulgueu, o compartiu-la amb amics i família. Només cal que obriu MetaGer en qualsevol dispositiu, hi introduïu la clau i ja podreu cercar, o navegar anònimament.',
                ],
            ],
            'start' => 'Comença',
            'login' => 'Ja tinc una clau',
        ],
    ],
];
