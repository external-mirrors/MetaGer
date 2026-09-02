<?php

/**
 * Was ein MetaGer-Schlüssel kostet — /preise.
 *
 * Aus pass/lang/<locale>/cost.json des Keymanagers übernommen, wo diese Seite
 * bis zum Umzug lag. Die Preiszahlen selbst stehen bewusst nicht hier: sie
 * kommen über App\Landing\KeyPrice vom Keymanager, weil der Checkout sie
 * ausgibt.
 */

return [
    "headings" => [
        "Esto es lo que cuesta tu llave MetaGer",
        "Lo más importante resumido",
    ],
    "texts" => [
        "Por cada búsqueda web sin publicidad en MetaGer con la configuración predeterminada se te cobrará <b>1 token</b>. Puedes recargar tu clave con uno de estos paquetes de tokens en cualquier momento.",
    ],
    "short-info" => [
        [
            "heading" => "Las fichas son válidas durante 2 años",
            "text" => "Las fichas que adquiera serán válidas hasta que se agoten. No existe una orden permanente.",
        ],
        [
            "heading" => "30 días de garantía de devolución del dinero",
            "text" => "Si no está satisfecho con su llave, dispone de 30 días tras la compra para devolver el crédito no utilizado.",
        ],
        [
            "heading" => "La clave se configura y utiliza automáticamente en el navegador",
            "text" => "No necesitas hacer nada más para utilizar tu llave MetaGer en la búsqueda. Tras cargarla, se configura automáticamente en tu navegador y recibirás información sobre cómo configurarla fácilmente en dispositivos adicionales.",
        ],
        [
            "heading" => "Sin seguimiento",
            "text" => "Utilice nuestra <a href=\":linkapp\">aplicación para Android</a>, o nuestra extensión para navegador y sea anónimo de forma demostrable utilizando <a href=\":linktokens\">tokens anónimos</a>.",
        ],
    ],
    "pricing" => [
        "heading" => "Así se componen nuestros precios",
        "texts" => [
            "La mayor parte de nuestros ingresos fluye directamente a los servicios de búsqueda consultados. Queremos ofrecer un concepto sostenible, lo que implica que los buscadores consultados no sufren ningún perjuicio económico por ofrecer resultados de búsqueda anónimos y sin publicidad para MetaGer. Además, hay una parte para cubrir nuestros costes de personal y de servidor y, por supuesto, las tasas de los proveedores de servicios de pago y los impuestos están incluidos en los precios.",
            "Así, al seleccionar los servicios de búsqueda que se van a consultar, no sólo puede fijar sus propios costes, sino también decidir al mismo tiempo qué proyectos quiere apoyar. De ahí también la facturación basada en tokens.",
        ],
    ],
    "payment-methods" => [
        "heading" => "Formas de pago",
        "texts" => [
            "Las claves de MetaGer han sido diseñadas por nosotros de tal manera que no requieren ningún dato personal. No obstante, a más tardar durante la ejecución de un pago, suelen requerirse algunos datos. Ya sea el IBAN de la cuenta pagadora, o la dirección de correo electrónico de la cuenta PayPal utilizada. El SUMA-EV no procesa estos datos por sí mismo y no los almacena. Sin embargo, dependiendo del método de pago, el proveedor del servicio de pago sí lo hace.",
            "Por lo tanto, nuestros métodos de pago están configurados de forma que sea necesario recopilar la menor cantidad de datos posible, y en algunos casos incluso ningún dato del usuario.",
        ],
        "anonymous" => "Métodos de pago anónimos",
        "more" => "Otras formas de pago",
    ],
    /**
     * Die Namen der Zahlungsarten. Standen im "checkout"-Namensraum des
     * Keymanagers, der dort bleibt — hierher kopiert, weil diese Seite die
     * einzige war, die sie außerhalb des Bezahlvorgangs gebraucht hat.
     */
    "methods" => [
        "cash" => "Efectivo",
        "prepay" => "Transferencia bancaria",
        "card" => "Tarjeta de crédito / débito",
    ],
];
