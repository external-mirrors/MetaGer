<?php

return [
    'lookup' => [
        'heading' => 'Cercar una comanda',
        'description' => 'Introduïu l\'identificador de pagament d\'una de les vostres comandes per veure\'n els detalls.',
        'placeholder' => 'Identificador de pagament',
        'submit' => 'Mostra la comanda',
        'error' => [
            'invalid' => 'L\'identificador de pagament no és vàlid.',
            'not_found' => 'Cap comanda de la vostra clau coincideix amb aquest identificador de pagament.',
        ],
    ],

    'show' => [
        'heading' => 'Comanda :reference',
        'breadcrumb' => 'Comandes',
        'thanks' => 'Gràcies per la vostra compra!',
        'pending' => 'Les vostres fitxes s\'abonaran tan aviat com rebem el pagament. Rebreu un correu de confirmació quan sigui el cas.',
        'lookup_hint' => 'Podeu tornar a obrir aquesta vista general en qualsevol moment introduint el vostre identificador de pagament (:reference).',
        'order_line' => 'Comanda :id del :date',
        'item' => 'Clau de MetaGer: fitxes',
        'count' => 'Quantitat',
        'price' => 'Preu',
        'vat' => 'IVA (:rate %)',
        'total' => 'Import total',
        'exchange_rate' => 'Tipus de canvi',
        'download_confirmation' => 'Baixa la confirmació de la comanda',
        'request_invoice' => 'Crea la factura',
        'request_refund' => 'Sol·licita un reemborsament',
    ],

    'invoice' => [
        'heading' => 'Factura',
        'breadcrumb' => 'Comanda :reference',
        'description' => 'Si necessiteu una factura, introduïu les vostres dades de facturació al formulari de sota.',
        'ready' => 'Ja existeix una factura per a aquesta comanda.',
        'download' => 'Baixa la factura',
        'submit' => 'Crea la factura',
        'storage' => 'Estem legalment obligats a conservar les factures emeses durant <span class="bold">10 anys</span>. Com que la factura se us ha d\'emetre nominalment, necessàriament conté dades personals (nom, adreça).',
        'error' => [
            'invalid' => 'Comproveu les vostres dades — falten alguns camps obligatoris o són massa llargs.',
        ],
        'field' => [
            'company' => 'Nom de l\'empresa (opcional)',
            'first_name' => 'Nom',
            'last_name' => 'Cognoms',
            'address1' => 'Adreça 1',
            'address2' => 'Adreça 2 (opcional)',
            'zip' => 'Codi postal',
            'city' => 'Població',
            'state' => 'Província (opcional)',
        ],
    ],

    'refund' => [
        'heading' => 'Reemborsament',
        'breadcrumb' => 'Comanda :reference',
        'unavailable' => 'No queda saldo reemborsable per a aquesta comanda — o bé ja s\'ha sol·licitat un reemborsament, o bé el mètode de pagament utilitzat no admet una sol·licitud de reemborsament mitjançant aquest formulari.',
        'description' => 'No esteu satisfets amb la vostra clau? Ens sap molt de greu! És clar que en aquest cas us reemborsarem l\'import de la factura. El reemborsament sempre es fa al mateix compte que es va fer servir per al pagament original. També ens agradarà rebre les vostres crítiques.',
        'partial_note' => 'Nota: ja heu fet servir part del saldo comprat. Per això només us podem reemborsar <span class="bold">:count</span> de <span class="bold">:total</span> cerques.',
        'message' => [
            'label' => 'El vostre missatge (opcional)',
        ],
        'submit' => ':amount € Sol·licita un reemborsament',
        'error' => [
            'not_allowed' => 'Ja no és possible un reemborsament per a aquesta comanda.',
            'unreachable' => 'Error en enviar el missatge. Torneu-ho a provar més tard.',
        ],
    ],
];
