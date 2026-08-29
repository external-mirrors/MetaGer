<?php

/**
 * Die Anmeldeseite — /anmelden. Siehe lang/de/login.php für die Herkunft der
 * Schlüssel und dafür, was gegenüber dem Keymanager neu ist.
 */
return [
    'heading' => 'Iniciar sesión en MetaGer',
    'lede' => 'Su clave es su cuenta. Lleva su saldo de fichas y es todo lo que sabemos de usted: ningún nombre, ninguna dirección de correo, ninguna contraseña.',

    'key' => [
        'label' => 'Clave o código de acceso',
        'hint' => '36 caracteres. Desde un dispositivo que ya tiene la sesión iniciada también sirve la contraseña de un solo uso de seis cifras del diálogo de transferencia.',
        'placeholder' => 'xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx',
    ],

    'submit' => 'Iniciar sesión',
    'or' => 'o',

    'file' => [
        'button' => 'Elegir archivo de copia de seguridad',
        'hint' => 'El archivo o la imagen del código QR que guardó al configurar su clave.',
    ],

    'qr' => [
        'button' => 'Escanear código QR',
        'hint' => 'Con la cámara de este dispositivo, por ejemplo desde la pantalla de otro.',
        'no_camera' => 'No hay cámara disponible.',
        'invalid' => 'Ese código QR no contiene ninguna clave.',
        'close' => 'Cerrar',
    ],

    'create' => [
        'prompt' => '¿Todavía no tiene clave?',
        'action' => 'Configurar una clave',
    ],

    'errors' => [
        'invalid_key' => 'Eso no es una clave válida. Una clave tiene 36 caracteres y un código de acceso tiene seis cifras.',
        'invalid_login_code' => 'Ese código de acceso ya no es válido. Dura unos segundos y sirve para un solo inicio de sesión: haga que el dispositivo conectado le muestre uno nuevo.',
        'invalid_key_payment_id' => 'Eso es un número de pago, no una clave. Su clave tiene 36 caracteres y no empieza por Z.',
        'no_input' => 'Introduzca una clave o elija un archivo de copia de seguridad.',
        'file_unreadable' => 'No se ha podido leer ninguna clave de ese archivo. Debería contener el código QR que guardó al configurar su clave.',
    ],

    'validation' => [
        'hex' => 'Una clave solo contiene los caracteres 0–9, a–f y guiones.',
        'uuid' => 'Eso no es una clave válida.',
        'login' => 'Eso no es ni una clave completa ni un código de acceso.',
    ],

    'empty_key' => [
        'message' => 'Esta clave no tiene saldo. Si es lo que esperaba, inicie sesión; si no, puede que haya escrito mal algún carácter.',
        'entered' => 'Clave introducida',
        'revalidate' => 'Revisar lo introducido',
        'confirm' => 'Entrar de todos modos',
    ],

    'extension' => [
        'heading' => 'La extensión de MetaGer para su navegador',
        'text' => 'Siga conectado incluso después de borrar los datos del navegador — y permanezca <a href=":tokenlink">demostrablemente anónimo</a> aun estando conectado.',
        'install' => 'Instalar para :browser',
        'install_generic' => 'Instalar la extensión',
    ],
];
