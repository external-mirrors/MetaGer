<?php

/**
 * Fragen zum MetaGer-Schlüssel — /hilfe/schluessel.
 *
 * Aus dem "faq"-Zweig von pass/lang/<locale>/help.json des Keymanagers.
 */

return [
    "heading" => "Preguntas sobre la llave MetaGer",
    "faqs" => [
        [
            "summary" => "¿Cómo funciona la llave MetaGer?",
            "description" => "Con una clave MetaGer buscas sin publicidad. Recibes tokens de los que se descuenta una búsqueda por búsqueda. Cuando utilizas una clave MetaGer, se desactivan todas las funciones que protegen a MetaGer de las llamadas automáticas. Esto significa que no verás solicitudes captcha y que tu dirección IP no se guardará durante un tiempo limitado. En pocas palabras, esto hará que MetaGer sea más rápido, más fiable y más seguro.",
        ],
        [
            "summary" => "¿Cómo funciona la ficha anónima?",
            "description" => "Puedes utilizar el token anónimo con nuestra extensión de navegador o app. Esto te permitirá buscar de forma aún más segura con MetaGer. Al utilizar el token anónimo, una parte de tu crédito, en forma de contraseñas aleatorias, se almacenará en tu dispositivo. A través de un <a href=\":tokenlink\">complejo proceso criptográfico</a>, nos resulta imposible incluso a nosotros asociar tus búsquedas realizadas entre sí, o con tu clave.",
        ],
        [
            "steps" => [
                [
                    "description" => "Cuando estás en la página de gestión de la llave MetaGer, hay una opción para copiar una URL. Con esta URL todos los ajustes de MetaGer, así como la clave de MetaGer se pueden guardar en otro dispositivo.",
                    "heading" => "Copiar URL",
                ],
                [
                    "description" => "Cuando estés en la página de gestión de claves de MetaGer, hay una opción para guardar un archivo. Esto guarda tu clave de MetaGer como un archivo. A continuación, puede utilizar este archivo en otro dispositivo para iniciar sesión allí con su clave.",
                    "heading" => "Guardar archivo",
                ],
                [
                    "description" => "También puede escanear el código QR que aparece en la página de administración para iniciar sesión en otro dispositivo.",
                    "heading" => "Escanear código QR",
                ],
                [
                    "heading" => "Introduzca manualmente la clave MetaGer",
                    "description" => "Por supuesto, también puedes introducir la clave manualmente en otro dispositivo.",
                ],
            ],
            "summary" => "¿Cómo se utiliza la llave MetaGer?",
            "description" => "La clave MetaGer se configura y utiliza automáticamente en el navegador. Así que no necesitas hacer nada más. Si quieres utilizar la llave MetaGer en dispositivos adicionales, hay varias maneras de configurar la llave MetaGer:",
        ],
        [
            "summary" => "Tengo que introducir mi clave regularmente. ¿Qué puedo hacer?",
            "description" => "Le indicamos a su navegador que almacene permanentemente la clave una vez generada o iniciada la sesión. Dependiendo de la configuración de su navegador, es posible que lo haya configurado para eliminar regularmente las cookies y los datos del sitio web, lo que, por supuesto, también le cerrará la sesión en MetaGer. Tienes las siguientes opciones:",
            "steps" => [
                [
                    "description" => "En la configuración de Firefox puede poner MetaGer en una lista blanca para una exención de la eliminación de cookies y datos del sitio web que le mantendrá conectado.",
                    "heading" => "Añadir una excepción",
                ],
                [
                    "heading" => "Instale nuestra extensión de navegador",
                    "description" => "Nuestra extensión de navegador para <a href=\"https://addons.mozilla.org/firefox/addon/metager-suche/\" target=\"_blank\" rel=\"noopener\">Firefox</a> y <a href=\"https://chrome.google.com/webstore/detail/gjfllojpkdnjaiaokblkmjlebiagbphd\" target=\"_blank\" rel=\"noopener\">Chrome</a> puede almacenar su configuración de búsqueda que incluye su clave sin utilizar cookies para que pueda eliminar todos los browserdata sin ser desconectado de MetaGer.",
                ],
                [
                    "heading" => "Iniciar sesión sin introducir la clave de 36 caracteres",
                    "description" => "Si utilizas un gestor de contraseñas, puedes guardar la clave en él para iniciar sesión automáticamente. Alternativamente, ofrecemos una <a href=\":keylink\">URL de configuración</a> que puede guardarse, por ejemplo, como marcador. Al abrir la URL de configuración, iniciará la sesión sin necesidad de introducir manualmente la clave.",
                ],
            ],
        ],
        [
            "summary" => "No estoy satisfecho con la llave MetaGer. ¿Qué puedo hacer?",
            "description" => "En este caso, puede solicitar el reembolso de las fichas no utilizadas en los 30 días siguientes a la compra. Para ello, necesitarás tu ID de pago. Para solicitar un reembolso, abra la página de gestión de claves de MetaGer. Allí, haz clic en la opción de menú \"Pedidos\" e introduce tu ID de pago. A continuación, haz clic en el botón \"Solicitar reembolso\" y envía la solicitud de reembolso.",
        ],
        [
            "summary" => "¿Cómo puedo buscar de forma totalmente anónima?",
            "description" => "Su privacidad y anonimato son muy importantes para nosotros. Por eso ofrecemos métodos de pago anónimos (en efectivo). También ofrecemos el uso de <a href=\":tokenlink\">fichas anónimas</a>, que incluso pueden utilizar para buscar de forma anónima verificable.",
        ],
        [
            "summary" => "Necesito una factura. ¿Cómo puedo obtenerla?",
            "description" => "Para ello, sólo necesita su identificador de pago. Para solicitar la factura, abre la página de administración de la llave MetaGer. Haz clic en la opción de menú \"Pedidos\" e introduce tu ID de pago. Ahora puedes hacer clic en el botón \"Solicitar factura\" e iniciar la solicitud de factura. Para la factura necesitamos tu nombre completo, tu dirección de correo electrónico y tu dirección.",
        ],
        [
            "summary" => "Me gustaría cargar mi llave MetaGer automáticamente. ¿Cómo hacerlo?",
            "description" => "Para nuestros miembros, la clave incluida en la afiliación se recarga automáticamente cada mes. La cantidad de fichas depende de la cuota de afiliación pagada.",
        ],
        [
            "summary" => "He recibido una tarjeta o un enlace con un código de vale. ¿Qué hago con él?",
            "description" => "Algunas organizaciones regalan llaves de MetaGer con un saldo fijo mediante tarjetas promocionales o un enlace. Abra <a href=\":voucherlink\">nuestra página de canje</a>, introduzca el código impreso o escanee el código QR de la tarjeta. Recibirá inmediatamente una nueva llave de MetaGer con el saldo regalado, válido durante un tiempo limitado. Cada código solo puede canjearse una vez.",
        ],
    ],
    "more-questions" => "¿Tiene más preguntas? No dude en utilizar nuestro <a href=\":contactlink\" target=\"_blank\">formulario de contacto</a>.",
];
