<?php
return [
    'heading' => [
        '1' => 'Verificación de hechos versus noticias falsas:',
    ],
    'paragraph' => [
        '1' => '¿De dónde viene el mensaje?',
        '2' => 'Hay varias formas de hacerlo. Para comprobar la calidad de la información, existe el llamado <a href="https://library.csuchico.edu/sites/default/files/craap-test.pdf">"test CRAAP</a>". Se desarrolló en la Universidad Estatal de California en Chico. Esta prueba ofrece muchas preguntas útiles para ver si la información es fiable. Además de esta prueba, puede encontrar más información útil aquí:',
    ],
    'list' => [
        '1' => [
            '1' => '¿Este sitio web tiene una impresión? ¿Quién se menciona allí (nombre, empresa, ...) y qué se puede descubrir acerca de estas personas?',
            '2' => '¿Quién es el propietario del sitio web en la base de datos Whois? ¿Coincide esto con la impresión y lo que se puede averiguar sobre los propietarios del sitio web? (<a href="https://de.wikipedia.org/wiki/Whois" target="_blank" rel="noopener"> https://de.wikipedia.org/wiki/Whois </a>)',
            '3' => '¿Hay un autor en el sitio web? ¿Qué se puede descubrir acerca de esta persona / s?',
            '4' => 'Publica lo anterior ¿Gente frecuente / él sobre el tema? ¿Eres conocido en esta (materia) área? ¿Hay alguna entrada de Wikipedia sobre ti?',
            '0' => 'sitio web',
        ],
        '2' => [
            '1' => '¿Es un autor nombrado con un nombre que parece real? Si no: mensaje extremadamente cuestionable.',
            '2' => '¿Qué información adicional hay para este nombre?',
            '3' => '¿Se puede verificar si este nombre es real? ¿También se puede contactar a esta persona en otros canales de comunicación? ¿Puedes llamarla y hablar con ella sobre este texto?',
            '4' => '¿El perfil de este nombre está verificado en FB? (<a href="https://www.facebook.com/help/196050490547892" target="_blank" rel="noopener"> https://www.facebook.com/help/196050490547892 </a>)',
            '5' => '¿Cuánto tiempo lleva este perfil en existencia?',
            '0' => 'Facebook u otras redes sociales o foros',
        ],
        '3' => [
            '1' => '¡No todo en Wikipedia es cierto!',
            '2' => 'Verifique el historial de versiones: ¿Quién escribió qué y cuándo?',
            '3' => '¿Se puede saber algo sobre estos autores?',
            '4' => '¿Hay una página de discusión para esta entrada de Wikipedia de la que se puedan sacar más conclusiones?',
            '5' => 'Comprueba qué origen se indican en la entrada de Wikipedia. A menudo también pueden servir como origen de información adicional.',
            '0' => 'Wikipedia',
        ],
        '4' => [
            '1' => '¡Falsificar correos electrónicos es MUY fácil! => Correos electrónicos falsos.',
            '2' => '¿El correo electrónico realmente proviene del remitente especificado? Verifique el encabezado del correo electrónico cuidadosamente y examine las direcciones IP y las rutas de entrega mencionadas allí (no es fácil).',
            '3' => 'Utilice el correo electrónico firmado y mejor cifrado (no es fácil); Notas aquí: <a href="https://www.heise.de/ct/artikel/Ausgebootet-289538.html" target="_blank" rel="noopener"> https://www.heise.de/ ct / artikel / Ausbootoot-289538.html </a>',
            '0' => 'Correos electrónicos',
        ],
        '5' => [
            '1' => 'Mira de cerca el fondo. Paisajismo, edificios, automóviles y matrículas, ropa, personas. ¿Es correcto? ¿Encaja con el texto asociado si es necesario?',
            '2' => '¿Puedes encontrar imágenes similares con la búsqueda de imágenes inversa de los motores de búsqueda de imágenes?',
            '3' => '¿Puedes leer metadatos de las imágenes con programas gráficos? ¿Estos metadatos coinciden con el contenido de la imagen?',
            '0' => 'Fotos, videos',
        ],
        '7' => '¡NO hay seguridad absoluta contra la falsificación!',
    ],
];