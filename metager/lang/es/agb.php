<?php

/**
 * Allgemeine Geschäftsbedingungen für die Token-Aufladung — /agb.
 *
 * Vertragstext, aus pass/lang/<locale>/agb.json des Keymanagers übernommen.
 * Tests\Feature\AgbTest vergleicht die gerenderte deutsche Fassung Zeile für
 * Zeile mit einem Abzug der alten Seite; jede Abweichung steht dort
 * ausgeschrieben, damit sie mit rechtlichem Blick nachlesbar bleibt. Es sind
 * drei:
 *
 *   - Der Text nennt seine eigene Fundstelle. Die stand wörtlich als
 *     "metager.de/keys/agb" im Vertrag und ist jetzt der Platzhalter :agburl.
 *   - Die Paketliste in §4 nannte 12000 Token, die es nicht zu kaufen gibt,
 *     und verschwieg die 500, die es gibt. Sie zählt jetzt genau das auf, was
 *     der Checkout verkauft — AgbTest::testTheTokenPackagesAreTheOnesThatCanBeBought
 *     vergleicht sie in allen Sprachen mit App\Landing\KeyPrice.
 *   - Weil sich der Vertragstext damit geändert hat, ist auch das "Stand:"-
 *     Datum weitergerückt.
 */

return [
    "heading" => "Condiciones generales de recarga de fichas (en Key)",
    "date" => "Estado: Agosto 2026",
    "translationNotice" => "Nota: Esta es una traducción de los términos y condiciones válidos en alemán. La versión jurídicamente vinculante puede consultarse en <a href=\":linkGerman\">aquí.</a>",
    "paragraphs" => [
        [
            "heading" => "Proveedor, ámbito de aplicación y modificaciones",
            "paragraphs" => [
                "Las siguientes Condiciones Generales se aplican a las relaciones comerciales entre los usuarios de los servicios de los sitios web metager.de y metager.org, en particular la recarga de tokens en la llave, y el operador SUMA-EV. En lo sucesivo, los \"usuarios\" de la recarga de tokens / la llave se denominarán también \"usuarios\", y SUMA-EV se denominará en lo sucesivo \"MetaGer\".",
                "Estas CGC están disponibles en todo momento en :agburl, se puede acceder a ellas, guardarlas e imprimirlas en cualquier momento. Los pedidos anteriores pueden consultarse en el área de clientes, en \"Gestionar llave - Pedidos\", introduciendo el ID de pago. Esto sólo es posible dentro de los 30 días siguientes a la fecha de compra.",
                "Estas condiciones se aplican exclusivamente a los usuarios que sean consumidores en el sentido del artículo 13 del Código Civil alemán. Se considera consumidor a toda persona física que celebre un negocio jurídico con fines predominantemente no comerciales ni autónomos.",
                "MetaGer se reserva el derecho a ampliar o restringir el grupo de usuarios y el grupo de participantes elegibles y se reserva además el derecho a modificar o complementar en cualquier momento estas condiciones generales para \"usuarios\" si fuera necesario en interés de un procesamiento sencillo o seguro o para evitar un uso indebido. Las modificaciones de las condiciones generales se anunciarán mediante su publicación en el sitio web de MetaGer. Si el usuario no está de acuerdo con dichas modificaciones o adiciones a las CGC, deberá oponerse a la modificación por escrito a MetaGer en el plazo de 4 semanas. En caso contrario, las CGC modificadas se considerarán aprobadas y, por tanto, pasarán a formar parte efectiva del contrato.",
                "El motor de búsqueda en línea metager.de, sus sitios asociados y el software asociado son operados por SUMA-EV. El domicilio social de SUMA-EV es Henniesruh 28D, 30655 Hannover. SUMA-EV está representada por el consejo de administración, que a su vez está representado por el director general Dominik Hebeler. Número de registro: VR200033, Tribunal de registro: Amtsgericht Hannover.",
                "Los datos de contacto son los siguientes\nTeléfono +49 511 34000070\nFax: +49 511 34001023\nFormulario de contacto: metager.de/kontakt\n*Número de teléfono fijo nacional.\n",
                "Según la normativa sobre resolución de litigios en línea en materia de consumo, nos remitimos al siguiente enlace: http://ec.europa.eu/consumers/odr/",
            ],
        ],
        [
            "heading" => "Celebración del contrato y condiciones de pago",
            "paragraphs" => [
                "La puesta a disposición de los diferentes paquetes de tokens por parte de MetaGer no constituye una oferta contractual jurídicamente vinculante, sino únicamente una invitación no vinculante al usuario a realizar una recarga o compra. Al hacer clic en el botón \"Realizar pago\" o en un texto comparable, el usuario presenta una oferta jurídicamente vinculante para celebrar un contrato de compra con MetaGer.",
                "Antes de enviar el pedido de forma vinculante, el usuario puede volver al sitio web donde se registra la información y corregir los errores de introducción o cancelar el proceso cerrando el navegador de Internet pulsando el botón \"Atrás\" del navegador de Internet utilizado tras comprobar sus datos.",
                "Los precios indicados incluyen el IVA legal y otros componentes del precio. Al tratarse de un servicio, no es necesario realizar ningún envío y las fichas se ponen a disposición inmediatamente después de finalizar el proceso de pago. El pago por adelantado es posible. Si el usuario ha optado por el pago por adelantado, se compromete a abonar el precio de compra inmediatamente después de la celebración del contrato.",
            ],
        ],
        [
            "heading" => "Garantía, lenguaje contractual y servicio de atención al cliente",
            "paragraphs" => [
                "Se aplican las disposiciones legales de garantía.",
                "El idioma del contrato es el alemán.",
                "Un servicio de atención al cliente para preguntas, quejas y objeciones está disponible los días laborables de 9:00 a 16:00 horas en los datos de contacto de SUMA-EV.",
            ],
        ],
        [
            "heading" => "Clave, opciones de pago y recarga",
            "paragraphs" => [
                "El usuario puede crear una cuenta de crédito, en lo sucesivo denominada clave, recargar crédito en ella y comprar así fichas. Las opciones de pago incluyen tarjeta de crédito y PayPal, entre otras. También es posible el pago en efectivo por correo a la dirección de MetaGer indicada anteriormente.",
                "Para utilizar una clave MetaGer y recargar tokens en ella, primero hay que crear la respectiva clave individual en el sitio web de MetaGer.",
                "Dependiendo del paquete seleccionado, el usuario recibe exactamente las fichas adquiridas para uso gratuito (ilimitado). Están disponibles las siguientes opciones de compra:",
                [
                    "500 fichas: 5 euros",
                    "1000 fichas: 10 euros",
                    "2000 fichas: 20 euros",
                    "3000 fichas: 30 euros",
                    "4000 fichas: 40 euros",
                    "6000 fichas: 60 euros",
                ],
                "A través de campañas de marketing con terceros como parte de campañas de socios y programas de fidelización de clientes, el usuario también puede recibir claves. En este caso, se aplicarán siempre las presentes CGC y, en su caso, las respectivas condiciones de la campaña.",
            ],
        ],
        [
            "heading" => "Validez y canje de fichas",
            "paragraphs" => [
                "Las fichas pueden ser canjeadas por cada usuario dentro del intervalo de validez especificado sin limitación alguna. La disponibilidad de las fichas adquiridas y la frecuencia con la que pueden canjearse dentro de un periodo determinado se indican en la página de resumen de la clave.",
                "A partir de la compra de las fichas, éstas son válidas durante dos años naturales. La fecha de validez se indica siempre en el resumen. Una vez expirada la validez, también expira la oferta.",
                "Tras adquirir un paquete de fichas, se carga directamente en la llave.",
                "Todas las recargas, así como todo el proceso, desde la creación de la clave hasta el canje de la ficha, son completamente anónimos. La única excepción son los datos necesarios para procesar el pago.",
                "Como prueba de la recarga, MetaGer tiene derecho a comprobar el proceso de pago.",
                "El usuario no está obligado en ningún momento a facilitar sus datos personales al recargar la clave. Toda la información que facilite a este respecto es voluntaria. No obstante, algunos datos personales pueden ser necesarios para la facturación y la tramitación del pago. En consecuencia, el usuario deberá facilitar toda la información de forma veraz.",
                "Los paquetes de tokens adquiridos y los tokens resultantes en una clave de MetaGer no son transferibles. Sin embargo, la transferencia de la clave respectiva por parte del usuario está expresamente permitida por MetaGer.",
            ],
        ],
        [
            "paragraphs" => [
                "MetaGer no se hace responsable de los daños resultantes del uso del servicio. MetaGer no garantiza ni asume responsabilidad alguna por la exactitud, integridad, fiabilidad, calidad y actualidad de otros sitios resultantes del uso de los servicios.",
                "MetaGer ofrece un servicio en línea.",
                "MetaGer ofrece voluntariamente la posibilidad de reembolsar el precio de compra de los tokens no utilizados, siempre que el método de pago utilizado por el usuario lo admita. Quedan excluidas las operaciones de pago en efectivo. El reembolso deberá ser solicitado por el usuario en el plazo de 30 días desde la finalización del proceso de compra. Para ello, debe introducirse el ID de pago correspondiente en la página de resumen.",
                "Las fichas caducadas por el paso del tiempo no son reembolsables.",
                "MetaGer se esfuerza siempre por mantener las funciones lo más disponibles posible. MetaGer no asume ninguna garantía ni responsabilidad por la disponibilidad de Internet o de la red móvil.",
                "MetaGer sólo responde por dolo y negligencia grave. Estas y las anteriores limitaciones de responsabilidad no se aplican a la responsabilidad por daños personales, a la responsabilidad según la Ley de Responsabilidad por Productos Defectuosos ni a la responsabilidad por incumplimiento de obligaciones contractuales esenciales. Las obligaciones contractuales esenciales son aquellas que son absolutamente necesarias para la correcta ejecución de un contrato, de modo que no se ponga en peligro la consecución del objeto del contrato, y en cuyo cumplimiento puede confiar regularmente el cliente. Si se incumple culposamente una obligación contractual esencial de este tipo, la responsabilidad se limita al daño típico contractual y previsible en el momento de la celebración del contrato.",
                "Todas las limitaciones y exclusiones de responsabilidad también se aplicarán en consecuencia a los representantes, empleados ejecutivos, órganos y demás auxiliares ejecutivos y asistentes de MetaGer.",
                "El usuario se compromete a no utilizar los servicios ofrecidos con fines abusivos. En particular, es abusivo facilitar datos personales de terceros con fines de engaño o para obtener ventajas.",
                "Si el usuario pretende utilizar el servicio más allá del ámbito doméstico habitual, deberá comunicarlo a MetaGer de manera informal, preferiblemente a través del formulario de contacto, al inicio de dicho uso.",
            ],
            "heading" => "Responsabilidad",
        ],
        [
            "heading" => "Disposiciones finales",
            "paragraphs" => [
                "Se aplica la legislación alemana. Queda excluida la aplicación de la Convención de las Naciones Unidas sobre los Contratos de Compraventa Internacional de Mercaderías.",
                "En caso de que una o varias disposiciones de las presentes Condiciones Generales fueran o devinieran inválidas, ello no afectará a la validez de las restantes disposiciones de las mismas. Las partes se comprometen a sustituir las disposiciones inválidas o nulas por nuevas disposiciones que se ajusten jurídicamente al contenido económico de las disposiciones inválidas o nulas. Lo mismo se aplica en caso de que se produzca una laguna en el contrato. Para llenar el vacío, las partes se comprometen a trabajar para establecer disposiciones apropiadas en este contrato que se acerquen lo más posible a lo que las partes habrían determinado de acuerdo con el significado y propósito de este contrato si hubieran considerado el punto. Si no se llega a un acuerdo, se aplicará supletoriamente la ley.",
            ],
        ],
    ],
];
