<?php

/**
 * Die Anmeldeseite — /anmelden. Siehe lang/de/login.php für die Herkunft der
 * Schlüssel und dafür, was gegenüber dem Keymanager neu ist.
 */
return [
    'heading' => 'Inicia la sessió a MetaGer',
    'lede' => 'La vostra clau és el vostre compte. Porta el vostre saldo de fitxes i és tot el que sabem de vosaltres: cap nom, cap adreça electrònica, cap contrasenya.',

    'key' => [
        'label' => 'Clau o codi d\'inici de sessió',
        'hint' => '36 caràcters. Des d\'un dispositiu que ja té la sessió iniciada també serveix la contrasenya d\'un sol ús de sis xifres del diàleg de transferència.',
        'placeholder' => 'xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx',
    ],

    'submit' => 'Inicia la sessió',
    'or' => 'o',

    'file' => [
        'button' => 'Tria el fitxer de còpia de seguretat',
        'hint' => 'El fitxer o la imatge del codi QR que vau desar en configurar la clau.',
    ],

    'qr' => [
        'button' => 'Escaneja el codi QR',
        'hint' => 'Amb la càmera d\'aquest dispositiu, per exemple des de la pantalla d\'un altre.',
        'no_camera' => 'No hi ha cap càmera disponible.',
        'invalid' => 'Aquest codi QR no conté cap clau.',
        'close' => 'Tanca',
    ],

    'create' => [
        'prompt' => 'Encara no teniu cap clau?',
        'action' => 'Configura una clau',
    ],

    'errors' => [
        'invalid_key' => 'Això no és una clau vàlida. Una clau té 36 caràcters i un codi d\'inici de sessió en té sis.',
        'invalid_login_code' => 'Aquest codi d\'inici de sessió ja no és vàlid. Dura uns segons i només serveix per a un inici de sessió: feu que el dispositiu connectat us en mostri un de nou. L\'abreujament que hi ha al costat del vostre saldo no és un codi d\'inici de sessió.',
        // Sis caràcters que no són cap clau. Gairebé sempre és l\'abreujament
        // que hi ha al costat del saldo: vegeu KeyIdenticon.
        'key_mark' => 'Aquests sis caràcters són l\'abreujament de la vostra clau: el que apareix al costat del vostre saldo. Identifica el compte, però no l\'obre. Per iniciar la sessió necessiteu la clau completa de 36 caràcters o un codi d\'inici de sessió d\'un dispositiu ja connectat.',
        'invalid_key_payment_id' => 'Això és un número de pagament, no una clau. La vostra clau té 36 caràcters i no comença per Z.',
        'no_input' => 'Introduïu una clau o trieu un fitxer de còpia de seguretat.',
        'file_unreadable' => 'No s\'ha pogut llegir cap clau d\'aquest fitxer. Hauria de contenir el codi QR que vau desar en configurar la clau.',
    ],

    'validation' => [
        'hex' => 'Una clau només conté els caràcters 0–9, a–f i guions.',
        'uuid' => 'Això no és una clau vàlida.',
        'login' => 'Això no és ni una clau completa ni un codi d\'inici de sessió.',
    ],

    'empty_key' => [
        'message' => 'Aquesta clau no té saldo. Si és el que esperàveu, inicieu la sessió; si no, pot ser que hàgiu escrit malament algun caràcter.',
        'entered' => 'Clau introduïda',
        'revalidate' => 'Revisa l\'entrada',
        'confirm' => 'Inicia la sessió igualment',
    ],

    'extension' => [
        'heading' => 'L\'extensió de MetaGer per al vostre navegador',
        'text' => 'Mantingueu la sessió iniciada fins i tot després d\'esborrar les dades del navegador — i conserveu un <a href=":tokenlink">anonimat demostrable</a> tot i tenir la sessió iniciada.',
        'install' => 'Instal·la-la per a :browser',
        'install_generic' => "Instal·la l'extensió",
    ],
];
