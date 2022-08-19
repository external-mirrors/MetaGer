<?php

return [
    'head.1' => 'declaración de transparencia',
    'head.2' => 'MetaGer ist transparent',
    'head.3' => '¿Qué es un metabuscador?',
    'head.4' => '¿Cuál es la ventaja de un metabuscador?',
    'head.5' => '¿Cómo está compuesta nuestra clasificación?',
    'head.compliance' => '¿Cómo responde MetaGer a las solicitudes de las autoridades?',
   
   
    'text.1' => 'MetaGer ist transparent. Nuestro <a href=":sourcecode">código fuente</a> es <a href=":license">de licencia libre</a> y está disponible públicamente para que todos lo vean. No almacenamos los datos de los usuarios y valoramos la protección de datos y la privacidad. Por eso concedemos acceso anónimo a los resultados de la búsqueda. Esto es posible a través de un proxy anónimo y un acceso oculto a TOR. Además, existe una estructura organizativa transparente, ya que MetaGer está respaldada por la asociación sin ánimo de lucro <a href=":sumalink">SUMA-EV</a>, de la que cualquiera puede hacerse socio.',
    'text.2.1' => 'Para explicar qué son los metabuscadores, tiene sentido explicar primero a grandes rasgos cómo funciona la indexación de los buscadores normales. Estos motores de búsqueda obtienen sus resultados a partir de una base de datos de páginas web, que también se denomina índice. Los motores de búsqueda utilizan los llamados "rastreadores" que recogen las páginas web y las añaden al índice (base de datos). El rastreador comienza con un conjunto de páginas web y abre todas las páginas web enlazadas. Estos se indexan, es decir, se añaden al índice. A continuación, el rastreador abre las páginas web enlazadas en estas páginas web y continúa así.',
    'text.2.2' => 'Un metabuscador combina los resultados de varios buscadores bajo sí mismo y los evalúa de nuevo según su propio esquema. Esto significa que el metabuscador no tiene su propio índice. Por eso los metabuscadores no utilizan rastreadores. Utilizan el índice de otros motores de búsqueda.',

    'text.3' => 'Una clara ventaja de los metabuscadores es que el usuario sólo necesita una única consulta para obtener los resultados de varios buscadores. El metabuscador muestra los resultados relevantes en una lista de resultados que se vuelve a ordenar. MetaGer no es un metabuscador puro, ya que también utilizamos pequeños índices propios.',
    'text.4' => 'Tomamos el ranking de nuestros motores de búsqueda de origen y los ponderamos. Estas valoraciones se convierten en puntuaciones. Además, se tiene en cuenta la aparición de los términos de búsqueda en la URL y en el fragmento, así como la presencia excesiva de caracteres especiales (otros caracteres como el cirílico). También utilizamos una lista negra para eliminar páginas individuales de la lista de resultados. Bloqueamos las páginas web en la pantalla si estamos legalmente obligados a hacerlo. También nos reservamos el derecho de bloquear sitios web con información demostrablemente incorrecta, sitios web de muy baja calidad y otros sitios web particularmente dudosos.',
    'text.5' => 'Si tiene más preguntas o dudas, no dude en utilizar nuestro <a href=":contact">formulario de contacto</a> y plantearnos sus dudas.',
    'text.compliance' => 'Cumplimos con las solicitudes de las autoridades si estamos legalmente obligados a hacerlo y llegamos a la conclusión de que una aplicación no viola las libertades fundamentales. Nos tomamos este examen muy en serio. También almacenamos la menor cantidad posible de datos personales para reducir el riesgo de tener que entregarlos. En el siguiente cuadro encontrará datos sobre las solicitudes oficiales que hemos tramitado en los últimos 5 años. En breve habrá más información.',

    'table.compliance.th.authinfocomp' => 'Solicitudes de información cumplidas',
    'table.compliance.th.authblockcomp' => 'Solicitudes de bloqueo cumplidas',

];
