<?php
return [
    'title' => 'La vostra afiliació a SUMA-EV',
    'non-de' => 'Malauradament, ara mateix només podem acceptar sol·licituds d\'admissió de països de parla alemanya. Ens podeu donar suport amb un <a href=":donationlink">donatiu</a>, que serà molt benvingut.',
    'success' => 'Moltes gràcies per enviar la vostra sol·licitud d\'afiliació. La processarem tan aviat com puguem. Després rebreu un correu nostre amb més informació a l\'adreça que ens heu indicat.',
    'back' => 'Torna a la pàgina d\'inici',
    'application' => [
        'cancel' => [
            'application' => 'Elimina la sol·licitud d\'afiliació',
            'update' => 'Descarta els canvis'
        ],
        'update_hint' => 'Els canvis sol·licitats per a la vostra afiliació es revisaran i acceptaran aviat. Si esteu conformes amb l\'estat que es mostra, podeu abandonar aquesta pàgina. Altrament, podeu fer més canvis o eliminar la vostra sol·licitud de canvi amb el botó de sota.',
        'description' => 'Gràcies per considerar l\'<a href="https://suma-ev.de/en/mitglieder/" target="_blank">afiliació</a> a la nostra associació sense ànim de lucre. Per tramitar la vostra sol·licitud només necessitem unes poques dades, que podeu emplenar aquí.',
        'update' => 'A sota veureu la informació que tenim desada de la vostra afiliació. Podeu modificar-la fent clic a «Edita». Aquí no és possible canviar les vostres dades de contacte. Si han canviat, envieu-nos un <a href=":contact_link" target="_blank">correu electrònic</a> amb la informació actualitzada.',
        "payment_block" => 'Intentarem autoritzar un pagament de la vostra propera quota per validar el mètode de pagament, però el pagament només s\'executarà si venç dins de les dues properes setmanes; en cas contrari, s\'anul·larà.'
    ],
    'data' => [
        'description' => 'Hem registrat les dades següents per a la vostra sol·licitud:',
        'name' => 'Nom',
        'email' => 'Adreça electrònica',
        "company" => "Nom de l'empresa",
        "amount" => "Quota d'afiliació",
        "payment_method" => "Mètode de pagament",
        "payment_methods" => [
            "banktransfer" => "Transferència bancària",
            "directdebit" => "Domiciliació bancària",
            "paypal" => "PayPal",
            "card" => "Targeta de crèdit"
        ],
        "payment" => [
            "interval" => [
                "monthly" => "mensual",
                "quarterly" => "trimestral",
                "six-monthly" => "Semestral",
                "annual" => "anual"
            ]
        ]
    ],
    'key' => [
        'description' => 'Per fer servir MetaGer s\'utilitza la clau següent, que nosaltres recarreguem. Si ja teníeu la sessió iniciada, s\'ha fet servir la vostra clau existent.',
        'later' => 'La primera recàrrega es fa un cop tramitada la vostra sol·licitud',
        'now' => 'Ja està carregada i es pot fer servir immediatament.',
    ],
];
