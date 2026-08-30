<?php

/**
 * Die Seite zum Erstellen eines Schlüssels — /schluessel-erstellen. Siehe
 * lang/de/key-create.php für die Herkunft der Schlüssel und dafür, was
 * gegenüber dem Keymanager neu ist.
 */
return [
    'heading' => 'Crear una clave',
    'lede' => 'Su clave es su cuenta. Lleva su saldo de fichas y es todo lo que sabemos de usted: ningún nombre, ninguna dirección de correo, ninguna contraseña. Eso significa también que, si la pierde, pierde el saldo que hay en ella.',

    'existing' => [
        'text' => '¿Ya tuvo una clave de MetaGer? Inicie sesión con ella en lugar de crear una nueva: una clave nueva recibe su propio saldo separado, y el antiguo se queda en la clave antigua.',
        'action' => 'Iniciar sesión con una clave existente',
    ],

    'offer' => [
        'text' => 'Una pulsación del botón y ya tiene una. Ningún formulario, ninguna credencial: MetaGer sortea una cadena de caracteres que todavía no es de nadie.',
        'button' => 'Crear la clave ahora',
    ],

    'working' => 'Un momento: estamos sorteando una clave nueva para usted …',

    'key' => [
        'label' => 'Su clave nueva',
        'hint' => '36 caracteres. Son con los que inicia sesión en cualquier otro dispositivo.',
    ],

    'copy' => [
        'action' => 'Copiar la clave',
        'done' => 'Copiada',
    ],

    'save' => [
        'heading' => 'Guárdela en algún sitio',
        'text' => 'Mientras este navegador conserve la cookie, usted seguirá con la sesión iniciada. Si la pierde —un dispositivo nuevo, datos de navegación borrados—, esta clave es el único camino de vuelta.',

        'qr' => [
            'alt' => 'Código QR que lleva a su clave',
            'action' => 'Guardar como imagen',
            'hint' => 'La imagen que pide el formulario de inicio de sesión. Más adelante puede subirla allí o fotografiarla con la cámara.',
        ],

        'url' => [
            'label' => 'Marcador',
            'action' => 'Copiar la URL',
            'hint' => 'Abrir esta URL vuelve a configurar la clave junto con los ajustes de este navegador.',
        ],

        'no_cookies' => 'Este navegador no guarda cookies para MetaGer. Sin cookie no seguirá con la sesión iniciada: entonces la URL de arriba es la manera de iniciarla antes de una búsqueda. También puede añadirla como buscador en su navegador.',
    ],

    'continue' => 'Continuar: añadir saldo',
    'continue_hint' => 'Una clave nueva todavía no tiene saldo. En el paso siguiente elige un paquete de fichas.',

    'errors' => [
        'keyserver_unreachable' => 'Ahora mismo no se ha podido crear ninguna clave. Es cosa nuestra y no suya: inténtelo de nuevo en un momento.',
        'too_many_attempts' => 'Desde esta conexión se acaban de crear muchísimas claves. Espere unos minutos y vuelva a cargar la página.',
        'no_key' => 'La clave se perdió por el camino; eso pasa cuando la página ha estado abierta mucho tiempo. Aquí tiene una nueva.',
    ],
];
