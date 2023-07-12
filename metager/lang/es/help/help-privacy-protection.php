<?php
return [
    'title' => 'Ayuda de MetaGer',
    'backarrow' => 'Devolver',
    'datenschutz' => [
        'title' => 'Anonimato y seguridad de los datos',
        '1' => 'Cookies de seguimiento, identificadores de sesión y direcciones IP',
        '2' => 'Nada de esto se utiliza, almacena, retiene o procesa de alguna manera aquí en MetaGer (excepción: almacenamiento a corto plazo contra ataques de hackers y bots). Como consideramos que este tema es extremadamente importante, también hemos creado opciones que pueden ayudarte a conseguir el máximo nivel de seguridad: el servicio oculto TOR de MetaGer y nuestro servidor proxy anonimizador.',
        '3' => 'Encontrará más información a continuación. Se puede acceder a las funciones en "Servicios" en la barra de navegación.',
    ],
    'tor' => [
        'title' => 'Servicio oculto de Tor',
        '1' => 'MetaGer lleva muchos años ocultando y no almacenando las direcciones IP. Sin embargo, estas direcciones son visibles temporalmente en el servidor de MetaGer mientras se ejecuta una búsqueda: por lo que si MetaGer se viera comprometido alguna vez, ese atacante podría leer y almacenar sus direcciones. Para satisfacer las más altas necesidades de seguridad, mantenemos una instancia de MetaGer en la red Tor: el servicio MetaGer-TOR-hidden - accesible a través de: <a href="/tor/" target="_blank" rel="noopener">https://metager.de/tor/.</a> Para utilizarlo, necesitas un navegador especial, que puedes descargar de <a href="https://www.torproject.org/" target="_blank" rel="noopener">https://www.torproject.org/.</a> ',
        '2' => 'A continuación, se puede acceder a MetaGer en el navegador Tor en: http://metagerv65pwclop2rsfzg4jwowpavpwd6grhhlvdgsswvo6ii4akgyd.onion .',
    ],
    'proxy' => [
        'title' => 'Anonimizar el servidor proxy de MetaGer',
        '1' => 'Para utilizarlo, lo único que hay que hacer en la página de resultados de MetaGer es hacer clic en "OPEN ANONYMOUS" en la parte inferior del resultado. A continuación, su solicitud se dirigirá al sitio web de destino a través de nuestro servidor proxy de anonimización y sus datos personales permanecerán totalmente protegidos. Importante: si sigue los enlaces de las páginas a partir de este punto, seguirá protegido por el proxy. Sin embargo, no se puede ir a una nueva dirección en el campo de dirección anterior. En este caso, perderá la protección. También puede ver si sigue protegido en el campo de la dirección. Muestra: https://proxy.suma-ev.de/?url=hier es la dirección actual.',
    ],
    'maps' => [
        'title' => 'MetaGer Maps',
        '1' => 'Salvaguardar la privacidad en la era de los pulpos de datos globales también nos ha llevado a desarrollar <a href="https://maps.metager.de" target="_blank">https://maps.metager.de:</a> El único planificador de rutas (hasta donde sabemos) que ofrece una funcionalidad completa a través del navegador y la aplicación, sin almacenar las ubicaciones del usuario.  Todo esto es verificable, porque nuestro software es de código abierto. Para utilizar maps.metager.de, recomendamos nuestra versión de aplicación rápida. Puedes descargar nuestras aplicaciones en <a href="/app" target="_blank">Get an App</a> (o, por supuesto, a través de Play Store).',
        '2' => 'Esta función de mapa también se puede llamar desde la búsqueda de MetaGer (y viceversa). En cuanto hayas buscado un término en MetaGer, verás un nuevo foco de búsqueda "Mapas" en la parte superior derecha.  Si hace clic en él, accederá a un mapa asociado. .',
        '3' => 'Después de llamar al mapa, muestra los puntos encontrados por MetaGer (POIs = Puntos de Interés) también mostrados en la columna de la derecha. Al acercarse, esta lista se adapta a la sección del mapa.  Si mantiene el ratón sobre un marcador en el mapa o en la lista, se resalta el correspondiente homólogo.  Haga clic en "Detalles" para obtener información más detallada sobre este punto en la base de datos que aparece a continuación.',
    ],
    'content' => [
        'title' => 'Contenido cuestionable / protección de menores',
        'explanation' => [
            '1' => 'He recibido "hits" que no sólo me resultan molestos, sino que, en mi opinión, ¡tienen contenido ilegal!',
            '2' => 'Si encuentra algo en Internet que considere ilegal o perjudicial para los menores, puede ponerse en contacto con <a href="mailto:hotline@jugendschutz.net" target="_blank" rel="noopener">hotline@jugendschutz.net</a> o ir a <a href="http://www.jugendschutz.net/" target="_blank" rel="noopener">www.jugendschutz.net</a> y rellenar el formulario de denuncia. Tiene sentido exponer brevemente lo que considera ilegal y cómo ha llegado a esta oferta. También puede informarnos directamente de los contenidos dudosos. Para ello, envíe un correo electrónico a nuestro responsable de protección de menores<a href="mailto:jugendschutz@metager.de" target="_blank" rel="noopener">(jugendschutz@metager.de)</a>.',
        ],
    ],
];