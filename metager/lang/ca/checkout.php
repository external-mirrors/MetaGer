<?php

/**
 * Aufladen (/konto/aufladen) — App\Http\Controllers\ChargeController.
 *
 * `cash`, `consent` und micropayment.prepay sind wortgleich aus dem Keymanager
 * übernommen (dessen checkout.json unter payments.cash/payments.prepay/
 * micropayment, dessen order.json unter agb und revocation, je Sprache) —
 * dieselbe Kasse, jetzt hier gerendert. `page`, `manual`,
 * micropayment.lastschrift/directbanking (dort nur Logos, keine Texte) und
 * `returned` sind neu.
 */
return [
    'page' => [
        'change' => 'Canvia la quantitat',
        'methods' => [
            'heading' => 'Trieu el mètode de pagament',
            'more' => 'Més mètodes de pagament',
            'back' => 'Tria un altre mètode de pagament',
        ],
        'cancel' => 'Tornar al compte',
    ],

    'cash' => [
        'label' => 'Efectiu',
        'description' => 'També podeu recarregar la clau en efectiu. Per fer-ho, envieu-nos per correu postal el número de comanda següent juntament amb l\'import desitjat. Tingueu en compte que el número de comanda ha de ser llegible perquè el puguem tramitar.',
        'note' => 'Tingueu en compte el següent:',
        'no_large_values' => 'Per la vostra pròpia seguretat, no ens envieu més de 100 € per correu postal. No assumim cap responsabilitat sobre el trajecte. Sou vosaltres qui heu de garantir que la carta ens arribi.',
        'no_coins' => 'Només acceptem bitllets. No ens envieu monedes!',
        'accepted_currencies' => 'Només acceptem les monedes següents: EUR, USD, CAD, GBP.',
        'currency_translation' => 'Sempre carreguem imports en EUR. Si ens envieu una altra moneda, l\'import enviat es convertirà al tipus de canvi del dia',
        'no_refund' => 'A causa de la legislació vigent contra el blanqueig de capitals, malauradament no és possible cap reemborsament ni devolució. Ara bé, un cop hàgim registrat la recàrrega, podeu introduir l\'identificador de pagament enviat a «Comandes» per obtenir un resum de la comanda i/o demanar una factura.',
        'generate' => 'Genera un identificador de pagament',
        'error' => [
            'unreachable' => 'Alguna cosa ha anat malament en crear la vostra comanda. Torneu-ho a provar més tard.',
        ],
        'order' => [
            'heading' => 'El vostre identificador de pagament',
            'copy' => 'Copia l\'identificador de pagament',
            'address_heading' => 'Envieu la carta a l\'adreça següent i apunteu-vos l\'identificador de pagament per als vostres arxius',
            'address' => 'SUMA-EV
Postfach 51 01 43
30631 Hannover
Alemanya',
            'expiration' => 'L\'identificador de pagament és vàlid fins al :date. Després d\'aquesta data ja no es pot fer servir per a una recàrrega.',
            'unique' => 'Feu servir l\'identificador de pagament només per a una única recàrrega. En rebreu un de nou cada cop que visiteu aquesta pàgina!',
        ],
    ],

    'consent' => [
        'agb' => 'En continuar amb la compra, accepteu les nostres <a href=":agblink" target="_blank">condicions generals</a>.',
        'label' => 'Accepto expressament l\'execució del contracte abans que expiri el termini de desistiment. Entenc que el <a href=":revocation_link" target="_blank">dret de desistiment</a> s\'extingeix quan comença l\'execució del contracte. En canvi, us concedim un <a href=":refundlink" target="_blank">dret de devolució voluntari de 30 dies</a>.',
        'error' => 'Aquest camp és obligatori',
    ],

    'manual' => [
        'label' => 'Manual (dev)',
        'description' => 'Salteu un pagament real. Només disponible en un entorn de desenvolupament.',
        'submit' => 'Completa el pagament',
    ],

    'micropayment' => [
        'label' => 'Micropayment',
        'prepay' => [
            'label' => 'Transferència bancària',
            'email' => [
                'label' => 'Adreça electrònica',
                'description' => 'A aquesta adreça us enviarem, un sol cop, informació sobre les nostres dades bancàries i un avís quan el pagament s\'hagi completat.',
            ],
        ],
        'lastschrift' => ['label' => 'Domiciliació bancària'],
        'directbanking' => ['label' => 'Transferència bancària instantània'],
        'submit' => 'Fes el pagament',
        'privacy' => 'Fent clic a «Fes el pagament» sereu redirigits al nostre proveïdor de pagaments <a href="https://micropayment.de" target="_blank">MicroPayment</a> per completar la compra. Més informació sobre la <a href=":link" target="_blank">privadesa a :link_text</a>.',
    ],

    'returned' => [
        'heading' => 'Recàrrega completada',
        'paid' => 'Gràcies! La vostra clau s\'ha recarregat amb :amount tokens.',
        'pending' => 'El vostre pagament encara s\'està processant. Tan bon punt ens arribi, la vostra clau es recarregarà automàticament.',
    ],
];
