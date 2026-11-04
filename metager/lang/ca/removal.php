<?php
return [
    'title' => 'Eliminar un resultat de cerca – MetaGer',
    'heading' => 'Eliminar un resultat de cerca',
    'lead' => 'MetaGer és un metacercador: no té cap índex propi, sinó que mostra el que troben altres cercadors. Per això un resultat només es pot eliminar de debò allà d’on ve: al lloc web i als cercadors els resultats dels quals mostra MetaGer. Aquesta pàgina us diu on.',
    'toc' => [
        'label' => 'En aquesta pàgina',
    ],
    'website' => [
        'heading' => 'Adreceu-vos al mateix lloc web',
        'text' => 'És l’únic pas que funciona a tot arreu. Un cop la pàgina s’ha esborrat o canviat, tots els cercadors la treuen o l’actualitzen la propera vegada que la visiten, i MetaGer amb ells, sense que calgui demanar-ho a cadascun.',
        'tips' => [
            'La majoria de llocs web indiquen un contacte a l’avís legal o a la política de privadesa. Demaneu-los que esborrin el contingut, que el corregeixin o, almenys, que hi treguin el vostre nom.',
            'Pel que fa a dades personals, els llocs web de la UE han de respondre una sol·licitud segons l’art. 17 del RGPD (dret de supressió).',
            'Si el lloc web és vostre: esborreu la pàgina perquè respongui amb «404» o «410», o marqueu-la amb «noindex». Els cercadors la retiraran per si sols.',
        ],
        'sources' => 'Per als resultats de :sources és l’única via.',
    ],
    'kind' => [
        'heading' => 'De quin tipus de sol·licitud es tracta?',
        'intro' => 'Si el lloc web no us ajuda, o no podeu esperar, els cercadors tenen els seus propis procediments. Quin s’aplica depèn del que vulgueu aconseguir.',
        'personal_data' => [
            'summary' => 'Dades personals: el dret a l’oblit',
            'text' => 'Un resultat per al vostre nom enllaça a informació sobre vosaltres que és inexacta, obsoleta, irrellevant o excessiva. Segons l’art. 17 del RGPD podeu demanar a un cercador que deixi de mostrar aquest resultat en les cerques del vostre nom. El cercador pondera el vostre interès amb el de la societat a accedir a la informació, de manera que no totes les sol·licituds prosperen. A les targetes de sota, cerqueu «Dades personals».',
        ],
        'outdated' => [
            'summary' => 'Contingut obsolet',
            'text' => 'El lloc web ja ha esborrat o canviat la pàgina, però el resultat encara mostra el títol o el text antics. Això es corregeix sol la propera vegada que el cercador visita la pàgina; la majoria també permeten demanar una actualització anticipada. A les targetes de sota, cerqueu «Contingut obsolet».',
        ],
        'legal' => [
            'summary' => 'Drets d’autor i altres motius legals',
            'text' => 'Un resultat infringeix els vostres drets d’autor o altres drets. Els cercadors accepten aquests avisos amb els seus propis formularis legals; normalment només els pot enviar el titular dels drets o algú en nom seu. A les targetes de sota, cerqueu «Drets d’autor i legal».',
        ],
        'illegal' => [
            'summary' => 'Contingut il·legal',
            'text' => 'Representacions d’abús sexual infantil, continguts perjudicials per a menors, incitació a l’odi i similars: denuncieu-los primer a una línia de denúncia. Actua contra el contingut mateix, al seu allotjament, i col·labora amb la policia. Si a més voleu que MetaGer deixi de mostrar aquest resultat, envieu-nos només la seva adreça (URL) com a text. No ens envieu mai el contingut mateix, ni captures de pantalla ni fitxers adjunts.',
            'hotlines' => 'Línies de denúncia:',
        ],
    ],
    'providers' => [
        'heading' => 'Pregunteu als cercadors que mostra MetaGer',
        'text' => 'MetaGer no cerca el web pel seu compte. Consulta els cercadors de sota en el moment de la vostra cerca, mostra el que retornen i en desa la resposta unes quantes hores. Per això, quan un d’ells elimina un resultat, també desapareix del que MetaGer en rep.',
        'which' => 'A la pàgina de resultats de MetaGer, sota cada resultat, hi diu de quin cercador prové. Un resultat pot venir de diversos; en aquest cas, pregunteu a cadascun.',
        'shows' => 'A MetaGer a:',
        'kinds' => [
            'personal_data' => 'Dades personals',
            'outdated' => 'Contingut obsolet',
            'legal' => 'Drets d’autor i legal',
        ],
        'no_form' => 'Sense formulari, contacteu amb :name',
        'contact' => 'Contacteu amb :name',
        'us' => 'Escriviu-nos',
        'google' => 'MetaGer rep els resultats de Google a través del servei Serper. El que Google elimina del seu índex també desapareix de MetaGer.',
        'brave' => 'Brave Search té un índex propi. Les sol·licituds sobre dades personals s’envien per correu electrònic a privacy@brave.com amb l’assumpte «RTBF request»; la pàgina enllaçada explica què ha de contenir el correu.',
        'yandex' => 'Yandex té un índex propi. Per a cada sol·licitud hi ha un formulari diferent a les seves pàgines d’ajuda.',
        'mojeek' => 'Mojeek té un índex propi i no edita els resultats a mà. Retira pàgines per motius legals a petició; escriviu-li des de la pàgina de contacte i indiqueu els URL exactes.',
        'staan' => 'Staan és la interfície de cerca d’European Search Perspective. Les sol·licituds sobre dades personals les rep amb un formulari propi; ompliu tots els camps i adjunteu-hi els documents justificatius. L’eliminació només afecta l’índex de Staan.',
        'pixabay' => 'Pixabay allotja les imatges ell mateix. Feu servir el formulari de denúncia o el botó de denúncia a la pàgina de la imatge a Pixabay.',
        'onenewspage' => 'One News Page recull notícies d’altres editors. El seu formulari cobreix eliminació, correcció i drets d’autor; normalment la primera adreça és l’editor original.',
        'suma' => 'TootNews el gestiona SUMA-EV, l’associació que hi ha darrere de MetaGer. Les publicacions mateixes són en servidors de Mastodon; pregunteu-hi primer i després escriviu-nos.',
        'website' => 'BASE mostra el que contenen els repositoris on cerca. Quan el repositori elimina un registre, també desapareix de BASE.',
    ],
    'us' => [
        'heading' => 'Si tot i així ens escriviu',
        'text' => 'Podeu fer-ho, i us respondrem. Això és el que fem:',
        'steps' => [
            'Comprovem si MetaGer mostra el resultat per a les cerques que indiqueu i de quins cercadors prové.',
            'Us responem amb els enllaços adequats per a exactament aquests cercadors, els d’aquesta pàgina.',
            'Si teniu un dret davant de MetaGer mateix, per exemple segons l’art. 17 del RGPD en les cerques del vostre nom, amaguem el resultat a MetaGer, encara que continuï a l’origen. No cal que abans us adreceu al lloc web ni als cercadors. Amagar-lo només afecta MetaGer: el resultat continua al lloc web i a tots els altres cercadors.',
            'Responem en el termini d’un mes. Si una sol·licitud és especialment complexa, pot trigar fins a dos mesos més; us ho diem dins del primer mes. Si no amaguem un resultat, us en donem els motius. Aleshores podeu presentar una reclamació davant d’una autoritat de control de protecció de dades o acudir als tribunals.',
        ],
        'include' => 'Perquè sigui ràpid, indiqueu les adreces exactes (URL) dels resultats i les cerques en què apareixen.',
        'identity' => 'Si es tracta de les vostres dades personals, en alguns casos us demanem una prova que sou la persona afectada o que actueu en nom seu, per exemple una còpia del vostre document d’identitat. Si no, qualsevol podria fer-nos amagar qualsevol adreça. A la còpia podeu tapar tot el que no necessitem per comparar-la amb el resultat. La guardem amb la vostra sol·licitud i, un cop tancada, l’esborrem juntament amb la resta de les vostres dades.',
        'contact' => 'A la pàgina de contacte',
    ],
];
