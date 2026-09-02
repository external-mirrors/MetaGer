<?php

return [
    'heading' => 'Campañas de vales',
    'description' => 'Reparte claves de tu propio saldo de fichas, por ejemplo entre amigos o compañeros. Las claves entregadas solo descuentan sus fichas de tu clave cuando realmente se usan: los regalos no utilizados no te cuestan nada.',
    'unreachable' => 'Tus campañas de vales no se han podido cargar en este momento. Inténtalo de nuevo más tarde.',
    'copy_link' => 'Copiar enlace',
    'public_link' => 'Enlace público',
    'delete_note' => 'Las campañas caducadas y desactivadas se eliminan automáticamente.',
    'print_cards' => 'Imprimir tarjetas (PDF)',
    'disable' => 'Desactivar',
    'delete' => 'Eliminar ahora',

    'status' => [
        'active' => 'activa',
        'disabled' => 'desactivada',
        'expired' => 'caducada',
    ],

    'facts' => [
        'tokens_per_key' => ':tokens fichas por clave',
        'redeemed' => ':redeemed de :total canjeados',
        'budget' => 'quedan :left de :total fichas',
        'expires' => 'termina el :date',
    ],

    'create' => [
        'heading' => 'Crear una campaña',
        'info' => 'La campaña se respalda con esta clave: las fichas repartidas se descuentan de tu saldo cuando se usan. Las campañas duran 3 meses, las claves repartidas son válidas durante 1 mes tras el canje.',
        'name' => 'Nombre (solo visible para ti)',
        'tokens_per_key' => 'Fichas por clave repartida',
        'total_volume' => 'Máximo total de fichas',
        'total_volume_hint' => 'Tu clave contiene actualmente :charge fichas. Nunca puedes repartir más de tu saldo.',
        'voucher_count' => 'Número de vales (opcional)',
        'voucher_count_hint' => 'Por defecto: el máximo total dividido entre las fichas por clave.',
        'submit' => 'Crear campaña',
        'error' => [
            'tokens_per_key_too_high' => 'Las fichas por clave no pueden superar el máximo total.',
            'voucher_count_out_of_range' => 'El número de vales no encaja con las fichas por clave y el máximo total.',
            'over_budget' => 'El máximo total supera tu saldo disponible.',
            'too_many_active' => 'Ya tienes el número máximo de campañas activas.',
            'invalid' => 'No se ha podido crear la campaña. Comprueba tus datos.',
            'unreachable' => 'La campaña no se ha podido crear en este momento. Inténtalo de nuevo más tarde.',
        ],
    ],

    /**
     * /c — App\Http\Controllers\VoucherController.
     */
    'redeem' => [
        'enter' => [
            'heading' => 'Canjea tu vale',
            'description' => '¿Has recibido un código de vale para búsquedas gratuitas en MetaGer? Introdúcelo aquí para obtener tu clave personal de MetaGer.',
            'label' => 'Tu código de vale',
            'submit' => 'Canjear código',
            'invalid_code' => 'Este código no es válido. Comprueba lo que has escrito.',
            'rate_limited' => 'Demasiados intentos. Inténtalo de nuevo más tarde.',
        ],
        'teaser' => [
            'heading' => 'Tu regalo de MetaGer',
            'tokens' => 'Fichas',
            'description' => 'Este código te da una clave de MetaGer propia cargada con :tokens fichas: busca en la web sin publicidad y sin que te rastreen.',
            'validity' => 'La clave es válida durante :days días tras el canje.',
            'submit' => 'Quiero mi clave',
        ],
        'redeemed' => [
            'heading' => '¡Aquí tienes tu clave de MetaGer!',
            'description' => 'Tu nueva clave está cargada con :tokens fichas.',
            'save' => [
                'heading' => '1. Guarda tu clave',
                'description' => 'Tu clave es tu acceso: solo se muestra aquí y no se puede recuperar. Guárdala en tu gestor de contraseñas, descarga el código QR o imprime esta página.',
            ],
            'copy_key' => 'Copiar clave',
            'validity' => 'La clave es válida hasta :date.',
            'use' => [
                'heading' => '2. Empieza a buscar',
                'description' => 'Abre este enlace para activar la clave en tu navegador. Guárdalo en favoritos para seguir conectado.',
            ],
            'copy_url' => 'Copiar enlace',
            'start_searching' => 'Empezar a buscar ahora',
            'to_account' => 'Ir a mi cuenta',
            'qr_alt' => 'Código QR de la clave',
            'no_cookies' => 'Este navegador no parece guardar cookies. Guarda en su lugar la clave o el código QR de arriba.',
        ],
        'error' => [
            'heading' => 'Esto no ha funcionado',
            'invalid_code' => 'Este código no existe. Comprueba lo que has escrito.',
            'invalid_token' => 'Este enlace no es válido o ha caducado.',
            'already_redeemed' => 'Este código ya se ha canjeado.',
            'campaign_inactive' => 'Esta campaña ha terminado. El código ya no se puede canjear.',
            'budget_exhausted' => 'Todos los regalos de esta campaña ya se han repartido.',
            'rate_limited' => 'Demasiados intentos. Inténtalo de nuevo más tarde.',
            'unreachable' => 'El vale no se ha podido canjear en este momento. Inténtalo de nuevo más tarde.',
            'unknown' => 'Se ha producido un error inesperado. Inténtalo de nuevo más tarde.',
            'retry' => 'Introducir un código',
        ],
    ],
];
