<?php
return [
    'title' => 'Ayuda de MetaGer',
    'backarrow' => 'Devolver',
    'suchfunktion' => [
        'title' => 'Funciones de búsqueda',
    ],
    'stopworte' => [
        'title' => 'Palabras clave',
        '1' => 'Si quiere excluir de los resultados de búsqueda de MetaGer aquellos en los que aparecen determinadas palabras (palabras de exclusión / stop words), puede conseguirlo marcando estas palabras con un signo de menos.',
        '2' => 'Ejemplo: Usted está buscando un coche nuevo, pero definitivamente no un BMW. Así que tu aportación es:',
        '3' => 'coche nuevo -bmw',
    ],
    'mehrwortsuche' => [
        'title' => 'Búsqueda de varias palabras',
        '1' => 'Cuando busca en MetaGer más de una palabra, intentamos automáticamente ofrecerle resultados que contengan todas las palabras, o que se aproximen lo más posible a ellas.',
        '2' => 'Si esto no es suficiente para usted, tiene 2 opciones para hacer su búsqueda más precisa:',
        '3' => [
            'example' => '"la" "mesa" "redonda"',
            'text' => 'Si quiere estar seguro de que las palabras de su búsqueda también aparecen en los resultados, debe ponerlas entre comillas.',
        ],
        '4' => [
            'example' => '"la mesa redonda"',
            'text' => 'Con la búsqueda de frases, también puede buscar combinaciones de palabras en lugar de palabras individuales. Para ello, basta con colocar las palabras que deben aparecer juntas entre comillas.',
        ],
    ],
    'urls' => [
        'title' => 'Excluir URLs',
        'explanation' => 'Puede excluir los resultados de la búsqueda cuyos enlaces contengan determinadas palabras utilizando "-url:" en su búsqueda.',
        'example_b' => '<i>mi búsqueda</i> -url:hund',
        'example_a' => 'Ejemplo: No quiere ningún resultado en el que aparezca la palabra "perro" en el enlace del resultado:',
    ],
    'bang' => [
        'title' => '!bangs',
        '1' => 'MetaGer admite en cierta medida una notación que suele denominarse sintaxis "bang".<br>Este tipo de "!bang" siempre comienza con un signo de exclamación y no contiene espacios. Algunos ejemplos son "!twitter" o "!facebook".<br>Si en la consulta de búsqueda se utiliza un "bang", que nosotros admitimos, aparece una entrada en nuestros Quicktips que puede utilizarse para continuar la búsqueda con el servicio correspondiente (en este caso, Twitter o Facebook) con sólo pulsar un botón.</br></br>',
    ],
    'faq' => [
        '18' => [
            'h' => '¿Por qué no se abren directamente los flequillos?',
            'b' => 'Los "redireccionamientos" de !bang forman parte de nuestros Quicktips y requieren un "clic" adicional. Esta fue una decisión difícil para nosotros, ya que hace que el !bang sea menos útil. Sin embargo, lamentablemente es necesario porque los enlaces a los que se redirige no provienen de nosotros, sino de un proveedor externo, DuckDuckGo.<p>Siempre nos aseguramos de que nuestros usuarios tengan el control en todo momento. Por lo tanto, protegemos de dos maneras: en primer lugar, el término de búsqueda introducido nunca se transmite a DuckDuckGo, sólo el !bang. En segundo lugar, el usuario confirma explícitamente la visita al destino de !bang. Desgraciadamente, por razones de personal, no podemos comprobar todos estos "bangs" ni mantenerlos nosotros mismos.</p>',
        ],
    ],
    'searchinsearch' => [
        'title' => 'Buscar en la búsqueda',
        '1' => 'Se puede acceder a la función de búsqueda en el buscador mediante el botón "MÁS" situado en la parte inferior derecha del cuadro de resultados. Al hacer clic se abre un menú en el que "Guardar resultado" está en primer lugar. Con esta opción, el resultado respectivo se almacena en una memoria separada. El contenido de esta memoria se muestra a la derecha de los resultados, bajo los consejos rápidos (en las pantallas demasiado pequeñas, los resultados guardados no se muestran por falta de espacio). Allí puede filtrar o reordenar los resultados guardados por palabra clave. Puede encontrar más información sobre el tema "Búsqueda en la búsqueda" en el <a href="http://blog.suma-ev.de/node/225" target="_blank" rel="noopener"> blog de SUMA</a>.',
    ],
    'selist' => [
        'title' => 'Añadir MetaGer a la lista de motores de búsqueda del navegador',
        'explanation_b' => 'Algunos navegadores esperan que introduzca una URL; esto es "https://metager.de/meta/meta.ger3?eingabe=%s" sin introducir comillas. Puede generar la URL usted mismo si busca algo con metager.de y luego reemplaza lo que está en la parte superior del campo de dirección después de "eingabe=" con %s. Si sigue teniendo problemas, póngase en contacto con nosotros: <a href="/kontakt" target="_blank" rel="noopener">Formulario de contacto</a>',
        'explanation_a' => 'Por favor, intente instalar primero el plugin actual. Para instalarlo, basta con hacer clic en el enlace que se encuentra justo debajo del campo de búsqueda. Su navegador ya debería haber sido reconocido allí.',
    ],
];