<?php
return [
    'plugin' => 'Instalar MetaGer',
    'plugin-title' => 'Añadir MetaGer a su navegador',
    'key' => [
        'placeholder' => 'Introduzca su MetaGer Key para iniciar la búsqueda.',
        'tooltip' => [
            'nokey' => 'Configurar una búsqueda sin anuncios',
            'empty' => 'Ficha agotada. Recarga ahora.',
            'low' => 'Ficha a punto de agotarse. Recarga ahora.',
            'full' => 'Búsqueda sin publicidad activada.',
        ],
    ],
    'placeholder' => 'MetaGer: Buscar & encontrar seguro, proteger la privacidad',
    'searchbutton' => 'Iniciar MetaGer-Search',
    'foki' => [
        'web' => 'Web',
        'bilder' => 'Imágenes',
        'nachrichten' => 'Noticias/Política',
        'science' => 'Ciencia',
        'produkte' => 'Productos',
        'maps' => 'Mapas',
    ],
    'adfree' => 'Utiliza MetaGer sin publicidad',
    'skip' => [
        'search' => 'Saltar a la consulta de búsqueda',
        'navigation' => 'Saltar a la navegación',
        'fokus' => 'Saltar a la selección del foco de búsqueda',
    ],
    'lang' => 'lenguaje wwitch',
    'searchreset' => 'eliminar la entrada de consulta de búsqueda',
    'searchbar-replacement' => [
        'tagline' => 'Open source. Sin publicidad. Anónimo.',
        'message' => 'Su clave es su acceso: sin cuenta, sin dirección de correo. Su saldo y sus ajustes dependen de ella.',
        'first_time' => '¿Es la primera vez que viene?',
        'start' => 'Configurar una clave',
        // Swapped in by resources/js/accountBreadcrumb.js when this browser has
        // rendered a signed-in page before. Three strings, replacing three
        // elements in place — nothing appears, nothing moves.
        'welcome_back' => 'Bienvenido de nuevo.',
        'welcome_back_message' => 'Ya había iniciado sesión en este dispositivo. Inicie sesión con la misma clave: su saldo sigue ahí.',
        'welcome_back_button' => 'Volver a iniciar sesión',
        'have_key' => 'Iniciar sesión con mi clave',
        'login' => 'Iniciar sesión',
        'key_error' => "La clave introducida no es válida. Por favor, compruebe la entrada.",
        'login_code_error' => "El código de inicio de sesión introducido no era válido. Sugerencia: ¡Los códigos de inicio de sesión sólo son válidos mientras están visibles en otro dispositivo!",
        'payment_id_error' => "Ha introducido un identificador de pago que no es una clave correcta. Tu clave tiene 36 caracteres.",
        'new_key' => '¿Aún no tienes llave?',
        'extension' => 'Permanezca conectado y anónimo con nuestra webextensión',
    ],
    // The landing page shown to a visitor without a key: hero, "how it works",
    // and the five benefit cards. It came from the keymanager's own root page
    // (pass/views/index.ejs, pass/lang/*/index.json), which /keys used to serve
    // and which now redirects here.
    //
    // Placeholders are Laravel's :name, not i18next's {{name}}, and the links
    // are passed in from parts/landing/* so the locale prefix and the /keys
    // paths stay in one place.
    'landing' => [
        'title' => 'MetaGer: busca y navega por la web sin que te vigilen',
        'description' => 'MetaGer respeta tu privacidad: sin publicidad, sin rastreo, sin registros. Y ahora también puedes visitar cualquier web de forma anónima.',
        'advantages' => [
            'ads' => 'Sin publicidad',
            'tracking' => 'Sin rastreo',
            'logging' => 'Sin registros',
            'compromise' => 'Sin concesiones',
        ],
        'calltoaction' => 'Cómo funciona',
        'benefits' => [
            'browsing' => [
                'heading' => 'No solo búsqueda anónima: también navegación anónima',
                'description' => 'Con tu clave de MetaGer también puedes abrir cualquier web en un navegador privado que se ejecuta de forma segura en nuestros servidores, no en tu dispositivo. Las webs no pueden saber quién eres ni desde dónde navegas, y todo se borra automáticamente al terminar la sesión. Sin instalación ni configuración: solo abrir y empezar.',
                'fingerprinting' => 'Huella digital',
                'tracking' => 'Rastreo',
            ],
            'ads' => [
                'heading' => 'Sin publicidad',
                'description' => 'La publicidad y la privacidad casi nunca encajan. Por eso en MetaGer no hay ningún tipo de publicidad, de modo que podemos proteger tu privacidad sin concesiones.',
                'ads' => 'Publicidad',
                'tracking' => 'Enlaces de rastreo',
            ],
            'logging' => [
                'heading' => 'Sin registros',
                'description' => 'Buscar en internet suele dejar un rastro de datos. Nosotros no necesitamos conservar nada: nuestro buscador está diseñado para que combatir el spam no requiera registros. Tampoco te encontrarás ni un solo captcha en nuestro sitio, ni siquiera usando una VPN.',
                'logging' => 'Registros',
            ],
            'compromise' => [
                'heading' => 'Sin concesiones',
                'description' => 'En lugar de una cuenta ligada a tus datos personales, simplemente recibes una clave generada al azar, sin nombre ni dirección de correo. Elige entre varios <a href=":linkPaymentMethods">métodos de pago</a>, incluido el pago en efectivo, totalmente anónimo. Con nuestra <a href=":linkApp">aplicación para Android</a> o la extensión de navegador, incluso puedes demostrar que tus búsquedas siguen siendo anónimas gracias a los <a href=":linkToken">tokens anónimos</a>.',
                'compromise' => 'Datos personales',
            ],
            'efficiency' => [
                'heading' => 'Busca de forma más eficiente',
                'description' => 'Encuentra antes lo que buscas. Cuando resulta útil, añadimos enlaces directos claros, noticias relevantes y vídeos dentro de los resultados de búsqueda. Nuestra búsqueda de imágenes también recurre a fuentes adicionales.',
            ],
        ],
        'howitworks' => [
            'heading' => 'Cómo funciona',
            'steps' => [
                [
                    'heading' => 'Consigue tu clave gratuita',
                    'description' => 'Tu clave de MetaGer se genera automáticamente. Sin registro ni datos personales. Es lo único que necesitas para usar MetaGer.',
                ],
                [
                    'heading' => 'Activa tu acceso',
                    'description' => 'Un <a href=":linkCost">pago</a> único añade saldo a tu clave, que llamamos token. Con ello activas la búsqueda sin publicidad ni rastreo y la navegación anónima, además de todas las funciones actuales y futuras de MetaGer. Unos 500 token (5 €) suelen durar alrededor de 2 meses.',
                    'membership' => 'Nota: los miembros de nuestra asociación sin ánimo de lucro <a href="https://suma-ev.de" target="_blank">SUMA-EV</a> pueden usar MetaGer sin coste adicional. <a href=":linkMembership" target="_blank">Hazte miembro ahora</a>',
                ],
                [
                    'heading' => 'Usa MetaGer en todas partes',
                    'description' => 'Usa la misma clave en tantos dispositivos como quieras, o compártela con amigos y familia. Solo tienes que abrir MetaGer en cualquier dispositivo, introducir tu clave y ya puedes buscar, o navegar de forma anónima.',
                ],
            ],
            'start' => 'Empezar',
            'login' => 'Ya tengo una clave',
        ],
    ],
];
