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
    ],
];
