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
];
