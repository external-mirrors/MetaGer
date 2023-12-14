<?php
return [
    'title' => 'Ayuda de MetaGer',
    'backarrow' => 'Devolver',
    'stopworte' => [
        '2' => 'Ejemplo: Usted está buscando un coche nuevo, pero definitivamente no un BMW. Así que tu aportación es:',
    ],
    'mehrwortsuche' => [
        '2' => 'Si esto no es suficiente para usted, tiene 2 opciones para hacer su búsqueda más precisa:',
        '4' => [
            'text' => 'Con la búsqueda de frases, también puede buscar combinaciones de palabras en lugar de palabras individuales. Para ello, basta con colocar las palabras que deben aparecer juntas entre comillas.',
        ],
    ],
    'urls' => [
        'title' => 'Excluir URLs',
        'explanation' => 'Puede excluir los resultados de búsqueda que contengan palabras específicas en sus enlaces de resultados utilizando "-url:" en su búsqueda.',
        'example_b' => '<i>mi búsqueda</i> -url:perro',
        'example_a' => 'Ejemplo: Desea excluir los resultados en cuyo enlace aparezca la palabra "perro":',
    ],
    'bang' => [
        'title' => 'Mapas MetaGer <a title="For easy help, click here" href="/hilfe/easy-language/services#eh-maps" ><img class="easy-help-icon lm-only" src="/img/help-questionmark-icon-lm.svg"/><img class="easy-help-icon dm-only" src="/img/help-questionmark-icon-dm.svg"/></a>',
        '1' => 'MetaGer admite de forma limitada un estilo de escritura conocido como sintaxis "bang". <br>Un "bang" siempre empieza con un signo de exclamación y no contiene espacios. Los ejemplos incluyen \'!twitter\' o \'!facebook\'.<br>Cuando se utiliza un !bang compatible en la consulta de búsqueda, aparece una entrada en nuestros consejos rápidos, lo que le permite continuar la búsqueda con el servicio respectivo (Twitter o Facebook) con sólo pulsar un botón.',
        '2' => '¿Por qué no se abre directamente?',
        '3' => 'Los "redireccionamientos" de !bang forman parte de nuestros consejos rápidos y requieren un "clic" adicional. Ésta fue una decisión difícil para nosotros, ya que hace que !bangs sea menos útil. Sin embargo, lamentablemente es necesario porque los enlaces a los que se produce la redirección no proceden de nosotros, sino de un tercero, DuckDuckGo.<p>Siempre nos aseguramos de que nuestros usuarios mantengan el control en todo momento. Por lo tanto, protegemos de dos maneras: En primer lugar, el término de búsqueda introducido nunca se transmite a DuckDuckGo, sólo el !bang. En segundo lugar, el usuario confirma explícitamente la visita al objetivo !bang. Lamentablemente, por motivos de personal, no podemos comprobar ni mantener todos estos !bangs nosotros mismos.',
    ],
    'searchinsearch' => [
        '1' => 'Se puede acceder a la función de búsqueda en el buscador mediante el botón "MÁS" situado en la parte inferior derecha del cuadro de resultados. Al hacer clic se abre un menú en el que "Guardar resultado" está en primer lugar. Con esta opción, el resultado respectivo se almacena en una memoria separada. El contenido de esta memoria se muestra a la derecha de los resultados, bajo los consejos rápidos (en las pantallas demasiado pequeñas, los resultados guardados no se muestran por falta de espacio). Allí puede filtrar o reordenar los resultados guardados por palabra clave. Puede encontrar más información sobre el tema "Búsqueda en la búsqueda" en el <a href="http://blog.suma-ev.de/node/225" target="_blank" rel="noopener"> blog de SUMA</a>.',
    ],
    'selist' => [
        'title' => 'Añade MetaGer a la lista de motores de búsqueda de tu navegador <a title="For easy help, click here" href="/hilfe/easy-language/functions#eh-selist"><img class="easy-help-icon lm-only" src="/img/help-questionmark-icon-lm.svg"/><img class="easy-help-icon dm-only" src="/img/help-questionmark-icon-dm.svg"/></a>',
        'explanation_b' => 'Algunos navegadores requieren que introduzcas una URL; debe ser "https://metager.de/meta/meta.ger3?input=%s" sin comillas. Puede generar usted mismo la URL buscando algo con metager.de y sustituyendo lo que hay detrás de "input=" en la barra de direcciones por %s. Si sigue teniendo problemas, póngase en contacto con nosotros: <a href="/kontalt" target="_blank" rel="noopener">Formulario de contacto</a>',
        'explanation_a' => 'Por favor, intente primero instalar el plugin actual. Para instalarlo, haga clic en el enlace situado justo debajo del cuadro de búsqueda. Su navegador ya debería haber sido detectado allí.',
    ],
    'searchfunction' => [
        'title' => "Funciones de búsqueda",
    ],
    'stopwords' => [
        'title' => 'Palabras clave <a title="For easy help, click here" href="/hilfe/easy-language/functions#eh-stopwordsearch"><img class="easy-help-icon lm-only" src="/img/help-questionmark-icon-lm.svg"/><img class="easy-help-icon dm-only" src="/img/help-questionmark-icon-dm.svg"/></a>',
        '3' => "coche nuevo -bmw",
        '2' => "Ejemplo: Buscas un coche nuevo, pero no un BMW. Su entrada sería:",
        '1' => "Si desea excluir los resultados de la búsqueda en MetaGer que contengan palabras específicas (palabras de exclusión / stopwords), puede hacerlo anteponiendo a estas palabras un signo menos.",
    ],
    'key' => [
        'title' => 'Añadir clave MetaGer <a title="For easy help, click here" href="/hilfe/easy-language/functions#eh-keyexplain"><img class="easy-help-icon lm-only" src="/img/help-questionmark-icon-lm.svg"/><img class="easy-help-icon dm-only" src="/img/help-questionmark-icon-dm.svg"/></a>',
        '1' => 'La clave MetaGer se configura automáticamente en tu navegador y se utiliza. No necesitas hacer nada más. Si quieres utilizar la llave MetaGer en otros dispositivos, hay varias maneras de configurar la llave MetaGer:',
        '2' => 'Código de inicio de sesión <br>En la <a href = "/keys/key/enter">página de gestión</a> de la llave MetaGer, puede utilizar el código de inicio de sesión para añadir su llave a otro dispositivo. Simplemente introduzca el código numérico de seis dígitos al iniciar sesión. El código de acceso sólo puede utilizarse una vez y sólo es válido mientras la ventana esté abierta.',
        '3' => 'Copiar URL <br>Cuando estás en la <a href = "/keys/key/enter">página de gestión</a> de la llave MetaGer, hay una opción para copiar una URL. Esta URL se puede utilizar para guardar todos los ajustes de MetaGer, incluyendo la llave MetaGer, en otro dispositivo.',
        '4' => 'Guardar archivo <br>Cuando estás en la <a href = "/keys/key/enter">página de gestión</a> de la llave MetaGer, hay una opción para guardar un archivo. Esto guarda su clave MetaGer como un archivo. A continuación, puede utilizar este archivo en otro dispositivo para iniciar sesión con su clave.',
        '5' => 'Escanear código QR <br>Alternativamente, también puede escanear el código QR que aparece en la <a href = "/keys/key/enter">página de gestión</a> para iniciar sesión con otro dispositivo.',
        '6' => 'Introducir manualmente la clave MetaGer <br>También puedes introducir manualmente la clave en otro dispositivo.',
        'colors' => [
            'title' => 'Llave MetaGer de color',
            '1' => 'Para reconocer fácilmente si está buscando sin anuncios, hemos dado a nuestros símbolos clave colores. A continuación se explican los colores correspondientes:',
            'grey' => 'Gris: No ha configurado una clave. Está utilizando la búsqueda libre.',
            'red' => 'Rojo: Si el símbolo de tu llave es rojo, significa que esta llave está vacía. Ha agotado todas las búsquedas sin publicidad. Puedes recargar la llave en la página de gestión de llaves.',
            'green' => 'Verde: Si el símbolo de la llave es verde, significa que está utilizando una llave cargada.',
            'yellow' => 'Amarilla: Si ves una llave amarilla, aún te quedan 30 fichas de saldo. Tus búsquedas se están agotando. Se recomienda recargar la llave pronto.',
        ],
    ],
    'multiwordsearch' => [
        'title' => 'Búsqueda multipalabra <a title="For easy help, click here" href="/hilfe/easy-language/functions#eh-severalwords"><img class="easy-help-icon lm-only" src="/img/help-questionmark-icon-lm.svg"/><img class="easy-help-icon dm-only" src="/img/help-questionmark-icon-dm.svg"/></a>',
        '4' => [
            'example' => '"la mesa redonda"',
            'text' => "Con una búsqueda de frases, puede buscar combinaciones de palabras en lugar de palabras sueltas. Basta con entrecomillar las palabras que deben aparecer juntas.",
        ],
        '3' => [
            'example' => '"la" "mesa" "redonda"',
            'text' => "Si quiere asegurarse de que las palabras de su búsqueda también aparezcan en los resultados, debe entrecomillarlas.",
        ],
        '2' => "Si esto no le basta, tiene 2 opciones para precisar su búsqueda:",
        '1' => "Al buscar más de una palabra en MetaGer, intentamos proporcionar automáticamente resultados en los que aparezcan todas las palabras o se acerquen lo máximo posible.",
    ],
    'exactsearch' => [
        'title' => 'Búsqueda exacta <a title="For easy help, click here" href="/hilfe/easy-language/functions#exactsearch"><img class="easy-help-icon lm-only" src="/img/help-questionmark-icon-lm.svg"/><img class="easy-help-icon dm-only" src="/img/help-questionmark-icon-dm.svg"/></a>',
        '1' => "Si desea encontrar una palabra específica en los resultados de búsqueda de MetaGer, puede anteponer a esa palabra un signo más. Cuando se utiliza el signo más y las comillas, se busca una frase exactamente como la has introducido.",
        '2' => "Ejemplo: S",
        '3' => 'Ejemplo: ',
        'example' => [
            '1' => "+palabraejemplo",
            '2' => '+"frase de ejemplo"',
        ],
    ],
    'easy-help' => 'Haciendo clic en el símbolo <a title="For easy help, click here" href="/hilfe/easy-language/services" ><img class="easy-help-icon lm-only" src="/img/help-questionmark-icon-lm.svg"/><img class="easy-help-icon dm-only" src="/img/help-questionmark-icon-dm.svg"/></a> , accederá a una versión simplificada de la ayuda.',
];
