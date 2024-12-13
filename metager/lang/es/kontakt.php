<?php
return [
    'headline' => [
        '1' => 'Contacto',
        '2' => 'Correo electrónico',
        'pgp' => 'Cifrado',
    ],
    'form' => [
        '1' => 'Formulario de contacto anónimo',
        '2' => 'Puede enviarnos un mensaje anónimo a través de este formulario. Si decide no incluir su dirección de correo electrónico, no recibirá respuesta.',
        '5' => 'Su dirección de correo electrónico (opcional)',
        '6' => 'Su mensaje',
        '7' => '<strong>Su mensaje será encryptada antes de mandarla <a href="http://openpgpjs.org/.">OpenPGP.js</a> para esto necesitamos Javascript.</strong> Sino tiene activado Javascript su mensaje será enviada sin encryptación.',
        '8' => 'Encriptar y enviar',
        'name' => 'Nombre',
        '9' => 'Añadir hasta 5 archivos adjuntos (tamaño de archivo &lt; 5 MB)',
        'temperror' => 'Actualmente experimentamos dificultades. Nuestro formulario de contacto volverá pronto.',
    ],
    'letter' => [
        '1' => 'Por carta',
        '2' => 'Preferimos que nos contacte por medios digitales. Si lo ve indispensable contactarnos vía correo fisico, nos puede escribir a la siguiente dirección:',
        '3' => "SUMA-EV\r
Postfach 51 01 43\r
D-30631 Hannover\r
Germany",
    ],
    'error' => [
        '1' => 'Lo sentimos, pero desafortunadamente no recibimos ningún dato con su solicitud de contacto. El mensaje no fue enviado.',
        '2' => 'Se ha producido un error al enviar su mensaje. Puede ponerse en contacto con nosotros directamente en la siguiente dirección de correo electrónico: :email',
    ],
    'success' => [
        '1' => 'Su mensaje nos fue enviado con éxito. ¡Muchas gracias por esto! Procesaremos esto lo antes posible y luego lo contactaremos nuevamente si es necesario.',
    ],
    'email' => [
        'text' => 'Puede ponerse en contacto con nosotros enviando un correo a: <a href="mailto::mail">:mail</a>',
        'pgp' => [
            'description' => 'Nuestros correos electrónicos están firmados criptográficamente. Si desea verificar la firma o enviar su correo cifrado por favor utilice la siguiente clave pública. Si desea recibir una respuesta cifrada adjunte su clave pública a su correo cifrado y firmado.',
            'pubkey' => 'Clave pública PGP: <a href="/download/pubkey.asc" download="0x2185CC8F3CA782EC.asc">0x2185CC8F3CA782EC</a> o en <a href=":keyserver" target="_blank" rel="noopener">keys.openpgp.org</a>',
            'fingerprint' => 'Huella PGP: 5FA5 2398 C382 B498 B14A B7F6 2185 CC8F 3CA7 82EC',
        ],
    ],
];
