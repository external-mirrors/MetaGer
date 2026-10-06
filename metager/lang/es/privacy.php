<?php
return [
    'title' => 'Protección de Datos',
    'introduction' => 'En aras de la máxima transparencia, le indicamos qué datos recogemos de usted y cómo los utilizamos. La protección de sus datos es importante para nosotros y debería serlo también para usted. <strong>Lea atentamente esta declaración; es en su interés.</strong>',
    'responsible_party' => [
        'title' => 'Responsables y personas de contacto',
        'description' => 'MetaGer y los servicios relacionados son operados por <a href="https://suma-ev.de">SUMA-EV</a>, que es también el autor de esta declaración. En esta declaración, "nosotros" se refiere generalmente a SUMA-EV. Puede encontrar nuestros datos de contacto en nuestro <a href=":link_impress">Pie de imprenta</a>. Puede ponerse en contacto con nosotros por correo electrónico utilizando nuestro formulario de contacto <a href=":link_contact"></a> .',
    ],
    'contexts' => [
        'metager' => [
            'title' => 'Utilización del motor de búsqueda MetaGer',
            'description' => 'Al utilizar nuestro motor de búsqueda web MetaGer a través de su formulario web o de su interfaz OpenSearch, se generan los siguientes datos:',
            'query' => 'Como parte integrante de la metabúsqueda, la consulta de búsqueda se transmite a nuestros socios para obtener resultados de búsqueda que se mostrarán en la página de resultados. Los resultados recibidos, incluido el término de búsqueda, se conservan para su visualización durante unas horas.',
            'preferences' => 'Utilizamos estos datos (por ejemplo, la configuración de idioma) para responder a la consulta de búsqueda correspondiente. Almacenamos algunos de estos datos de forma no personal con fines estadísticos.',
            'suggest' => 'Si las sugerencias de búsqueda están habilitadas para la barra de direcciones de su navegador (opcional), necesitamos almacenar su configuración de sugerencias en nuestro servidor. La clave será una cadena concatenada de su dirección IP, su agente de usuario y la cabecera accept-language de su navegador con hash sha1. Si la configuración está desactivada (por defecto) no se almacenará nada.',
        ],
        'title' => 'Datos entrantes por contexto',
        'contact' => [
            'title' => 'Utilización del formulario de contacto',
            'description' => 'Al utilizar el formulario de contacto de MetaGer, se generan los siguientes datos, que almacenamos como referencia hasta 2 meses después de la finalización de su solicitud:',
            'contact' => 'Se almacenará con fines de referencia hasta 2 meses después de la finalización de su solicitud.',
        ],
        'donate' => [
            'title' => 'Utilización del formulario de donación',
            'description' => 'Los siguientes datos transmitidos en el formulario de donación se almacenarán durante 2 meses para su procesamiento:',
            'contact' => 'Utilizamos estos datos exclusivamente para posibles consultas y en ningún caso los transmitimos a terceros.',
            'payment' => 'Los datos de pago sólo se utilizarán para procesar la donación y en ningún caso se transmitirán a terceros. Por motivos fiscales, estamos obligados a conservar y, por tanto, guardar estos datos durante 10 años. Después se borrarán automáticamente y no se seguirán procesando.',
            'message' => 'El mensaje que introduzca aquí nos será transmitido y se tendrá en cuenta a la hora de procesar su donación.',
        ],
        'key' => [
            'title' => 'Comprobar clave MetaGer',
            'contact' => 'Utilizamos estos datos exclusivamente para posibles consultas o para la facturación y en ningún caso los transmitimos a terceros.',
            'payment' => 'Los datos de pago sólo se utilizarán para procesar la donación y en ningún caso se transmitirán a terceros. Por motivos fiscales, estamos obligados a conservar y, por tanto, guardar estos datos durante 10 años. Después se borrarán automáticamente y no se seguirán procesando.',
        ],
        'suma' => [
            'title' => 'Uso del sitio web <a href="https://suma-ev.de">suma-ev.de</a>',
            'description' => 'Al visitar los sitios web del dominio "suma-ev.de", se recopilan los siguientes datos, que se almacenan durante un máximo de una semana:',
            'function' => 'Al visitar los sitios web del dominio "suma-ev.de", se recopilan los siguientes datos, que se almacenan durante un máximo de una semana:',
            'other' => 'En los demás sitios web de nuestros dominios, sólo procesamos los datos recogidos para responder a las consultas y en el ámbito de los demás puntos de esta declaración de protección de datos.',
            'startpage' => 'En la página de inicio de nuestro servicio MetaGer, utilizamos el agente de usuario que nos ha transmitido para mostrarle las instrucciones de instalación del complemento adecuado para su navegador.',
        ],
        'newsletter' => [
            'title' => 'Suscríbase al boletín SUMA-EV',
            'description' => 'Para mantenerle informado sobre nuestras actividades, le ofrecemos un boletín informativo por correo electrónico. Almacenamos los siguientes datos hasta que cancele su suscripción:',
            'contact' => 'Utilizamos estos datos exclusivamente para enviarle nuestro boletín y en ningún caso los transmitimos a terceros.',
        ],
        'maps' => [
            'title' => 'Uso de Maps.MetaGer.de',
            'description' => 'Cuando se utiliza el servicio de mapas MetaGer, se generan los siguientes datos:',
            'ip' => 'Utilizamos su dirección IP para estimar una buena ubicación de partida en la que centrar inicialmente el mapa. Para ello, su dirección IP se procesa localmente. Los resultados no se almacenan en ninguna parte y se eliminarán inmediatamente después de su solicitud.',
        ],
        'proxy' => [
            'title' => 'Uso del proxy de anonimización',
            'description' => 'Al utilizar el proxy de anonimización, se generan los siguientes datos:',
        ],
        'quote' => [
            'title' => 'Utilización de la búsqueda de citas',
            'description' => 'El término de búsqueda introducido se utiliza para buscar resultados en la base de datos de citas. A diferencia de las búsquedas en Internet con MetaGer, no es necesario transmitir el término de búsqueda a terceros, ya que la base de datos de citas se encuentra en nuestro servidor. No se guardarán ni transmitirán otros datos.',
        ],
        'asso' => [
            'title' => 'Uso del asociador',
            'description' => 'El asociador utiliza el término de búsqueda para determinar y mostrar los términos asociados a él. No se guardarán ni transmitirán otros datos.',
        ],
        'mapsapp' => [
            'title' => 'Uso de la aplicación MetaGer',
            'description' => 'Utilizar la aplicación MetaGer es lo mismo que utilizar MetaGer a través de un navegador web.',
        ],
        'plugin' => [
            'title' => 'Utilización del plugin MetaGer',
            'description' => 'Cuando se utiliza el plugin MetaGer, se generan los siguientes datos:',
        ],
        'membership' => [
            'title' => 'Solicitar y gestionar una afiliación a SUMA-EV',
            'description' => 'Al solicitar la afiliación a SUMA-EV, y durante todo el periodo de vigencia de la misma, se generan los siguientes datos:',
            'contact' => 'En el caso de las personas físicas, el nombre y la dirección de correo electrónico; en el caso de las organizaciones, el nombre de la organización, su tamaño aproximado y el nombre y la dirección de correo electrónico de una persona de contacto. La dirección postal es opcional y, si se facilita, se utiliza principalmente para emitir un recibo de donación. En ningún caso cedemos estos datos a terceros.',
            'payment' => 'Utilizamos tus datos de pago exclusivamente para el cobro periódico de tu cuota de socio. En el apartado «Gestión de pagos» se describe cómo los tratamos, quién los recibe y durante cuánto tiempo los conservamos.',
            'reduction' => 'Si solicitas una cuota de afiliación reducida, tratamos el justificante que subas (por ejemplo, un extracto de transferencias) con el único fin de verificar tu derecho a la reducción. Dicho justificante puede contener datos especialmente sensibles según el artículo 9 del RGPD; por lo tanto, solo lo conservamos durante el tiempo que sea necesario para dicha verificación y no lo cedemos a terceros.',
            'crm' => 'Para gestionar las afiliaciones, utilizamos nuestro propio sistema de administración de afiliaciones, gestionado también por SUMA-EV. Los datos mencionados anteriormente se almacenan en dicho sistema mientras dure tu afiliación y, posteriormente, durante los plazos de conservación establecidos por la ley. Tu afiliación también está vinculada a una clave MetaGer, a través de la cual tu cuota de afiliación se abona como crédito de uso para MetaGer (véase «Comprobar la clave MetaGer»).',
            'cookies' => 'El formulario de solicitud y el portal de miembros no instalan cookies con solo abrirlos. Solo cuando envías un formulario o inicias sesión en el portal de miembros, instalamos una cookie de sesión técnicamente necesaria, para poder, por ejemplo, mostrarte los errores de introducción de datos o mantenerte conectado. Esta cookie caduca tras dos horas de inactividad y se elimina cuando cierras la sesión. Si abres el formulario a través de un enlace que indica un idioma diferente al configurado en tu navegador, recordamos ese idioma en una cookie que se elimina al cerrar el navegador. La base jurídica es el artículo 25, apartado 2, n.º 2, de la TDDDG.',
        ],
        'payments' => [
            'title' => 'Gestión de pagos',
            'description' => 'Los pagos a SUMA-EV —actualmente las cuotas de socio y, en el futuro, también las donaciones y las claves de MetaGer— se gestionan a través de nuestro propio sistema de pagos, gestionado asimismo por SUMA-EV. Se generan los siguientes datos:',
            'ip' => 'Solo en caso de pago mediante domiciliación bancaria SEPA: como parte del registro de tu orden de domiciliación (véase más abajo). En los demás casos, no se almacena.',
            'useragent' => 'Solo en caso de pago mediante domiciliación bancaria SEPA: como parte del registro de tu orden de domiciliación (véase más abajo). En los demás casos, no se almacena.',
            'contact' => 'Recibimos tu nombre y tu dirección de correo electrónico a través del servicio por el que estás pagando (por ejemplo, la solicitud de afiliación). Los utilizamos para enviarte correos electrónicos relacionados con tu pago, como datos para la transferencia bancaria, enlaces de pago o recordatorios de pago.',
            'payment' => 'En función de la forma de pago, el titular de la cuenta y el IBAN, una referencia a tu cuenta de PayPal o a tu tarjeta en el proveedor correspondiente, así como el importe, la hora y el estado de cada pago.',
            'methods' => 'Nosotros mismos tramitamos los adeudos directos SEPA y las transferencias bancarias; los adeudos directos se envían a nuestro banco indicando el titular de la cuenta, el IBAN, el importe y la referencia de la autorización. Al pagar con PayPal, introduces tus datos directamente en PayPal (Europe) S.à r.l. et Cie, S.C.A.; solo cuando eliges PayPal como método de pago, la página de pago carga el código de programa de PayPal, lo que proporciona a PayPal tu dirección IP y le permite instalar sus propias cookies. Al pagar con tarjeta o mediante Wero, introduces tus datos directamente en VR Payment GmbH. Nunca recibimos números de tarjeta ni datos de acceso a PayPal o Wero, sino únicamente una referencia que nos permite cobrar las cuotas periódicas. La base jurídica es el artículo 6, apartado 1, letra b), del RGPD.',
            'mandate' => 'Si nos das una autorización de domiciliación bancaria SEPA por internet, guardamos un registro del contenido de la autorización (titular de la cuenta, IBAN, referencia de la autorización, importe e intervalo, así como el texto de la autorización que se te mostró), junto con la hora en que se otorgó, tu dirección IP y tu agente de usuario. Necesitamos este registro para demostrar a nuestro banco que usted ha otorgado la autorización en caso de que se impugne un adeudo directo; solo en ese caso se lo mostramos al banco. La base jurídica es el artículo 6, apartado 1, letra b) del RGPD y nuestro interés legítimo en poder demostrar la autorización (artículo 6, apartado 1, letra f) del RGPD).',
            'cookies' => 'Las propias páginas de pago no instalan cookies al abrirlas. Solo si los datos introducidos están incompletos o son incorrectos, instalamos temporalmente una cookie de sesión técnicamente necesaria para mostrarte los errores; esta se elimina tan pronto como se han mostrado (artículo 25, apartado 2, n.º 2, de la TDDDG).',
            'retention' => 'Si un pago no se lleva a cabo porque lo abandonas, eliminamos tu nombre y tu dirección de correo electrónico una semana después de que haya caducado el proceso de pago. Si, por ejemplo, se rechaza una solicitud de afiliación antes de que se haya cobrado nada, eliminamos inmediatamente tus datos de pago y el registro de la autorización. El resto de datos de pago y registros de autorizaciones se conservan mientras exista la relación de pago y, posteriormente, durante los plazos de conservación legales (hasta 10 años).',
        ],
    ],
    'data' => [
        'message' => 'Mensaje',
        'payment' => 'Datos de pago',
        'ip' => 'Dirección IP',
        'useragent' => 'Usuario-Agente',
        'query' => 'Consulta de búsqueda',
        'preferences' => 'Preferencias del usuario',
        'contact' => 'Datos de contacto',
        'referrer' => 'el remitente que envió',
        'gps' => 'Datos de localización',
        'optional' => 'opcional',
        'unused' => 'No se guardará ni compartirá.',
        'error' => 'Informe de errores',
        'reduction' => 'Demostración de la reducción',
    ],
    'principles' => [
        'title' => 'Principios',
        'description' => 'Como asociación sin ánimo de lucro, estamos comprometidos con el libre acceso al conocimiento. Como sabemos que la libre investigación no es compatible con la vigilancia masiva, también nos tomamos muy en serio la protección de datos. Siempre hemos procesado únicamente los datos absolutamente necesarios para el funcionamiento de nuestros servicios. La protección de datos es siempre nuestra norma. No realizamos profiling, es decir, la creación automática de perfiles de usuario.',
    ],
    'hosting' => [
        'title' => 'Alojamiento',
        'description' => 'Nuestros servicios son administrados por nosotros, SUMA-EV, y operados en hardware alquilado a Hetzner Online GmbH.',
    ],
    'description' => [
        'title' => 'Descripción de los datos resultantes',
        'ip' => [
            'title' => 'Dirección de protocolo de Internet',
            'description' => 'La dirección de protocolo de Internet (en lo sucesivo, IP) es obligatoria para poder utilizar servicios web como MetaGer. Esta IP, en combinación con una fecha - similar a un número de teléfono - identifica claramente un acceso a Internet y a su propietario. En general, los tres primeros bloques (de un total de cuatro) de una IP no son personales. Si se acortan los bloques posteriores de la IP, la dirección acortada identifica el área geográfica aproximada alrededor de la conexión a Internet.',
            'example_full' => 'Ejemplos (dirección IP completa)',
            'example_partial' => 'Ejemplos (sólo los dos primeros bloques)',
        ],
        'useragent' => [
            'title' => 'Identificador del agente de usuario',
            'description' => 'Cuando accede a un sitio web, su navegador envía automáticamente un identificador, normalmente con datos sobre el navegador y el sistema operativo utilizados. Este identificador del navegador (el llamado agente de usuario) puede ser utilizado por los sitios web, por ejemplo, para reconocer dispositivos móviles y presentarles una salida personalizada.',
            'example' => 'Ejemplo',
        ],
        'payment' => [
            'title' => 'Datos de pago',
            'description' => 'Al comprar una clave MetaGer, se requieren diferentes datos de pago en función del proveedor de pago',
            'examples' => 'Ejemplos',
            'name' => 'Max Mustermann, mail@example.com',
            'card' => 'Últimos dígitos del número de la tarjeta de crédito',
        ],
        'query' => [
            'title' => 'Consulta de búsqueda introducida',
            'description' => 'Los términos de búsqueda introducidos son absolutamente necesarios para una búsqueda en Internet. Por regla general, no se pueden obtener datos personales de ellos; entre otras cosas, porque no tienen una estructura fija.',
            'examples' => 'Ejemplos',
            'example_1' => 'consumo de agua ducha',
            'example_2' => 'Letra En un árbol un cuco',
        ],
        'preferences' => [
            'title' => 'Preferencias del usuario',
            'description' => 'Además de los datos de los formularios y los agentes de usuario, el navegador suele transferir otros datos. Esto incluye la selección de idioma, la configuración de búsqueda, las cabeceras de aceptación, las cabeceras de no rastreo y mucho más.',
        ],
        'contact' => [
            'title' => 'Datos de contacto',
            'description' => 'A continuación encontrará el nombre (anterior y posterior) y la dirección de correo electrónico que nos ha facilitado. Estos datos los utilizamos exclusivamente para responder a sus preguntas y para enviarle información adicional.',
        ],
        'message' => [
            'title' => 'Mensaje',
            'description' => 'El mensaje introducido aquí nos será transmitido y utilizado para tramitar su solicitud.',
        ],
        'error' => [
            'title' => 'Informe de error (traza de pila)',
            'description' => 'Cuando se produce un error técnico en nuestra aplicación, se genera automáticamente una descripción del error junto con un seguimiento de la pila. Esto nos permite saber en qué parte del código fuente se ha producido el error para poder solucionarlo.',
        ],
        'reduction' => [
            'title' => 'Justificante para una cuota de socio reducida',
            'description' => 'Un documento que debes subir para acreditar tu derecho a una cuota de socio reducida, por ejemplo, un aviso que confirme la recepción de transferencias.',
        ],
    ],
    'base' => [
        'title' => 'Base jurídica del tratamiento',
        'description' => 'La base jurídica para el tratamiento de sus datos personales identificables es el art. 6 (1) (a) GDPR si da su consentimiento al tratamiento mediante el uso de nuestros servicios, o el Art. 6 (1) (f) GDPR si el tratamiento es necesario para proteger nuestros intereses legítimos, u otra base jurídica si se lo notificamos por separado.',
    ],
    'rights' => [
        'title' => 'Sus derechos como usuario (y nuestras obligaciones)',
        'description' => 'Para que usted también pueda proteger sus datos personales, le aclaramos (de acuerdo con el Art. 13 DSGVO) que tiene los siguientes derechos:',
        'information' => [
            'title' => 'Derecho a facilitar información',
            'description' => 'Usted tiene derecho (Art. 15 GDPR) a solicitarnos información en cualquier momento sobre si tenemos datos suyos (metager.de y SUMA-EV) sobre usted y, en caso afirmativo, cuáles. Le enviaremos lo antes posible, es decir, en el plazo de unos pocos días, una copia completa de los datos que hemos almacenado o almacenamos sobre usted de conformidad con el artículo 15, apartado 3, subsección 1 GDPR. Para ello, preferimos el método electrónico de conformidad con el artículo 15, apartado 3, subapartado 3 GDPR; Para ello, guardaremos su dirección de correo electrónico mientras dure el procesamiento. Por favor, infórmenos si desea específicamente la información en papel.',
        ],
        'correction' => [
            'title' => 'Derecho a corrección y suplementos',
            'description' => 'De conformidad con el artículo 16 del GDPR. Si hemos almacenado datos incorrectos sobre usted, puede solicitar que se corrijan. Esto también se aplica a los componentes que faltan, aquí usted tiene el derecho de complementar.',
        ],
        'deletion' => [
            'title' => 'Derecho de supresión',
            'description' => 'De conformidad con el artículo 17 del RGPD',
        ],
        'processing' => [
            'title' => 'Derecho a la restricción del tratamiento',
            'description' => 'De conformidad con el artículo 18 del RGPD; Por ejemplo, si nos ha pedido que suprimamos o modifiquemos datos sobre usted, puede imponernos una prohibición de tratamiento durante el tiempo que tardemos en hacerlo. Esto es posible independientemente de si finalmente modificamos, suprimimos, etc. los datos en cuestión.',
        ],
        'complaint' => [
            'title' => 'Derecho a reclamar',
            'description' => 'De conformidad con el artículo 13, apartado 2, letra d) del GDPR, puede presentar una reclamación ante el delegado de protección de datos del Estado federado de Baja Sajonia. En línea: <a href="https://www.lfd.niedersachsen.de/startseite/">Delegado de Protección de Datos</a>',
        ],
        'opposition' => [
            'title' => 'Derecho de oposición al tratamiento',
            'description' => 'De acuerdo con el artículo 21 del GDPR; por ejemplo, si usted está en una lista y quiere estar allí, aún puede prohibir el tratamiento o procesamiento posterior de esos datos.',
        ],
        'portability' => [
            'title' => 'Derecho a la portabilidad de datos',
            'description' => 'De acuerdo con el artículo 20 del GDPR; Esto significa que estamos obligados a proporcionarle los datos solicitados de forma legible, posiblemente legible por máquina o de forma habitual para que usted pueda hacer que los datos sean accesibles a otra persona tal como son (para transferir).',
        ],
        'obligation_notify' => [
            'title' => 'Obligación de notificación en relación con la rectificación o supresión de datos personales o la limitación del tratamiento:',
            'description' => 'De conformidad con el artículo 19 del GDPR; si hubiéramos hecho accesibles a terceros los datos que usted nos ha confiado (cosa que nunca hacemos), estaríamos obligados a informarles de que, a petición suya, los habríamos suprimido, modificado, etc.',
        ],
        'perception' => 'Para ejercer estos derechos, basta con ponerse en contacto con nosotros a través de nuestro <a href=":contact_link">formulario de contacto</a></b>. Si prefiere el formulario por carta, envíenos un correo a la dirección de nuestra oficina:',
    ],
    'changes' => [
        'title' => 'Cambios en esta declaración',
        'description' => 'Al igual que nuestras ofertas, esta declaración de protección de datos también está sujeta a cambios constantes. Por ello, le recomendamos que vuelva a leerla con regularidad.',
        'date' => 'Esta versión de nuestra política de privacidad tiene fecha: :date',
    ],
    'monitoring' => [
        'title' => 'Seguimiento de errores y supervisión de aplicaciones',
        'description' => 'Para garantizar la fiabilidad de nuestros servicios, utilizamos la herramienta de seguimiento de errores de código abierto GlitchTip, tanto en el backend como en el frontend de nuestras aplicaciones. GlitchTip se ejecuta exclusivamente en nuestra propia infraestructura; no se comparten ni se transmiten datos a ningún proveedor externo de análisis o supervisión.',
        'collected' => [
            'title' => '¿Qué datos se generan?',
            'description' => 'Cuando se produce un error en nuestra aplicación web o en nuestros servidores, recopilamos automáticamente:',
            'error' => 'Una descripción del error y un seguimiento de la pila que identifique la ubicación afectada en el código.',
            'useragent' => 'Tipo de navegador, sistema operativo y tipo aproximado de dispositivo, determinados a partir de tu agente de usuario.',
            'url' => 'La dirección (URL) de la página o función en la que se produjo el error.',
        ],
        'not_collected' => [
            'title' => '¿Qué datos no se generan?',
            'description' => 'Tu dirección IP se elimina antes de que se transmita a GlitchTip y nunca llega al sistema. No asociamos los informes de errores con ninguna persona ni cuenta de usuario, ni siquiera en el caso de los usuarios que han iniciado sesión. Tampoco recopilamos contraseñas, datos de pago ni el contenido de ningún formulario o mensaje que envíes a través de nuestros servicios.',
        ],
        'retention' => 'Almacenamos los informes de errores durante 30 días; después se eliminan automáticamente.',
        'base' => 'La base jurídica de este tratamiento es nuestro interés legítimo en prestar nuestros servicios de forma fiable y segura (art. 6, apartado 1, letra f) del RGPD).',
    ],
];
