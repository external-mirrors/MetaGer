<?php

/**
 * Die Seite zum Erstellen eines Schlüssels — /schluessel-erstellen. Siehe
 * lang/de/key-create.php für die Herkunft der Schlüssel und dafür, was
 * gegenüber dem Keymanager neu ist.
 */
return [
    'heading' => 'Crea una clau',
    'lede' => 'La vostra clau és el vostre compte. Porta el vostre saldo de fitxes i és tot el que sabem de vosaltres: cap nom, cap adreça electrònica, cap contrasenya. Això també vol dir que, si la perdeu, perdeu el saldo que hi ha.',

    'existing' => [
        'text' => 'Ja teníeu una clau de MetaGer? Inicieu-hi la sessió en lloc de crear-ne una de nova: una clau nova té el seu propi saldo separat, i el saldo antic es queda a la clau antiga.',
        'action' => 'Inicia la sessió amb una clau existent',
    ],

    'offer' => [
        'text' => 'Una premuda del botó i ja en teniu una. Cap formulari, cap credencial: MetaGer sorteja una cadena de caràcters que encara no és de ningú.',
        'button' => 'Crea la clau ara',
    ],

    'working' => 'Un moment: estem sortejant una clau nova per a vosaltres …',

    'key' => [
        'label' => 'La vostra clau nova',
        'hint' => '36 caràcters. Són el que us permet iniciar la sessió a qualsevol altre dispositiu.',
    ],

    'copy' => [
        'action' => 'Copia la clau',
        'done' => 'Copiada',
    ],

    'save' => [
        'heading' => 'Deseu-la en un lloc segur',
        'text' => 'Mentre aquest navegador conservi la galeta, mantindreu la sessió iniciada. Si la perd —un dispositiu nou, dades de navegació esborrades—, aquesta clau és l\'únic camí de tornada.',

        'qr' => [
            'alt' => 'Codi QR que porta a la vostra clau',
            'action' => 'Desa com a imatge',
            'hint' => 'La imatge que demana el formulari d\'inici de sessió. Més endavant podeu pujar-la allà o fotografiar-la amb la càmera.',
        ],

        'url' => [
            'label' => 'Adreça d\'interès',
            'action' => 'Copia l\'URL',
            'hint' => 'Obrir aquest URL torna a configurar la clau juntament amb la configuració d\'aquest navegador.',
        ],

        'no_cookies' => 'Aquest navegador no desa galetes per a MetaGer. Sense galeta no mantindreu la sessió iniciada: aleshores l\'URL de dalt és la manera d\'iniciar-la abans d\'una cerca. També el podeu afegir com a cercador al navegador.',
    ],

    'continue' => 'Continua: afegeix saldo',
    'continue_hint' => 'Una clau nova encara no té saldo. El pas següent és triar un paquet de fitxes.',

    'errors' => [
        'keyserver_unreachable' => 'Ara mateix no s\'ha pogut crear cap clau. És cosa nostra i no vostra: torneu-ho a provar de seguida.',
        'too_many_attempts' => 'Des d\'aquesta connexió s\'acaben de crear moltíssimes claus. Espereu uns minuts i torneu a carregar la pàgina.',
        'no_key' => 'La clau s\'ha perdut pel camí; això passa quan la pàgina ha estat oberta molta estona. Aquí en teniu una de nova.',
    ],
];
