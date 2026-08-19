<?php
return [
    'head' => [
        '1' => 'Declaració de transparència',
        '2' => 'MetaGer és transparent',
        '3' => 'Què és un metacercador?',
        '4' => 'Quin avantatge té un metacercador?',
        '5' => 'Com es construeix la nostra classificació?',
        'compliance' => 'Com respon MetaGer a les peticions de les autoritats?',
    ],
    'text' => [
        '1' => 'MetaGer és transparent. El nostre <a href=":sourcecode">codi font</a> té una llicència lliure i és públicament accessible per a tothom. No desem dades dels usuaris i donem valor a la protecció de dades i la privadesa. Per això oferim accés anònim als resultats de cerca, possible gràcies a un servidor intermediari anònim i a l\'accés com a servei ocult de TOR. A més, MetaGer té una estructura organitzativa transparent, ja que està sostingut per l\'associació sense ànim de lucre <a href=":sumalink">SUMA-EV</a>, de la qual qualsevol persona es pot fer membre.',
        '2' => [
            '1' => 'Per explicar què són els metacercadors, té sentit explicar primer breument com funciona a grans trets la indexació dels cercadors habituals. Els cercadors habituals obtenen els seus resultats d\'una base de dades de pàgines web, que també s\'anomena índex. Els cercadors fan servir els anomenats «rastrejadors», que recullen pàgines web i les afegeixen a l\'índex (la base de dades). El rastrejador comença amb un conjunt de pàgines web i obre totes les pàgines que hi estan enllaçades. Aquestes s\'indexen, és a dir, s\'afegeixen a l\'índex. Després el rastrejador obre les pàgines enllaçades en aquestes pàgines i continua així.',
            '2' => 'Un metacercador combina els resultats de diversos cercadors i els torna a valorar segons els seus propis criteris. Això vol dir que el metacercador no té índex propi. Per tant, els metacercadors no fan servir rastrejadors: utilitzen l\'índex d\'altres cercadors.',
        ],
        '3' => 'Un avantatge clar dels metacercadors és que l\'usuari només necessita una sola consulta per accedir als resultats de diversos cercadors. El metacercador presenta els resultats rellevants en una llista tornada a ordenar. MetaGer no és un metacercador pur, ja que també fem servir petits índexs propis.',
        '4' => 'Prenem les classificacions dels nostres cercadors font i les ponderem. Aquestes classificacions es converteixen després en puntuacions. S\'atorguen o es resten punts addicionals per l\'aparició dels termes de cerca a l\'URL i al fragment de text, així com per l\'aparició excessiva de caràcters especials (per exemple, altres jocs de caràcters com el ciríl·lic). També fem servir una llista de bloqueig per treure pàgines concretes de la llista de resultats. Bloquegem pàgines web si hi estem legalment obligats. També ens reservem el dret de bloquejar pàgines web amb informació demostrablement incorrecta, pàgines de qualitat molt deficient i altres pàgines especialment dubtoses.',
        '5' => 'Si teniu més preguntes o dubtes, feu servir el nostre <a href=":contact">formulari de contacte</a> i pregunteu-nos el que vulgueu!',
        'compliance' => 'Atenem les peticions de les autoritats si hi estem legalment obligats i arribem a la conclusió que fer-ho no vulnera llibertats fonamentals. Ens prenem aquesta revisió molt seriosament. A més, desem les mínimes dades personals possibles per reduir el risc d\'haver-les de facilitar. A la taula següent trobareu dades sobre les peticions d\'autoritats que hem tramitat durant els darrers 5 anys. Aviat hi haurà més informació.',
    ],
    'table' => [
        'compliance' => [
            'th' => [
                'authinfocomp' => 'Peticions d\'informació ateses',
                'authblockcomp' => 'Peticions de bloqueig ateses',
            ],
        ],
    ],
    'alt' => [
        'text' => [
            '1' => 'Representació visual de dos índexs que es complementen per formar un metaíndex',
        ],
    ],
];
