<?php

/**
 * Anonyme Token — /hilfe/anonyme-token.
 *
 * Portugiesisch gab es im Keymanager nicht; aus de/en neu geschrieben.
 * Der Pfad /keys/help/anonymous-token wird dauerhaft hierher weitergeleitet:
 * er steht in bereits versandten Mitglieds-Willkommensmails.
 */

return [
    "heading" => "Tokens anónimos",
    "description" => [
        "heading" => "O que são tokens anónimos?",
        "text" => "Se utilizar uma chave MetaGer, recebe uma palavra-passe gerada aleatoriamente que o seu navegador nos envia com cada pesquisa, para que possamos ativar a pesquisa sem publicidade. Se utilizar a nossa <a href=\"/app\" target=\"_blank\">aplicação Android</a> ou a nossa extensão para <a href=\"https://chrome.google.com/webstore/detail/gjfllojpkdnjaiaokblkmjlebiagbphd\" target=\"_blank\" rel=\"noopener\">Chrome</a> e <a href=\"https://addons.mozilla.org/firefox/addon/metager-suche/\" target=\"_blank\" rel=\"noopener\">Firefox</a>, em vez dessa palavra-passe o seu navegador envia-nos com cada pesquisa uma palavra-passe gerada aleatoriamente (token anónimo), criada localmente, para autenticação. Assim garante-se que cada palavra-passe é única e não tem qualquer ligação com a verdadeira chave MetaGer nem com as restantes palavras-passe.",
    ],
    "problem" => [
        "heading" => "Que problema resolvem os tokens anónimos?",
        "text" => "Se o seu navegador nos enviasse sempre a mesma palavra-passe em cada pesquisa, teríamos pelo menos em teoria a possibilidade de relacionar entre si todas as pesquisas feitas com a mesma chave. Mesmo que não o façamos, seria ainda assim necessária confiança para ter a certeza de que a sua pesquisa é anónima. Para que a pesquisa anónima não seja apenas uma promessa nossa, mas algo que podemos demonstrar, introduzimos os tokens anónimos.",
    ],
    "general-function" => [
        "heading" => "Como funciona?",
        "texts" => [
            "Queremos, portanto, que as palavras-passe de utilização única sejam geradas diretamente no seu dispositivo e que nos sejam enviadas para autenticação durante as pesquisas. Só que, para cada token anónimo no seu dispositivo, temos de garantir que foi descontado um token normal da sua chave MetaGer — sem (e aqui está a dificuldade) ficarmos a saber que chave MetaGer foi usada para gerar esse token anónimo.",
            "Tradicionalmente, usaríamos para isso alguma forma de assinatura criptográfica: assinaríamos o token anónimo gerado. Quando mais tarde nos enviasse o token anónimo juntamente com a assinatura, poderíamos ter a certeza de que o token é válido. Só que, para obter a assinatura, ter-nos-ia enviado o token anónimo juntamente com a sua chave verdadeira, o que anularia o anonimato.",
            "Por isso usamos uma forma modificada de assinatura criptográfica, a chamada <a href=\"https://pt.wikipedia.org/wiki/Assinatura_cega\" target=\"_blank\">assinatura cega</a>. Numa analogia do mundo real, é como se nos enviasse o seu token anónimo dentro de um envelope com papel químico. Neste exemplo, não conseguiríamos abrir o envelope, mas conseguiríamos assinar por fora, e a nossa assinatura passaria para o token anónimo lá dentro. Ao receber o envelope de volta, poderia retirá-lo e enviar-nos mais tarde a palavra-passe e a assinatura. Poderíamos então confirmar que a assinatura é de facto nossa.",
            "Na verdade, esta analogia é um pouco enganadora, porque no processo real, no momento em que nos envia o token anónimo e a assinatura, não só nunca vimos o token anónimo como também nunca vimos a própria assinatura. E ainda assim conseguimos verificar que foi gerada por nós.",
        ],
    ],
    "meaning" => [
        "heading" => "O que significa isto para as suas pesquisas autenticadas?",
        "texts" => [
            "Graças ao algoritmo descrito, tanto nós como o utilizador podemos ter a certeza de que, em cada pesquisa autenticada, é usada uma nova palavra-passe aleatória sem qualquer relação com a sua chave MetaGer.",
            "O que este algoritmo tem de especial é que todos os componentes que garantem o anonimato são executados localmente no seu dispositivo. Esse código-fonte pode ser consultado e verificado por qualquer pessoa a qualquer momento.",
            "E o melhor: não precisa de configurar nada para utilizar tokens anónimos. Basta instalar e usar a nossa extensão para o navegador ou a nossa aplicação Android para que o seu dispositivo passe a usar tokens anónimos em todas as pesquisas.",
        ],
    ],
    "technical-function" => [
        "heading" => "O algoritmo por detrás:",
        "texts" => [
            "Numa assinatura RSA clássica, pegaríamos no token anónimo <code>m</code>, no expoente secreto <code>d</code> e no módulo público <code>N</code> da nossa chave privada e criaríamos a assinatura através de <code>m^d (mod N)</code>. Só que queremos que <code>m</code> permaneça secreto.",
            "Por isso, o seu dispositivo cria com um gerador de números aleatórios um número <code>r</code> que é primo em relação a <code>N</code>. O máximo divisor comum de <code>r</code> e <code>N</code> tem, portanto, de ser <code>1</code>.",
            "Como <code>r</code> é um número aleatório, daí resulta que <code>m'</code> não revela qualquer informação sobre o token anónimo <code>m</code> guardado localmente.",
            "O nosso servidor recebe agora do seu dispositivo o token anónimo ocultado <code>m'</code>, juntamente com a chave MetaGer a utilizar. Descontamos um token da chave e devolvemos ao seu dispositivo a assinatura igualmente ocultada <code>s'&Congruent; (m')^d (mod N)</code>.",
            "O seu dispositivo pode agora calcular a assinatura RSA válida <code>s</code> para o token anónimo não ocultado: <code>s&Congruent; s' r^-1 (mod N)</code>. Isto funciona porque, para chaves RSA, <code>r^(e*d)&Congruent; r (mod N)</code>. E por isso também: <code>s &Congruent; s' * r^-1 &Congruent; (m')^d*r^-1 &Congruent; m^d*r^(e*d)*r^-1 &Congruent; m^d*r*r^-1 &Congruent; m^d (mod N)</code>.",
            "Durante uma pesquisa, o seu dispositivo envia-nos então o token anónimo não ocultado juntamente com a assinatura correspondente, para autorização. A chave em si já não nos é enviada durante a pesquisa.",
        ],
    ],
];
