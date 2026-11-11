<?php
return [
    'title' => 'Retirar un resultado de búsqueda – MetaGer',
    'meta_description' => 'Cómo retirar un resultado de búsqueda: derecho al olvido, contenido desactualizado, derechos de autor. Dónde pedirlo: Google, Brave y otros.',
    'heading' => 'Retirar un resultado de búsqueda',
    'lead' => 'MetaGer es un metabuscador: no tenemos índice propio, sino que mostramos lo que encuentran otros buscadores. Por eso, un resultado solo se puede retirar de verdad allí de donde procede: en el sitio web y en los buscadores cuyos resultados muestra MetaGer. Esta página le indica dónde.',
    'toc' => [
        'label' => 'En esta página',
    ],
    'website' => [
        'heading' => 'Diríjase al propio sitio web',
        'text' => 'Es la única vía que funciona en todas partes. Si la página se borra o se modifica, todos los buscadores la retiran o la actualizan en su siguiente visita, MetaGer incluido, sin que tenga que preguntar a cada uno por separado.',
        'tips' => [
            'La mayoría de los sitios web indican una dirección de contacto en el aviso legal o en la política de privacidad. Pida allí que borren el contenido, lo corrijan o al menos eliminen su nombre.',
            'Cuando se trata de datos personales, los sitios web de la UE deben responder a una solicitud basada en el art. 17 del RGPD (derecho de supresión).',
            'Si el sitio web es suyo: borre la página para que responda con «404» o «410», o márquela con «noindex». Los buscadores la retirarán por sí solos.',
        ],
        'sources' => 'Para los resultados de :sources, esta es la única vía.',
    ],
    'kind' => [
        'heading' => '¿De qué se trata?',
        'intro' => 'Si el sitio web no le ayuda o no puede esperar, los buscadores tienen sus propios procedimientos. Cuál es el adecuado depende de lo que quiera conseguir.',
        'personal_data' => [
            'summary' => 'Datos personales: el derecho al olvido',
            'text' => 'Un resultado para su nombre lleva a información sobre usted que es inexacta, desactualizada, no pertinente o excesiva. Según el art. 17 del RGPD, puede pedir a un buscador que deje de mostrar ese resultado en las búsquedas de su nombre. El buscador sopesa su interés frente al interés del público en la información, por lo que no todas las solicitudes prosperan. En las tarjetas de abajo aparece como «Datos personales».',
        ],
        'outdated' => [
            'summary' => 'Contenido desactualizado',
            'text' => 'El sitio web ya ha borrado o modificado la página, pero el resultado sigue mostrando el título o el texto antiguos. Esto se resuelve solo en la siguiente visita del buscador; en la mayoría de ellos también puede solicitar una actualización anticipada. En las tarjetas de abajo aparece como «Contenido desactualizado».',
        ],
        'legal' => [
            'summary' => 'Derechos de autor y otros motivos legales',
            'text' => 'Un resultado infringe sus derechos de autor u otros derechos. Los buscadores reciben estos avisos a través de sus propios formularios legales; normalmente solo puede enviarlos el titular de los derechos o alguien en su nombre. En las tarjetas de abajo aparece como «Derechos de autor y legal».',
        ],
        'illegal' => [
            'summary' => 'Contenidos ilegales',
            'text' => 'Las representaciones de abuso sexual de menores, los contenidos perjudiciales para menores, la incitación al odio y otros similares: denúncielos primero ante una línea de denuncia. Estas actúan contra el propio contenido, en su alojamiento, y colaboran con la policía. Si además quiere que MetaGer deje de mostrar ese resultado, envíenos únicamente su dirección (URL) como texto, por correo electrónico. No nos envíe nunca el contenido en sí, tampoco capturas de pantalla ni archivos adjuntos.',
            'hotlines' => 'Líneas de denuncia:',
        ],
    ],
    'providers' => [
        'heading' => 'Pregunte a los buscadores que muestra MetaGer',
        'text' => 'MetaGer no rastrea la web por sí mismo. Preguntamos a los buscadores de abajo en el momento en que usted busca, mostramos lo que devuelven y guardamos su respuesta en caché durante unas pocas horas. Si uno de ellos retira un resultado, este desaparece también de lo que MetaGer recibe de él.',
        'which' => 'El buscador del que procede un resultado se indica debajo del resultado en la página de resultados de MetaGer. Si procede de varios, pregunte a cada uno de ellos.',
        'shows' => 'En MetaGer en:',
        'kinds' => [
            'personal_data' => 'Datos personales',
            'outdated' => 'Contenido desactualizado',
            'legal' => 'Derechos de autor y legal',
        ],
        'no_form' => 'Sin formulario, contactar con :name',
        'contact' => 'Contactar con :name',
        'servers' => 'Los servidores de Mastodon en los que busca TootNews',
        'google' => 'MetaGer recibe los resultados de Google a través del servicio Serper. Lo que Google retira de su índice desaparece también de MetaGer.',
        'brave' => 'Brave Search tiene índice propio. Las solicitudes sobre datos personales se envían por correo electrónico a privacy@brave.com con el asunto «RTBF request»; la página enlazada indica qué debe incluir el correo.',
        'mojeek' => 'Mojeek tiene índice propio y no edita sus resultados a mano. Retira páginas a petición por motivos legales; escriba a través de su página de contacto e indique las direcciones exactas.',
        'staan' => 'Staan es la interfaz de búsqueda de European Search Perspective. Recibe las solicitudes sobre datos personales a través de su propio formulario; rellene todos los campos y adjunte los justificantes. La retirada solo afecta al índice de Staan.',
        'polias' => 'Polias tiene índice propio y no dispone de formulario. Escriba a hallo@polias.eu indicando la dirección de la página y el motivo.',
        'pixabay' => 'Pixabay aloja las imágenes por sí mismo. Utilice el formulario de denuncia o el botón de denuncia en la página de la imagen en Pixabay.',
        'suma' => 'TootNews lo gestiona SUMA-EV, la asociación que está detrás de MetaGer, y busca publicaciones en unos pocos servidores de Mastodon. Cada resultado indica de qué servidor procede; pida a quienes gestionan ese servidor que borren u oculten la publicación.',
        'website' => 'BASE muestra lo que contienen los repositorios en los que busca. Si el repositorio retira un registro, este desaparece también de BASE.',
    ],
    'own' => [
        'heading' => 'Nuestros propios buscadores',
        'text' => 'SUMA-EV, la asociación que está detrás de MetaGer, también gestiona buscadores propios: Nolm y TootNews / TrötNews. Tampoco tienen un índice propio, así que sus resultados también se eliminan allí de donde proceden.',
        'nolm' => 'Nolm busca en el Open Web Index. Solicite la eliminación con el formulario de retirada del Open Web Index: lo que se elimina allí desaparece también de Nolm.',
        'tootnews' => 'TootNews busca publicaciones en unos pocos servidores de Mastodon, que aparecen en su página de inicio; cada resultado indica de qué servidor procede. Pida a quienes gestionan ese servidor que borren u oculten la publicación. Una vez que desaparezca allí, TootNews deja de encontrarla.',
    ],
];
