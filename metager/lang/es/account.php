<?php
return [
    /**
     * The account, wherever it appears: the pill in the corner, the block at the
     * top of the site menu, and the one alert that interrupts.
     *
     * Its own file rather than more keys under index/sidebar, because the same
     * strings are now rendered from three different views on two different
     * layouts, and none of them is "the index page".
     */
    'pill' => [
        'charge' => ':charge fichas',
        // Shown instead of the key code when the key cannot be named — a legacy
        // non-UUID key whose canonical form we could not resolve.
        'signed_in' => 'Sesión iniciada',
        // The webextension: the key never reaches us, so there is nothing to name.
        'anonymous' => 'sesión iniciada de forma anónima',
        // The mark is decorative and the code is styled shorthand, so the whole
        // answer has to be in the label for anyone not reading the pixels.
        //
        // "ending in", not a bare "key :fingerprint": these are the last six
        // characters of the key (KeyUser::getKeyFingerprint()), and calling them
        // the key invited people to type them into the sign-in form — where,
        // until the guard in the keymanager's POST /key/enter, they resolved to
        // an empty phantom account.
        'aria' => 'Mi cuenta – clave terminada en :fingerprint, :charge fichas',
        'aria_nocharge' => 'Mi cuenta – clave terminada en :fingerprint',
        'aria_nofingerprint' => 'Mi cuenta – :charge fichas',
        'aria_anonymous' => 'Mi cuenta – sesión iniciada de forma anónima con la extensión web',
    ],
    'sidebar' => [
        'balance' => ':charge fichas · sin publicidad',
        // Not "0 fichas · …": at zero the searches are not ad-free, they do
        // not happen at all.
        'balance_empty' => 'No quedan fichas',
        'manage' => 'Gestionar',
        'topup' => 'Recargar',
        'logout' => 'Cerrar sesión',
        'login' => 'Iniciar sesión',
        'create' => 'Configurar',
        'logged_out' => 'No ha iniciado sesión. Con una clave busca sin publicidad y de forma anónima.',
        'anonymous_hint' => 'Sin publicidad · gestionado por la extensión web',
        // The webextension holds the key, so the account lives in its popup.
        // The button this labels is hidden until the extension reveals it.
        'extension_settings' => 'Gestionar en la extensión',
    ],
    /**
     * The account page itself — /konto, moved here from /keys/key/<uuid>.
     *
     * Taken from the keymanager's pass/lang/<locale>/key.json, but mostly new.
     * The old page was almost nothing but button labels; what it never said is
     * what any of them are *for* — which is exactly what support gets asked.
     *
     * Not carried over: `key.share.*`. The share button handed the settings URL,
     * key included, to `navigator.share` and therefore to the operating system's
     * share sheet. Passing an account on is not something a button should
     * advertise; whoever wants to can copy the URL. The copy button stayed.
     */
    'page' => [
        'heading' => 'Mi cuenta',

        // Not "your key: 123456". These are the last six characters, and calling
        // them the key led people to type them into the sign-in form.
        'fingerprint' => 'Clave terminada en :fingerprint',
        'fingerprint_unknown' => 'Sesión iniciada',

        'balance' => [
            'unit' => 'tokens',
            'one_token' => 'Un token es una búsqueda.',
            'valid_until' => 'Saldo válido hasta el :date',
            'empty' => 'No queda saldo. Sin tokens no puede buscar: recargue para continuar.',
            'low' => 'El saldo se está agotando.',
            'unknown' => 'Ahora mismo no podemos consultar su saldo. Es cosa nuestra, no suya: inténtelo de nuevo dentro de unos minutos.',
            'orders_summary' => 'De :count recargas, que caducan una tras otra',
            'orders_heading' => 'Fechas de caducidad',
            'order' => ':amount tokens hasta el :date',
        ],

        'actions' => [
            'topup' => 'Recargar saldo',
            'search' => 'Ir a la búsqueda',
        ],

        'charge' => [
            'heading' => 'Recargar saldo',
            'lede' => 'Un token es una búsqueda y cuesta un céntimo. Todos los precios incluyen IVA.',
            'tokens' => ':amount tokens',
            'price' => ':price €',
            'more' => 'Todos los precios y formas de pago',

            /**
             * Why no package is on offer right now. Three sentences for three
             * states, all three of which the old page had too — except that it
             * said "your key is already fully charged", which is not true: what
             * is full is not the balance but the number of open top-ups.
             */
            'blocked' => [
                'proxy' => 'Está navegando a través de una de nuestras sesiones de proxy. Mientras lo haga, la recarga está desactivada por su seguridad: un pago lleva a un proveedor de pagos, y este no debe ver esta sesión. Abra esta página sin sesión de proxy para recargar.',
                'full' => 'Esta clave ya tiene tres recargas. En cuanto la más antigua se agote o caduque, podrá volver a recargar.',
                'member' => 'Es miembro de SUMA-EV y busca sin coste adicional. No necesita ningún paquete de tokens.',
            ],
        ],

        /**
         * The section the old page did not have: QR code, settings URL and the
         * transfer button sat there in one row, with not a sentence about what
         * they are for.
         */
        'save' => [
            'heading' => 'Asegure su acceso',
            'text' => 'Mientras este navegador conserve la cookie, su sesión seguirá iniciada. Si la pierde — un dispositivo nuevo, datos de navegación borrados —, su clave es el único camino de vuelta a su saldo. Aquí la tiene, y aquí tiene tres formas de llevársela.',

            /**
             * The key itself.
             *
             * It has to be here — the sign-in form asks for it first of all —
             * and it is collapsed, because this page gets photographed for
             * support tickets. The old page showed it large and always.
             */
            'key' => [
                'summary' => 'Mostrar y copiar la clave',
                'label' => 'Su clave',
                'action' => 'Copiar la clave',
                'hint' => '36 caracteres. Es lo que le permite iniciar sesión en cualquier otro dispositivo. Plegada porque a menudo se hacen fotografías de esta página: quien vea su clave buscará a su costa.',
            ],

            'qr' => [
                'label' => 'Código QR',
                'alt' => 'Código QR que lleva a su clave',
                'action' => 'Guardar como imagen',
                'hint' => 'La imagen que pide el formulario de inicio de sesión. Puede subirla allí o fotografiarla con la cámara.',
            ],

            'url' => [
                'label' => 'Marcador',
                'action' => 'Copiar la URL',
                'hint' => 'Abrir esta URL restablece la clave junto con los ajustes de búsqueda de este navegador.',
            ],

            /**
             * The transfer dialog. The keymanager called it "generate login
             * code" — a label naming the means rather than the end, which is why
             * it never answered "how do I get MetaGer onto my phone?", even
             * though that is exactly what the button does.
             */
            'transfer' => [
                'label' => 'Otro dispositivo',
                'action' => 'Iniciar sesión en un dispositivo',
                'hint' => 'Muestra un código corto que se teclea en el formulario de inicio de sesión del otro dispositivo, en lugar de copiar la clave entera.',

                'title' => 'Iniciar sesión en otro dispositivo',
                'description' => 'Introduzca este código en el otro dispositivo, en el formulario de inicio de sesión, donde normalmente va la clave.',
                'waiting' => 'Obteniendo el código …',
                'note' => 'El código vale para un único inicio de sesión y solo mientras se muestre aquí. Cierre esta ventana en cuanto lo haya introducido.',
                'failed' => 'No se ha podido obtener el código. Cierre la ventana e inténtelo de nuevo enseguida.',
                'close' => 'Cerrar',
            ],
        ],

        /**
         * What still lives in the keymanager. A list at the foot rather than the
         * three equal tabs of before: hardly anybody has campaigns, and a third
         * tab claimed otherwise.
         */
        'more' => [
            'heading' => 'Más',
            'orders' => 'Pedidos y facturas',
            'campaigns' => 'Campañas de vales',
            'help' => 'Ayuda sobre la clave',
            'logout' => 'Cerrar sesión',
            // Signing out only clears the cookie. Someone who does not know
            // that will not click it — and someone who reads it as "delete
            // account" certainly will not.
            'logout_hint' => 'Elimina la clave de este navegador. El saldo permanece en la clave.',
        ],
    ],

    'empty' => [
        'message' => 'Ha agotado sus fichas.',
        'action' => 'Recargar ahora',
    ],
];
