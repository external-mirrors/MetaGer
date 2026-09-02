<?php

/**
 * Anonyme Token — /hilfe/anonyme-token.
 *
 * Aus dem "anonymous-token"-Zweig von pass/lang/<locale>/help.json.
 * Der Pfad /keys/help/anonymous-token wird dauerhaft hierher weitergeleitet:
 * er steht in bereits versandten Mitglieds-Willkommensmails.
 */

return [
    "heading" => "Testimonis anònims",
    "description" => [
        "heading" => "Què són els testimonis anònims?",
        "text" => "Si feu servir una clau de MetaGer, rebeu una contrasenya generada a l'atzar que el vostre navegador ens envia amb cada consulta perquè puguem activar la cerca sense publicitat. Si feu servir la nostra <a href=\"/app\" target=\"_blank\">aplicació per a Android</a> o la nostra extensió web per a <a href=\"https://chrome.google.com/webstore/detail/gjfllojpkdnjaiaokblkmjlebiagbphd\" target=\"_blank\" rel=\"noopener\">Chrome</a> i <a href=\"https://addons.mozilla.org/firefox/addon/metager-suche/\" target=\"_blank\" rel=\"noopener\">Firefox</a>, en lloc d'aquesta contrasenya el navegador ens envia amb cada consulta una contrasenya generada a l'atzar (testimoni anònim) per autenticar-se, generada localment. Això garanteix que cada contrasenya sigui única i que no tingui cap relació ni amb la clau real de MetaGer ni entre les diferents contrasenyes.",
    ],
    "problem" => [
        "heading" => "Quin problema volen resoldre els testimonis anònims?",
        "text" => "Si el vostre navegador ens envia sempre la mateixa contrasenya amb cada consulta, com a mínim teòricament tindríem la possibilitat d'establir una correlació entre totes les cerques fetes amb la mateixa clau. Encara que, evidentment, no ho fem, caldria confiança per estar segurs de la vostra cerca anònima. Perquè no només haguem de prometre la cerca anònima, sinó que també la puguem demostrar, hem introduït els testimonis anònims.",
    ],
    "general-function" => [
        "heading" => "Com funciona?",
        "texts" => [
            "Volem, doncs, que les contrasenyes d'un sol ús es generin directament al vostre dispositiu i que després ens les envieu per autenticar-vos durant les cerques. Tanmateix, per a cada testimoni anònim del vostre dispositiu hem d'assegurar-nos que se n'hagi restat una fitxa normal de la vostra clau de MetaGer, sense (i aquí rau la qüestió) que sapiguem quina clau de MetaGer s'ha fet servir per generar el testimoni anònim.",
            "Tradicionalment faríem servir alguna forma de signatura criptogràfica per a això. En aquest cas, signaríem el testimoni anònim generat. Després, quan ens enviéssiu el testimoni anònim juntament amb la signatura, podríem estar segurs que el testimoni és vàlid. Ara bé, per obtenir la signatura ens hauríeu enviat el testimoni anònim juntament amb la vostra clau real, cosa que anul·laria l'anonimat.",
            "Per això fem servir una forma modificada de signatura criptogràfica, l'anomenada <a href=\"https://ca.wikipedia.org/wiki/Signatura_cega\" target=\"_blank\">signatura cega</a>. Fent una analogia amb la vida real, és com si ens enviéssiu el vostre testimoni anònim dins un sobre de paper carbó. En aquest exemple no podríem obrir el sobre, però sí que podríem signar-lo per fora, de manera que la nostra signatura es transferiria al testimoni anònim de dins. Quan recuperéssiu el sobre, en podríeu treure el contingut i, més endavant, tornar-nos a enviar la contrasenya i la signatura. Aleshores podríem confirmar que la signatura és realment nostra.",
            "De fet, aquesta analogia és una mica enganyosa, perquè en el procés real, en el moment en què ens envieu el testimoni anònim i la signatura, no només no havíem vist mai abans el testimoni anònim, sinó que tampoc no havíem vist mai la signatura mateixa. I, tot i així, podem verificar que la signatura la vam generar nosaltres.",
        ],
    ],
    "meaning" => [
        "heading" => "Què vol dir això per a les vostres cerques autenticades?",
        "texts" => [
            "Fent servir l'algorisme descrit, tant nosaltres com vosaltres podem garantir que en cada cerca autenticada s'utilitza una contrasenya nova, aleatòria i sense cap relació amb la vostra clau de MetaGer.",
            "El que té d'especial aquest algorisme és que tots els components que garanteixen l'anonimat s'executen localment al vostre dispositiu. Qualsevol pot consultar i verificar aquest codi font en qualsevol moment.",
            "I el millor de tot: no cal que configureu res per fer servir els testimonis anònims. N'hi ha prou d'instal·lar i fer servir la nostra extensió de navegador o l'aplicació per a Android perquè el vostre dispositiu utilitzi testimonis anònims en totes les cerques.",
        ],
    ],
    "technical-function" => [
        "heading" => "L'algorisme que hi ha al darrere:",
        "texts" => [
            "En una signatura RSA clàssica, agafaríem el testimoni anònim <code>m</code>, l'exponent secret <code>d</code> i el mòdul públic <code>N</code> de la nostra clau privada i crearíem la signatura amb <code>m^d (mod N)</code>. Però nosaltres volem que <code>m</code> continuï sent secret.",
            "Per això, el vostre dispositiu crea un nombre aleatori <code>r</code> amb un generador de nombres aleatoris, coprimer amb <code>N</code>. És a dir, el màxim comú divisor de <code>r</code> i <code>N</code> ha de ser <code>1</code>.",
            "Com que <code>r</code> és un nombre aleatori, se'n dedueix que <code>m'</code> no revela cap informació sobre el testimoni anònim <code>m</code> desat localment.",
            "El nostre servidor rep ara el testimoni anònim ofuscat <code>m'</code> del vostre dispositiu juntament amb la clau de MetaGer que cal fer servir. Restem una fitxa de la clau i tornem al vostre dispositiu la signatura, també ofuscada, <code>s'&Congruent; (m')^d (mod N)</code>.",
            "El vostre dispositiu ja pot calcular la signatura RSA vàlida <code>s</code> per al testimoni anònim sense ofuscar: <code>s&Congruent; s' r^-1 (mod N)</code>. Això funciona perquè, per a les claus RSA, <code>r^(e*d)&Congruent; r (mod N)</code>. I, per tant, també: <code>s &Congruent; s' * r^-1 &Congruent; (m')^d*r^-1 &Congruent; m^d*r^(e*d)*r^-1 &Congruent; m^d*r*r^-1 &Congruent; m^d (mod N)</code>.",
            "El vostre dispositiu ens envia ara el testimoni anònim sense ofuscar juntament amb la signatura associada per autoritzar una cerca. Durant la cerca, la clau ja no se'ns envia.",
        ],
    ],
];
