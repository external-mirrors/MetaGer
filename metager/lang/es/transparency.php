<?php
return [
    'head' => [
        '1' => 'Declaración de transparencia',
        '2' => 'MetaGer es transparente',
        '3' => '¿Qué es un metabuscador?',
        '4' => '¿Cuál es la ventaja de un metabuscador?',
        '5' => '¿Cuál es la composición de nuestra clasificación?',
        'compliance' => '¿Cómo responde MetaGer a los requerimientos de las autoridades?',
    ],
    'text' => [
        '1' => 'MetaGer es transparente. Nuestro <a href=":sourcecode">código fuente</a> <a href=":license">tiene licencia libre</a> y está disponible públicamente para todos. No almacenamos los datos de los usuarios y valoramos la protección de datos y la privacidad. Por lo tanto, concedemos acceso anónimo a los resultados de la búsqueda. Esto es posible a través de un proxy anónimo y un acceso oculto a TOR. Además, MetaGer cuenta con una estructura organizativa transparente, ya que está respaldada por la asociación sin ánimo de lucro <a href=":sumalink">SUMA-EV</a>, de la que cualquiera puede hacerse socio.',
        '2' => [
            '1' => 'Para explicar lo que son los metabuscadores, conviene primero explicar brevemente cómo funciona la indexación de los motores de búsqueda normales. Estos motores de búsqueda obtienen sus resultados a partir de una base de datos de sitios web, que también se denomina índice. Los motores de búsqueda utilizan los llamados "rastreadores" que recogen las páginas web y las añaden al índice (base de datos). El rastreador comienza con un conjunto de páginas web y abre todas las páginas web enlazadas. Estos se indexan, es decir, se añaden al índice. A continuación, el rastreador abre las páginas web enlazadas en estas páginas web y continúa así.',
            '2' => 'Un metabuscador combina los resultados de varios motores de búsqueda y los evalúa de nuevo según su propio esquema. Esto significa que el metabuscador no tiene su propio índice. Por eso los metabuscadores no utilizan rastreadores. Utilizan el índice de otros motores de búsqueda.',
        ],
        '3' => 'Una clara ventaja de los metabuscadores es que el usuario sólo necesita una única consulta para acceder a los resultados de varios buscadores. El metabuscador muestra los resultados relevantes en una lista reordenada de resultados. MetaGer no es un metabuscador puro, ya que también utilizamos pequeños índices propios.',
        '4' => 'Tomamos la clasificación de nuestros motores de búsqueda de origen y los ponderamos. Estas valoraciones se convierten en puntuaciones. Además, se tiene en cuenta la aparición de los términos de búsqueda en la URL y en el fragmento, así como la presencia excesiva de caracteres especiales (otros caracteres como el cirílico). También utilizamos una lista negra para eliminar páginas individuales de la lista de resultados. Bloqueamos las páginas web en la pantalla si estamos legalmente obligados a hacerlo. También nos reservamos el derecho de bloquear sitios web con información demostrablemente incorrecta, sitios web de muy baja calidad y otros sitios web particularmente dudosos.',
        '5' => 'Si tiene alguna otra pregunta o duda, no dude en utilizar nuestro <a href=":contact">formulario de contacto</a> y plantearnos sus dudas',
        'compliance' => 'Cumplimos con las solicitudes de las autoridades si estamos legalmente obligados a hacerlo y llegamos a la conclusión de que la aplicación no viola las libertades fundamentales. Nos tomamos muy en serio esta evaluación. También almacenamos la menor cantidad posible de datos personales para reducir el riesgo de tener que divulgarlos. En el siguiente cuadro encontrará datos sobre las solicitudes oficiales que hemos tramitado en los últimos 5 años. En breve se ofrecerá más información.',
    ],
    'table' => [
        'compliance' => [
            'th' => [
                'authinfocomp' => 'Solicitudes de información cumplidas',
                'authblockcomp' => 'Solicitudes de bloqueo cumplidas',
            ],
        ],
    ],
    'alt' => [
        'text' => [
            '1' => 'Representación visual de dos índices que se complementan para formar un metaindice',
        ],
    ],
];