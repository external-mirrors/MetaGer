<?php

/**
 * Allgemeine Geschäftsbedingungen für die Token-Aufladung — /agb.
 *
 * Vertragstext, aus pass/lang/<locale>/agb.json des Keymanagers übernommen.
 * Tests\Feature\AgbTest vergleicht die gerenderte deutsche Fassung Zeile für
 * Zeile mit einem Abzug der alten Seite; jede Abweichung steht dort
 * ausgeschrieben, damit sie mit rechtlichem Blick nachlesbar bleibt. Es sind
 * drei:
 *
 *   - Der Text nennt seine eigene Fundstelle. Die stand wörtlich als
 *     "metager.de/keys/agb" im Vertrag und ist jetzt der Platzhalter :agburl.
 *   - Die Paketliste in §4 nannte 12000 Token, die es nicht zu kaufen gibt,
 *     und verschwieg die 500, die es gibt. Sie zählt jetzt genau das auf, was
 *     der Checkout verkauft — AgbTest::testTheTokenPackagesAreTheOnesThatCanBeBought
 *     vergleicht sie in allen Sprachen mit App\Landing\KeyPrice.
 *   - Weil sich der Vertragstext damit geändert hat, ist auch das "Stand:"-
 *     Datum weitergerückt.
 */

return [
    "heading" => "Condicions generals per a la recàrrega de fitxes (a la clau)",
    "date" => "Versió: agost de 2026",
    "translationNotice" => "Nota: aquesta és una traducció de les condicions generals vigents en alemany. La versió jurídicament vinculant es pot consultar <a href=\":linkGerman\">aquí</a>",
    "paragraphs" => [
        [
            "heading" => "Prestador, àmbit d'aplicació i modificacions",
            "paragraphs" => [
                "Les condicions generals següents s'apliquen a les relacions comercials entre els usuaris dels serveis dels llocs web metager.de i metager.org, en particular la recàrrega de fitxes a la clau, i l'operador SUMA-EV. A continuació, els «usuaris» de la recàrrega de fitxes o de la clau s'anomenen també «usuaris», i SUMA-EV s'anomena d'ara endavant «MetaGer».",
                "Aquestes condicions generals estan disponibles en tot moment a :agburl i es poden consultar, desar i imprimir sempre que es vulgui. Les comandes anteriors es poden consultar a l'àrea de client, a «Gestiona la clau – Comandes», introduint-hi l'identificador de pagament. Això només és possible dins dels 30 dies posteriors a la data de compra.",
                "Aquestes condicions s'apliquen exclusivament a usuaris que siguin consumidors en el sentit del § 13 del Codi civil alemany. És consumidora tota persona física que fa un negoci jurídic amb finalitats que majoritàriament no són ni comercials ni professionals per compte propi.",
                "MetaGer es reserva el dret d'ampliar o restringir el grup d'usuaris i el grup de participants amb dret a participar-hi, i es reserva a més el dret de modificar o completar aquestes condicions generals per als «usuaris» en qualsevol moment si això és necessari per garantir una tramitació senzilla o segura o per evitar-ne l'ús indegut. Les modificacions de les condicions generals s'anunciaran mitjançant la seva publicació al lloc web de MetaGer. Si l'usuari no està d'acord amb aquestes modificacions o addicions a les condicions generals, ha de manifestar la seva oposició per escrit a MetaGer en un termini de 4 setmanes. Altrament, les condicions generals modificades es consideraran acceptades i passaran a formar part efectiva del contracte.",
                "El cercador en línia metager.de, els seus llocs associats i el programari relacionat són gestionats per SUMA-EV. El domicili social de SUMA-EV és Henniesruh 28D, 30655 Hannover. SUMA-EV està representada per la junta directiva, que al seu torn està representada pel director general Dominik Hebeler. Número de registre: VR200033. Jutjat de registre: Amtsgericht Hannover.",
                "S'apliquen les dades de contacte següents:\nTelèfon: +49 511 34000070\nFax: +49 511 34001023\nFormulari de contacte: metager.de/kontakt\n*número de telèfon fix nacional.\n",
                "D'acord amb el reglament sobre resolució de litigis en línia en matèria de consum, us remetem a l'enllaç següent: http://ec.europa.eu/consumers/odr/",
            ],
        ],
        [
            "heading" => "Formalització del contracte i condicions de pagament",
            "paragraphs" => [
                "L'oferta dels diversos paquets de fitxes per part de MetaGer no constitueix una oferta contractual jurídicament vinculant, sinó només una invitació no vinculant a l'usuari perquè faci una recàrrega o una compra. En fer clic al botó «Fes el pagament» o a un text equivalent, l'usuari presenta una oferta jurídicament vinculant de formalitzar un contracte de compravenda amb MetaGer.",
                "Abans d'enviar la comanda de manera vinculant, l'usuari pot tornar al lloc web on es recullen les dades i corregir-hi els errors d'introducció, o bé cancel·lar el procés tancant el navegador després de prémer el botó «Enrere» del navegador que utilitzi i revisar-ne les dades.",
                "Els preus indicats inclouen l'IVA legal i altres components del preu. Com que es tracta d'un servei, no cal cap enviament i les fitxes es posen a disposició immediatament després de completar el procés de pagament. És possible el pagament per avançat. Si l'usuari ha triat el pagament per avançat, es compromet a pagar el preu de compra immediatament després de formalitzar el contracte.",
            ],
        ],
        [
            "heading" => "Garantia, llengua del contracte i atenció al client",
            "paragraphs" => [
                "S'apliquen les disposicions legals de garantia.",
                "La llengua del contracte és l'alemany.",
                "Hi ha un servei d'atenció al client per a preguntes, reclamacions i objeccions, disponible els dies feiners de 9:00 a 16:00 a les dades de contacte de SUMA-EV.",
            ],
        ],
        [
            "heading" => "Clau, opcions de pagament i recàrrega",
            "paragraphs" => [
                "L'usuari pot crear un compte de saldo, d'ara endavant anomenat clau, recarregar-hi saldo i adquirir així fitxes. Entre les opcions de pagament hi ha, entre d'altres, la targeta de crèdit i PayPal. També és possible el pagament en efectiu per correu postal a l'adreça de MetaGer indicada més amunt.",
                "Per fer servir una clau de MetaGer i recarregar-hi fitxes, primer cal crear la clau individual corresponent al lloc web de MetaGer.",
                "Segons el paquet seleccionat, l'usuari rep exactament les fitxes comprades per fer-ne un ús lliure (il·limitat). Hi ha les opcions de compra següents:",
                [
                    "500 fitxes: 5 euros",
                    "1000 fitxes: 10 euros",
                    "2000 fitxes: 20 euros",
                    "3000 fitxes: 30 euros",
                    "4000 fitxes: 40 euros",
                    "6000 fitxes: 60 euros",
                ],
                "Mitjançant accions de màrqueting amb tercers en el marc de campanyes amb socis i programes de fidelització, l'usuari també pot rebre claus. En aquest cas s'apliquen sempre aquestes condicions generals i, si escau, les condicions de la campanya corresponent.",
            ],
        ],
        [
            "heading" => "Validesa i ús de les fitxes",
            "paragraphs" => [
                "Cada usuari pot bescanviar les fitxes sense limitació dins de l'interval de validesa indicat. La disponibilitat de les fitxes comprades i la freqüència amb què es poden bescanviar dins d'un període determinat s'indica a la pàgina de resum de la clau.",
                "Des de la compra de les fitxes, aquestes són vàlides durant dos anys naturals. La data de validesa sempre consta al resum. Un cop passada la validesa, l'oferta també caduca.",
                "Després de comprar un paquet de fitxes, aquest es carrega directament a la clau.",
                "Totes les recàrregues, així com tot el procés des de la creació de la clau fins al bescanvi de les fitxes, són completament anònims. L'única excepció són les dades necessàries per tramitar el pagament.",
                "Com a prova de la recàrrega, MetaGer té dret a comprovar el procés de pagament.",
                "L'usuari no està obligat en cap moment a facilitar les seves dades personals en recarregar la clau. Tota la informació que hi faciliti en aquest sentit és voluntària. Ara bé, poden ser necessàries determinades dades personals per a la facturació i la tramitació del pagament. En conseqüència, l'usuari ha de facilitar tota la informació de manera veraç.",
                "Els paquets de fitxes comprats i les fitxes que en resulten en una clau de MetaGer no són transferibles. Ara bé, MetaGer permet expressament que l'usuari transfereixi la clau corresponent.",
            ],
        ],
        [
            "heading" => "Responsabilitat",
            "paragraphs" => [
                "MetaGer no es fa responsable dels danys derivats de l'ús del servei. MetaGer no garanteix ni assumeix cap responsabilitat sobre l'exactitud, la integritat, la fiabilitat, la qualitat i l'actualitat d'altres llocs als quals es pugui accedir mitjançant l'ús dels serveis.",
                "MetaGer presta un servei en línia.",
                "MetaGer ofereix voluntàriament la possibilitat de reemborsar el preu de compra de les fitxes no utilitzades, sempre que el mètode de pagament emprat per l'usuari ho permeti. Les operacions de pagament en efectiu en queden excloses. L'usuari ha de sol·licitar el reemborsament dins dels 30 dies posteriors a la finalització del procés de compra. Per fer-ho, cal introduir l'identificador de pagament corresponent a la pàgina de resum.",
                "Les fitxes que hagin caducat pel pas del temps no són reemborsables.",
                "MetaGer s'esforça sempre per mantenir les funcions disponibles al màxim possible. MetaGer no assumeix cap garantia ni responsabilitat sobre la disponibilitat d'internet o de la xarxa mòbil.",
                "MetaGer només respon per dol i negligència greu. Aquestes limitacions de responsabilitat i les anteriors no s'apliquen a la responsabilitat per danys personals, a la responsabilitat derivada de la Llei de responsabilitat pel producte ni a la responsabilitat per l'incompliment d'obligacions contractuals essencials. Són obligacions contractuals essencials aquelles absolutament necessàries per a l'execució adequada del contracte, de manera que no es posi en perill l'assoliment de la seva finalitat i en el compliment de les quals el client pot confiar habitualment. Si s'incompleix culposament una obligació contractual essencial, la responsabilitat es limita al dany típic del contracte i previsible en el moment de formalitzar-lo.",
                "Totes les limitacions i exclusions de responsabilitat s'apliquen també, en conseqüència, als representants, els directius, els òrgans i altres auxiliars i col·laboradors de MetaGer.",
                "L'usuari es compromet a no fer un ús abusiu dels serveis oferts. En particular, és abusiu facilitar dades personals de tercers amb la finalitat d'enganyar o d'obtenir avantatges.",
                "Si l'usuari té la intenció de fer servir el servei més enllà de l'àmbit domèstic habitual, ho ha de comunicar a MetaGer de manera informal, preferentment mitjançant el formulari de contacte, en començar aquest ús.",
            ],
        ],
        [
            "heading" => "Disposicions finals",
            "paragraphs" => [
                "S'aplica el dret alemany. Queda exclosa l'aplicació de la Convenció de les Nacions Unides sobre els contractes de compravenda internacional de mercaderies.",
                "Si una o diverses disposicions d'aquestes condicions generals fossin o esdevinguessin nul·les, això no afectarà la validesa de la resta de disposicions. Les parts es comprometen a substituir les disposicions nul·les o invàlides per disposicions noves que s'ajustin jurídicament al contingut econòmic de les disposicions nul·les o invàlides. El mateix s'aplica si es detecta una llacuna al contracte. Per cobrir-la, les parts es comprometen a treballar per establir en aquest contracte disposicions adequades que s'acostin al màxim al que haurien determinat segons el sentit i la finalitat d'aquest contracte si haguessin considerat aquest punt. Si no s'arriba a cap acord, s'aplicarà subsidiàriament la llei.",
            ],
        ],
    ],
];
