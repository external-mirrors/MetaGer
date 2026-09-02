<?php

/**
 * Fragen zum MetaGer-Schlüssel — /hilfe/schluessel.
 *
 * Aus dem "faq"-Zweig von pass/lang/<locale>/help.json des Keymanagers.
 */

return [
    "heading" => "Preguntes sobre la clau MetaGer",
    "faqs" => [
        [
            "summary" => "Com funciona la clau de MetaGer?",
            "description" => "Amb una clau de MetaGer cerqueu sense publicitat. Rebeu fitxes, de les quals se'n descompta una per cerca. Quan feu servir una clau de MetaGer, es desactiven totes les funcions que protegeixen MetaGer de les consultes automatitzades. Això vol dir que no veureu captcha i que la vostra adreça IP no es conservarà durant un temps limitat. Dit d'una manera senzilla: això fa MetaGer més ràpid, més fiable i més segur.",
        ],
        [
            "summary" => "Com funciona el testimoni anònim?",
            "description" => "Podeu fer servir el testimoni anònim amb la nostra extensió de navegador o amb l'aplicació. Això us permetrà cercar amb MetaGer encara amb més seguretat. Quan feu servir testimonis anònims, una part del vostre saldo es desa al vostre dispositiu en forma de contrasenyes aleatòries. Mitjançant un <a href=\":tokenlink\">procés criptogràfic complex</a>, esdevé impossible fins i tot per a nosaltres relacionar entre elles les cerques que feu, o relacionar-les amb la vostra clau.",
        ],
        [
            "summary" => "Com faig servir la clau de MetaGer?",
            "description" => "La clau de MetaGer es configura i es fa servir automàticament al navegador. Per tant, no cal que feu res més. Si voleu fer servir la clau de MetaGer en més dispositius, hi ha diverses maneres de configurar-la:",
            "steps" => [
                [
                    "heading" => "Copia l'URL",
                    "description" => "Quan sou a la pàgina de gestió de la clau de MetaGer, hi ha l'opció de copiar un URL. Amb aquest URL es poden desar en un altre dispositiu tots els paràmetres de MetaGer i també la clau.",
                ],
                [
                    "heading" => "Desa un fitxer",
                    "description" => "Quan sou a la pàgina de gestió de la clau de MetaGer, hi ha l'opció de desar un fitxer. Això desa la vostra clau de MetaGer com a fitxer. Després podeu fer servir aquest fitxer en un altre dispositiu per iniciar-hi la sessió amb la vostra clau.",
                ],
                [
                    "heading" => "Escaneja el codi QR",
                    "description" => "Alternativament, també podeu escanejar el codi QR que es mostra a la pàgina de gestió per iniciar la sessió en un altre dispositiu.",
                ],
                [
                    "heading" => "Introdueix la clau de MetaGer manualment",
                    "description" => "És clar que també podeu introduir la clau manualment en un altre dispositiu.",
                ],
            ],
        ],
        [
            "summary" => "He d'introduir la clau sovint. Què puc fer?",
            "description" => "Indiquem al vostre navegador que desi permanentment la clau un cop generada o un cop hàgiu iniciat la sessió. Segons la configuració del navegador, pot ser que l'hàgiu configurat perquè esborri periòdicament les galetes i les dades dels llocs web, cosa que evidentment també us tanca la sessió de MetaGer. Teniu les opcions següents:",
            "steps" => [
                [
                    "heading" => "Afegiu una excepció",
                    "description" => "A la configuració del Firefox podeu posar MetaGer en una llista d'excepcions perquè no se n'esborrin les galetes ni les dades del lloc, de manera que la sessió es mantingui iniciada.",
                ],
                [
                    "heading" => "Instal·leu la nostra extensió de navegador",
                    "description" => "La nostra extensió per al <a href=\"https://addons.mozilla.org/firefox/addon/metager-suche/\" target=\"_blank\" rel=\"noopener\">Firefox</a> i el <a href=\"https://chrome.google.com/webstore/detail/gjfllojpkdnjaiaokblkmjlebiagbphd\" target=\"_blank\" rel=\"noopener\">Chrome</a> pot desar la vostra configuració de cerca, inclosa la clau, sense fer servir galetes, de manera que podeu esborrar totes les dades del navegador sense que se us tanqui la sessió de MetaGer.",
                ],
                [
                    "heading" => "Inicieu la sessió sense escriure la clau de 36 caràcters",
                    "description" => "Si feu servir un gestor de contrasenyes, hi podeu desar la clau perquè la sessió s'iniciï automàticament. Alternativament, oferim un <a href=\":keylink\">URL de configuració</a> que podeu desar, per exemple, a les adreces d'interès. En obrir-lo, aquest URL us iniciarà la sessió sense haver d'escriure la clau manualment.",
                ],
            ],
        ],
        [
            "summary" => "No estic satisfet amb la clau de MetaGer. Què puc fer?",
            "description" => "En aquest cas podeu demanar el reemborsament de les fitxes no utilitzades dins dels 30 dies posteriors a la compra. Per fer-ho us cal l'identificador de pagament. Per sol·licitar el reemborsament, obriu la pàgina de gestió de la clau de MetaGer. Allà feu clic a l'element de menú «Comandes» i introduïu-hi l'identificador de pagament. Després podeu fer clic al botó «Sol·licita un reemborsament» i enviar la sol·licitud.",
        ],
        [
            "summary" => "Com puc cercar de manera totalment anònima?",
            "description" => "La vostra privadesa i el vostre anonimat són molt importants per a nosaltres. Per això oferim mètodes de pagament anònims (efectiu). També oferim l'ús de <a href=\":tokenlink\">testimonis anònims</a>, amb els quals fins i tot podeu cercar de manera verificablement anònima.",
        ],
        [
            "summary" => "Necessito una factura. Com la puc obtenir?",
            "description" => "Per a això només us cal l'identificador de pagament. Per demanar la factura, obriu la pàgina de gestió de la clau de MetaGer. Aquí feu clic a l'element de menú «Comandes» i introduïu-hi l'identificador de pagament. Ara podeu fer clic al botó «Sol·licita una factura» i iniciar la petició. Per a la factura necessitem el vostre nom complet, la vostra adreça electrònica i la vostra adreça postal.",
        ],
        [
            "summary" => "Vull recarregar la clau de MetaGer automàticament. Com es fa?",
            "description" => "Als nostres membres, la clau inclosa en l'afiliació se'ls recarrega automàticament cada mes. La quantitat de fitxes depèn de la quota d'afiliació pagada.",
        ],
        [
            "summary" => "He rebut una targeta o un enllaç amb un codi de val. Què n'he de fer?",
            "description" => "Algunes organitzacions regalen claus de MetaGer amb una quantitat fixa de fitxes mitjançant targetes promocionals o un enllaç. Obriu <a href=\":voucherlink\">la nostra pàgina de bescanvi</a>, introduïu-hi el codi imprès o escanegeu el codi QR de la targeta. Rebreu immediatament una clau de MetaGer nova amb les fitxes regalades, vàlida durant un temps limitat. Cada codi només es pot bescanviar un cop.",
        ],
    ],
    "more-questions" => "Teniu més preguntes? Feu servir el nostre <a href=\":contactlink\" target=\"_blank\">formulari de contacte</a>.",
];
