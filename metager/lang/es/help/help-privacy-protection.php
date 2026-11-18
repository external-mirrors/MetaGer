<?php
return [
    'title' => 'Ayuda de MetaGer',
    'backarrow' => 'Devolver',
    'tor' => [
        'title' => 'Servicio oculto TOR <a title="For easy help, click here" href="/hilfe/easy-language/privacy-protection#eh-torhidden" ><img class="easy-help-icon lm-only" src="/img/help-questionmark-icon-lm.svg"/><img class="easy-help-icon dm-only" src="/img/help-questionmark-icon-dm.svg"/></a>',
        '1' => 'Desde hace muchos años, MetaGer oculta y no almacena las direcciones IP. Sin embargo, estas direcciones son temporalmente visibles en el servidor de MetaGer mientras se ejecuta una búsqueda: si MetaGer se viera comprometida, un atacante podría leer y almacenar sus direcciones. Para cumplir con los más altos requisitos de seguridad, operamos una instancia de MetaGer en la red Tor: el Servicio Oculto MetaGer TOR - accesible a través de: <a href="/tor/" target="_blank" rel="noopener">https://metager.de/tor/</a>. Para utilizarlo, necesita un navegador especial, que puede descargar de <a href="https://www.torproject.org/" target="_blank" rel="noopener">https://www.torproject.org/</a>.',
        '2' => 'Puede acceder a MetaGer en el navegador Tor en: http://metagerv65pwclop2rsfzg4jwowpavpwd6grhhlvdgsswvo6ii4akgyd.onion .',
    ],
    'proxy' => [
        'title' => 'Abrir de forma anónima (SafeBrowse) <a title="For easy help, click here" href="/hilfe/easy-language/privacy-protection#eh-proxy" ><img class="easy-help-icon lm-only" src="/img/help-questionmark-icon-lm.svg"/><img class="easy-help-icon dm-only" src="/img/help-questionmark-icon-dm.svg"/></a>',
        'intro' => 'Con «Abrir de forma anónima» visita un sitio web sin que este llegue a ver su dispositivo. La página se abre en un navegador que funciona en nuestros servidores: SafeBrowse. El sitio web solo trata con ese navegador y no conoce ni su dirección IP ni nada sobre su dispositivo, su navegador o sus cookies.',
        'use' => [
            'title' => 'Cómo abrir una página de forma anónima',
            'text' => [
                'Haga clic en «Abrir de forma anónima» (el icono del escudo) en un resultado. La página se abre en una pestaña nueva.',
                'Si quiere abrir todos los resultados de forma anónima, active «Abrir los resultados de forma anónima» en la configuración. Así, cada enlace de un resultado pasa por SafeBrowse, y con «Abrir directamente» visita una página sin SafeBrowse.',
            ],
        ],
        'session' => [
            'title' => 'La sesión',
            'text' => [
                'El primer resultado que abre de forma anónima inicia una sesión. Cada uno de los siguientes se abre en la misma sesión, en una pestaña propia. Allí sigue navegando como siempre: sigue enlaces, rellena formularios, escribe una dirección nueva o un término de búsqueda en la barra de direcciones. Mientras tanto sigue protegido.',
                'Una sesión cuesta tokens de su clave de MetaGer; cuántos y por cuánto tiempo se indica antes de empezar. Mientras usa la sesión, se prolonga automáticamente. Cuando deja de usarla, termina sola poco después, o la cierra con «Cerrar sesión».',
                'Cuando la sesión termina, el navegador de nuestros servidores se borra con todo lo que contenía: páginas visitadas, cookies, inicios de sesión y archivos descargados. La siguiente sesión empieza con un navegador nuevo y vacío. Por eso, guarde antes los archivos descargados en su dispositivo desde «Descargas».',
            ],
        ],
        'views' => [
            'title' => 'Dos vistas',
            'text' => [
                '«Página» muestra una copia del sitio web sin sus scripts. En ella lee, desplaza, busca y copia como en cualquier página; sus clics y lo que escribe van al navegador de nuestros servidores. «Navegador completo» muestra ese navegador como imagen, con todas sus pestañas. Allí funciona todo, también los vídeos y las páginas que «Página» no puede mostrar. Cambia entre ambas en la barra superior.',
            ],
        ],
        'nojs' => [
            'title' => 'Sin JavaScript',
            'text' => [
                'Aunque tenga JavaScript desactivado en su navegador, «Abrir de forma anónima» abre la página en SafeBrowse. Verá entonces una copia de la página en la que funcionan los enlaces, los botones y los formularios. Se vuelve a cargar después de cada clic, pero no cambia por sí sola. Los vídeos, los diálogos de la página y la subida de archivos no funcionan de esta manera. Si su navegador bloquea las cookies, los resultados que abre desde una página de resultados siguen llegando a la misma sesión, también si sigue buscando desde allí.',
            ],
        ],
        'limits' => [
            'title' => 'Lo que «Abrir de forma anónima» no hace',
            'text' => [
                'El sitio web no le reconoce por su dispositivo, pero sí por lo que usted mismo le dice: si inicia sesión o introduce datos personales, sabe quién es usted. Si tampoco quiere mostrarse ante MetaGer, use MetaGer a través de Tor (véase más arriba).',
            ],
        ],
    ],
    'maps' => [
        'title' => 'Mapas MetaGer',
        '1' => 'Preservar la privacidad en la era de los gigantes mundiales de los datos también nos ha llevado a desarrollar <a href="https://maps.metager.de" target="_blank">https://maps.metager.de</a>: el (que sepamos) único planificador de rutas que ofrece una funcionalidad completa a través del navegador y la app sin almacenar la ubicación del usuario. Todo esto es verificable porque nuestro software es de código abierto. Para utilizar maps.metager.de, recomendamos nuestra versión rápida de la app. Puede descargar nuestras aplicaciones desde <a href="/app" target="_blank">aquí</a> (o, por supuesto, también desde Play Store).',
        '2' => 'También se puede acceder a esta función de mapas desde la búsqueda de MetaGer (y viceversa). Una vez que hayas buscado un término en MetaGer, verás un nuevo foco de búsqueda "Mapas" en la esquina superior derecha. Al hacer clic en él, accederá al mapa correspondiente.',
        '3' => 'Al cargarse, el mapa muestra los puntos (POIs = Points of Interest) encontrados por MetaGer, que también se enumeran en la columna de la derecha. Al hacer zoom, esta lista se adapta a la sección del mapa. Al pasar el ratón por encima de un marcador del mapa o de la lista, se resalta el punto correspondiente. Haga clic en "Detalles" para obtener más información sobre ese punto a partir de la base de datos que aparece a continuación.',
    ],
    'content' => [
        'title' => 'Contenido cuestionable / Protección de menores <a title="For easy help, click here" href="/help/easy-language/privacy-protection#eh-content" ><img class="easy-help-icon lm-only" src="/img/help-questionmark-icon-lm.svg"/><img class="easy-help-icon dm-only" src="/img/help-questionmark-icon-dm.svg"/></a>',
        'explanation' => [
            '1' => 'He recibido "hits" que no sólo me resultan molestos, sino que además contienen, en mi opinión, ¡contenido ilegal!',
            '2' => 'Si encuentra en internet algo que considera ilegal o perjudicial para los menores, puede ponerse en contacto con <a href="mailto:hotline@jugendschutz.net" target="_blank" rel="noopener">hotline@jugendschutz.net</a> por correo electrónico o visitar <a href="http://www.jugendschutz.net/" target="_blank" rel="noopener">www.jugendschutz.net</a> y rellenar el formulario de denuncia disponible allí. Es útil facilitar una breve nota sobre lo que considera inadmisible y cómo dio con ese contenido. También puede comunicarnos directamente los contenidos dudosos. Para ello, envíe un correo electrónico a nuestro responsable de protección de menores (<a href="mailto:jugendschutz@metager.de" target="_blank" rel="noopener">jugendschutz@metager.de</a>).',
        ],
    ],
    'easy-help' => 'Haciendo clic en el símbolo <a title="For easy help, click here" href="/hilfe/easy-language/privacy-protection" ><img class="easy-help-icon lm-only" src="/img/help-questionmark-icon-lm.svg"/><img class="easy-help-icon dm-only" src="/img/help-questionmark-icon-dm.svg"/></a> , accederá a una versión simplificada de la ayuda.',
    'privacy' => [
        'title' => "Anonimato y seguridad de los datos",
        '1' => 'Cookies de seguimiento, identificadores de sesión y direcciones IP <a title="For easy help, click here" href="/hilfe/easy-language/privacy-protection#eh-tracking" ><img class="easy-help-icon lm-only" src="/img/help-questionmark-icon-lm.svg"/><img class="easy-help-icon dm-only" src="/img/help-questionmark-icon-dm.svg"/></a>',
        '2' => 'Ninguno de ellos se utiliza, almacena, retiene o procesa de otro modo aquí en MetaGer (excepción: almacenamiento a corto plazo para la protección contra ataques de hackers y bots). Dado que consideramos este tema extremadamente importante, también hemos creado formas de ayudarte a alcanzar el máximo nivel de seguridad: el servicio oculto TOR de MetaGer y nuestro navegador anónimo.',
        '3' => "A continuación encontrará más información. Puede acceder a las funciones desde \"Servicios\" en la barra de navegación.",
    ],
];
