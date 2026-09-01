<?php

/**
 * Aufladen (/konto/aufladen) — App\Http\Controllers\ChargeController.
 *
 * `cash`, `consent` und micropayment.prepay sind wortgleich aus dem Keymanager
 * übernommen (dessen checkout.json unter payments.cash/payments.prepay/
 * micropayment, dessen order.json unter agb und revocation, je Sprache) —
 * dieselbe Kasse, jetzt hier gerendert. `page`, `manual`,
 * micropayment.lastschrift/directbanking (dort nur Logos, keine Texte),
 * `returned` und vrpayment.label/submit/error.failed sind neu; vrpayment.privacy
 * ist wortgleich aus dem Keymanager übernommen wie cash/consent/micropayment.
 */
return [
    'page' => [
        'change' => 'Canvia la quantitat',
        'methods' => [
            'heading' => 'Trieu el mètode de pagament',
            'more' => 'Més mètodes de pagament',
            'back' => 'Tria un altre mètode de pagament',
            'cash_note' => 'Anònim',
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

    'vrpayment' => [
        'label' => 'Wero',
        'submit' => 'Fes el pagament',
        'privacy' => 'Fent clic a «Fes el pagament» sereu redirigits al nostre proveïdor de pagaments <a href="https://www.vr-payment.de" target="_blank">VR Payment</a> per completar la compra. Més informació sobre la <a href=":link" target="_blank">privadesa a VR Payment</a>.',
        'error' => [
            'failed' => 'VR Payment ha rebutjat aquest pagament. Torneu-ho a provar o trieu un altre mètode de pagament.',
            'onion' => 'Wero no està disponible a través de la nostra adreça onion: el proveïdor de pagament no us pot tornar aquí després. Trieu un altre mètode de pagament.',
        ],
    ],

    'paypal' => [
        'heading' => 'Fes el pagament',
        'submit' => 'Fes el pagament',
        'loading' => 'S\'està carregant el mètode de pagament',
        'cancel' => 'S\'ha cancel·lat el procés de pagament. Si el pagament s\'havia completat abans de la cancel·lació, la comanda es tramitarà tan aviat com el processador de pagaments el confirmi. Altrament, torneu-ho a provar.',
        'privacy' => 'Els mètodes de pagament d\'aquest grup normalment no requereixen un compte de PayPal, però s\'hi processen. Més informació sobre la <a href="https://www.paypal.com/us/legalhub/privacy-full" target="_blank">privadesa a PayPal</a>.',
        'noscript' => 'Aquest mètode de pagament necessita JavaScript. Trieu un altre mètode de pagament o activeu JavaScript.',
        'funding' => [
            'paypal' => 'PayPal',
            'card' => 'Targeta de crèdit o dèbit',
            'p24' => 'Przelewy24',
            'bancontact' => 'Bancontact',
            'blik' => 'BLIK',
            'eps' => 'EPS',
            'mybank' => 'MyBank',
        ],
        'error' => [
            'not_available' => 'El mètode de pagament seleccionat no està disponible a la vostra regió.',
            'generic' => 'S\'ha cancel·lat el procés de pagament per un error. Si el pagament s\'havia completat abans de la cancel·lació, la comanda es tramitarà tan aviat com el processador de pagaments el confirmi. Altrament, torneu-ho a provar.',
        ],
        'card' => [
            'label' => 'Targeta de crèdit o dèbit',
            'name' => 'Nom del titular (opcional)',
            'number' => 'Número de la targeta',
            'expiration' => 'Vàlida fins a',
            'cvv' => 'CVV',
            'error' => [
                '9500' => 'Targeta de crèdit rebutjada per sospita de frau',
                '5100' => 'L\'entitat de crèdit ha rebutjat la targeta',
                '00N7' => 'CVV incorrecte. Reviseu les dades introduïdes',
                '5400' => 'Targeta de crèdit caducada',
                '5180' => 'Ha fallat la comprovació de Luhn',
                '5120' => 'Targeta rebutjada per fons insuficients.',
                '9520' => 'Targeta rebutjada per pèrdua o robatori',
                '0500' => 'L\'entitat de crèdit ha rebutjat la targeta',
                '1330' => 'Targeta de crèdit no vàlida. Reviseu les dades introduïdes',
                '3ds' => 'Ha fallat l\'autenticació 3DS',
                'generic' => 'L\'entitat de crèdit ha rebutjat la targeta',
            ],
        ],
    ],
    'returned' => [
        'heading' => 'Recàrrega completada',
        'paid' => 'Gràcies! La vostra clau s\'ha recarregat amb :amount tokens.',
        'pending' => 'El vostre pagament encara s\'està processant. Tan bon punt ens arribi, la vostra clau es recarregarà automàticament.',
    ],
];
