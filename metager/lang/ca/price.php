<?php

/**
 * Was ein MetaGer-Schlüssel kostet — /preise.
 *
 * Aus pass/lang/<locale>/cost.json des Keymanagers übernommen, wo diese Seite
 * bis zum Umzug lag. Die Preiszahlen selbst stehen bewusst nicht hier: sie
 * kommen über App\Landing\KeyPrice vom Keymanager, weil der Checkout sie
 * ausgibt.
 */

return [
    "headings" => [
        "Això és el que costa la vostra clau de MetaGer",
        "El més important, resumit",
    ],
    "texts" => [
        "Per cada cerca web sense publicitat a MetaGer amb la configuració predeterminada se us cobrarà <b>1 fitxa</b>. Podeu recarregar la clau amb un d'aquests paquets de fitxes en qualsevol moment.",
    ],
    "short-info" => [
        [
            "heading" => "Les fitxes són vàlides durant 2 anys",
            "text" => "Les fitxes que compreu estan pensades per ser vàlides fins que les gasteu. No hi ha cap ordre permanent.",
        ],
        [
            "heading" => "30 dies de garantia de devolució",
            "text" => "Si no esteu satisfets amb la vostra clau, teniu 30 dies des de la compra per retornar el saldo no utilitzat.",
        ],
        [
            "heading" => "La clau es configura i es fa servir automàticament al navegador",
            "text" => "No cal que feu res més per fer servir la clau de MetaGer a la cerca. Un cop carregada, es configura automàticament al vostre navegador i rebreu informació sobre com configurar-la fàcilment en altres dispositius.",
        ],
        [
            "heading" => "Sense rastreig",
            "text" => "Feu servir la nostra <a href=\":linkapp\">aplicació per a Android</a> o la nostra extensió de navegador i conserveu un anonimat demostrable amb els <a href=\":linktokens\">testimonis anònims</a>.",
        ],
    ],
    "pricing" => [
        "heading" => "Així es componen els nostres preus",
        "texts" => [
            "La major part dels nostres ingressos va directament als serveis de cerca que consulteu. Volem oferir un model sostenible, cosa que implica que els cercadors consultats no pateixin cap perjudici econòmic per proporcionar resultats anònims i sense publicitat a MetaGer. A més, hi ha una part per cobrir els nostres costos de personal i de servidors i, és clar, els preus inclouen les comissions dels proveïdors de pagament i els impostos.",
            "Així doncs, en triar els serveis de cerca que es consulten no només establiu el vostre propi cost, sinó que alhora decidiu quins projectes voleu donar suport. D'aquí també la facturació basada en fitxes.",
        ],
    ],
    "payment-methods" => [
        "heading" => "Mètodes de pagament",
        "texts" => [
            "Hem dissenyat les claus de MetaGer de manera que no requereixin cap dada personal. Tanmateix, com a molt tard en executar un pagament, normalment calen algunes dades. Ja sigui l'IBAN del compte pagador o l'adreça electrònica del compte de PayPal utilitzat. SUMA-EV no tracta aquestes dades ella mateixa ni les desa. Ara bé, segons el mètode de pagament, el proveïdor de pagament sí que ho fa.",
            "Per això, els nostres mètodes de pagament estan configurats de manera que calgui recollir com menys dades d'usuari millor i, en alguns casos, fins i tot cap.",
        ],
        "anonymous" => "Mètodes de pagament anònims",
        "more" => "Altres mètodes de pagament",
    ],
    /**
     * Die Namen der Zahlungsarten. Standen im "checkout"-Namensraum des
     * Keymanagers, der dort bleibt — hierher kopiert, weil diese Seite die
     * einzige war, die sie außerhalb des Bezahlvorgangs gebraucht hat.
     */
    "methods" => [
        "cash" => "Efectiu",
        "prepay" => "Transferència bancària",
        "card" => "Targeta de crèdit o dèbit",
    ],
];
