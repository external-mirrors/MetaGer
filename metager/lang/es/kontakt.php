<?php
return [
    'lead' => 'Respondemos más rápido por correo electrónico.',
    'headline' => [
        '1' => 'Contacto',
        '2' => 'Correo electrónico',
    ],
    'letter' => [
        '1' => 'Correo postal',
        '3' => "SUMA-EV\r
Postfach 51 01 43\r
D-30631 Hannover\r
Germany",
    ],
    'email' => [
        'write' => 'Escribir un correo',
        'pgp' => [
            'summary' => 'Escribir cifrado (PGP)',
            'signed' => 'Todos nuestros correos van firmados con nuestra clave OpenPGP, que también se incluye en su cabecera Autocrypt. Use esta clave para escribirnos cifrado:',
            'replies' => [
                'heading' => 'Respuestas cifradas',
                'how' => 'Respondemos cifrado automáticamente en cuanto tenemos su clave y confiamos en ella. Para ello, firme su correo. Tomamos su clave del correo (como adjunto o en la cabecera Autocrypt) o de keys.openpgp.org, y la usamos por nuestra cuenta si',
                'trusted_when' => [
                    'keys.openpgp.org la proporciona para su dirección (es decir, su dirección está verificada allí), o',
                    'su correo lleva una firma DKIM válida de su dominio de remitente.',
                ],
                'otherwise' => 'Si no, alguien de nuestro equipo la confirma antes a mano. A partir de entonces ciframos todo lo que le enviamos, no solo las respuestas.',
                'tofu' => 'Confiamos en la primera clave que recibimos así para su dirección (Trust on First Use). No adoptamos automáticamente una clave nueva para la misma dirección: hasta revisarla, seguimos cifrando con la anterior.',
                'never_plain' => 'Nunca respondemos sin cifrar a un correo cifrado. Si no tenemos su clave, no podemos responder; por eso, firme siempre los correos cifrados que nos envíe.',
            ],
            'pubkey' => 'Clave pública PGP: <a href="/download/pubkey.asc" download="0x2185CC8F3CA782EC.asc">0x2185CC8F3CA782EC</a> o en <a href=":keyserver" target="_blank" rel="noopener">keys.openpgp.org</a>',
            'fingerprint' => 'Huella PGP: 5FA5 2398 C382 B498 B14A B7F6 2185 CC8F 3CA7 82EC',
        ],
    ],
    'removal' => [
        'heading' => 'Retirada',
        'only' => 'Solo puede pedirnos que retiremos resultados de búsqueda mediante nuestro formulario. No aceptamos estas solicitudes por correo electrónico, teléfono ni correo postal.',
        'form' => 'Ir al formulario',
        'guide' => 'El formulario es para nuestros propios índices de búsqueda. MetaGer muestra resultados de otros buscadores; <a href=":link">nuestra guía</a> explica dónde surte efecto una retirada en esos casos.',
    ],
    'phone' => [
        'heading' => 'Teléfono',
        'hours' => 'De lunes a viernes, de :from a :to (hora de Alemania)',
        'outside' => 'En ese horario casi siempre le atenderemos; fuera de él, casi nunca. En ese caso, es mejor que nos escriba un correo electrónico.',
    ],
    'topics' => [
        'heading' => 'Consultas frecuentes',
        'key' => [
            'title' => 'Clave de MetaGer y tokens',
            'text' => 'Cómo funciona la clave, recargas y saldo',
        ],
        'orders' => [
            'title' => 'Pedidos y facturas',
            'text' => 'Encontrar un pedido, recibos y reembolsos',
        ],
        'donation' => [
            'title' => 'Donaciones',
            'text' => 'Apoye a MetaGer con una donación',
        ],
        'membership' => [
            'title' => 'Hacerse socio',
            'text' => 'Únase a SUMA-EV, la asociación detrás de MetaGer',
        ],
        'help' => [
            'title' => 'Ayuda para buscar',
            'text' => 'Funciones, ajustes y preguntas frecuentes',
        ],
        'privacy' => [
            'title' => 'Protección de datos',
            'text' => 'Qué guardamos y qué no',
        ],
    ],
];
