<?php

/**
 * Anonyme Token — /hilfe/anonyme-token.
 *
 * Aus dem "anonymous-token"-Zweig von pass/lang/<locale>/help.json.
 * Der Pfad /keys/help/anonymous-token wird dauerhaft hierher weitergeleitet:
 * er steht in bereits versandten Mitglieds-Willkommensmails.
 */

return [
    "description" => [
        "heading" => "¿Qué son las fichas anónimas?",
        "text" => "Si utiliza una clave MetaGer, recibirá una contraseña generada aleatoriamente que su navegador nos envía con cada consulta de búsqueda para que podamos habilitar la búsqueda sin anuncios. Si utiliza nuestra <a href=\"/app\" target=\"_blank\">aplicación para Android</a>, o nuestra extensión web para <a href=\"https://chrome.google.com/webstore/detail/gjfllojpkdnjaiaokblkmjlebiagbphd\" target=\"_blank\" rel=\"noopener\">Chrome</a> y <a href=\"https://addons.mozilla.org/firefox/addon/metager-suche/\" target=\"_blank\" rel=\"noopener\">Firefox</a>, en lugar de la clave, su navegador nos envía una contraseña generada aleatoriamente (token anónimo) con cada solicitud de búsqueda para autenticación, que se genera localmente. Esto garantiza que cada contraseña es única y no tiene conexión con la clave real de MetaGer, ni entre las contraseñas individuales.",
    ],
    "problem" => [
        "heading" => "¿Qué problema se supone que resuelven las fichas anónimas?",
        "text" => "Si su navegador nos envía siempre la misma clave con cada consulta de búsqueda, al menos teóricamente tendríamos la posibilidad de establecer una correlación entre todas las búsquedas realizadas con la misma clave. Aunque no lo hiciéramos, por supuesto, la confianza seguiría siendo necesaria para estar seguros de su búsqueda anónima. Para que no sólo tengamos que prometer la búsqueda anónima, sino que también podamos demostrarla, hemos introducido los tokens anónimos.",
    ],
    "general-function" => [
        "heading" => "¿Cómo funciona?",
        "texts" => [
            "Así que queremos tener contraseñas de un solo uso generadas directamente desde su dispositivo final, que luego nos envía para la autenticación durante sus búsquedas. Sin embargo, para cada token anónimo en su dispositivo final, tenemos que asegurarnos de que un token regular se ha restado de su clave MetaGer para ello, sin (y este es el quid) decirnos qué clave MetaGer se utilizó para generar el token anónimo.",
            "Tradicionalmente, utilizaríamos algún tipo de firma criptográfica para este fin. En este caso, firmaríamos el código anónimo generado. Entonces, cuando nos envíes el token anónimo junto con la firma más adelante, podremos estar seguros de que el token anónimo es válido. Sin embargo, para obtener la firma, nos habrías enviado el token anónimo junto con tu clave real, lo que anularía el anonimato.",
            "Por lo tanto, en su lugar utilizamos una forma modificada de firma criptográfica, la denominada <a href=\"https://en.wikipedia.org/wiki/Blind_signature\" target=\"_blank\">blind signature</a>. Para crear una analogía de la vida real, es como si nos enviaras tu firma anónima en un sobre de papel carbón. En este ejemplo, no podríamos abrir el sobre, pero sí firmar desde el exterior, por lo que nuestra firma se transferiría al token anónimo del interior. Cuando recibiéramos el sobre, podríamos retirarlo y devolvernos más tarde la contraseña y la firma. Así podríamos confirmar que se trata efectivamente de nuestra firma.",
            "De hecho, esta analogía es un poco engañosa, porque en el proceso real, en el momento en que nos envías el token anónimo y la firma, no sólo no hemos visto nunca antes el token anónimo, sino que tampoco hemos visto nunca la propia firma. Y, sin embargo, podemos verificar que la firma ha sido generada por nosotros.",
        ],
    ],
    "meaning" => [
        "texts" => [
            "Utilizando el algoritmo descrito, tanto nosotros como usted podemos garantizar que cada vez se utilice una nueva contraseña aleatoria no relacionada con su clave MetaGer para sus búsquedas autenticadas.",
            "Lo especial de este algoritmo es que todos los componentes que garantizan el anonimato se ejecutan localmente en tu dispositivo. Este código fuente ejecutado puede ser visto y verificado por cualquiera en cualquier momento.",
            "Lo mejor de todo es que no necesitas configurar nada para usar tokens anónimos. Basta con instalar/utilizar nuestra extensión para navegador/aplicación para Android para que tu dispositivo utilice tokens anónimos en todas las búsquedas.",
        ],
        "heading" => "¿Qué significa esto para tus búsquedas autenticadas?",
    ],
    "technical-function" => [
        "heading" => "El algoritmo que hay detrás:",
        "texts" => [
            "En una firma RSA clásica, tomaríamos el testigo anónimo <code>m</code>, el exponente secreto <code>d</code>, y el módulo público <code>N</code> de nuestra clave privada y crearíamos la firma utilizando <code>m^d (mod N)</code>. Sin embargo, queremos que <code>m</code> permanezca secreto.",
            "Por lo tanto, su terminal crea un número aleatorio <code>r</code> utilizando un generador de números aleatorios, que no está relacionado con el divisor de <code>N</code>. Por lo tanto, el máximo común divisor de <code>r</code> y <code>N</code> debe ser <code>1</code>.",
            "Dado que <code>r</code> es un número aleatorio, se deduce que <code>m'</code> no revela ninguna información sobre el token anónimo almacenado localmente <code>m</code>.",
            "Nuestro servidor recibe ahora el token anónimo ofuscado <code>m'</code> de tu dispositivo final junto con la clave MetaGer a utilizar. Restamos un token de la clave y enviamos la firma también ofuscada <code>s'&Congruent; (m')^d (mod N)</code> de vuelta a tu dispositivo final.",
            "Tu terminal puede calcular ahora la firma RSA válida real <code>s</code> para el token anónimo sin cifrar: <code>s&Congruent; s' r^-1 (mod N)</code>. Esto funciona porque para claves RSA, <code>r^(e*d)&Congruent; r (mod N)</code>. Y por tanto también: <code>s &Congruent; s' * r^-1 &Congruent; (m')^d*r^-1 &Congruent; m^d*r^(e*d)*r^-1 &Congruent; m^d*r*r^-1 &Congruent; m^d (mod N)</code>.",
            "Su dispositivo final nos envía ahora el token anónimo sin cifrar junto con la firma asociada para su autorización durante una búsqueda. La clave en sí ya no se nos envía durante la búsqueda.",
        ],
    ],
    "heading" => "Fichas anónimas",
];
