<?php

/**
 * Was ein MetaGer-Schlüssel kostet — /preise.
 *
 * Portugiesisch gab es im Keymanager nicht; aus de/en neu geschrieben, wie in
 * Schritt 1 für index.php. Ohne diese Datei bekämen portugiesische Besucher die
 * deutsche Fassung — config/app.php fällt auf "de" zurück.
 */

return [
    "headings" => [
        "Isto é o que custa a sua chave MetaGer",
        "O mais importante em resumo",
    ],
    "texts" => [
        "Por cada pesquisa web sem publicidade no MetaGer com as definições predefinidas é-lhe cobrado <b>1 token</b>. Pode carregar a sua chave a qualquer momento com um destes pacotes de tokens.",
    ],
    "short-info" => [
        [
            "heading" => "Os tokens mantêm-se válidos durante 2 anos",
            "text" => "Os tokens que comprou foram concebidos para se manterem válidos até serem gastos. Não existe qualquer subscrição.",
        ],
        [
            "heading" => "Garantia de devolução do dinheiro em 30 dias",
            "text" => "Se não estiver satisfeito com a sua chave, tem 30 dias após a compra para devolver o saldo não utilizado.",
        ],
        [
            "heading" => "A chave é configurada e utilizada automaticamente no navegador",
            "text" => "Não precisa de fazer mais nada para utilizar a sua chave MetaGer na pesquisa. Depois de a carregar, fica automaticamente configurada no seu navegador e receberá indicações para a configurar facilmente noutros dispositivos.",
        ],
        [
            "heading" => "Sem rastreio",
            "text" => "Utilize a nossa <a href=\":linkapp\">aplicação Android</a> ou a nossa extensão para o navegador e navegue de forma comprovadamente anónima com <a href=\":linktokens\">tokens anónimos</a>.",
        ],
    ],
    "pricing" => [
        "heading" => "É assim que os nossos preços são compostos",
        "texts" => [
            "A maior parte das nossas receitas vai diretamente para os serviços de pesquisa que consulta. Queremos oferecer um modelo sustentável, o que implica que os motores de busca consultados não sofram prejuízo financeiro por fornecerem ao MetaGer resultados de pesquisa anónimos e sem publicidade. A isto acresce uma parte que cobre os nossos custos de pessoal e de servidores e, evidentemente, as comissões dos prestadores de serviços de pagamento e os impostos estão incluídos nos preços.",
            "Assim, ao escolher os serviços de pesquisa a consultar, não define apenas os seus próprios custos: decide ao mesmo tempo que projetos quer apoiar. Daí também a faturação baseada em tokens.",
        ],
    ],
    "payment-methods" => [
        "heading" => "Formas de pagamento",
        "texts" => [
            "As chaves MetaGer foram concebidas por nós de modo a não necessitarem de quaisquer dados pessoais. Ainda assim, o mais tardar durante a execução de um pagamento, são normalmente necessários alguns dados. Seja o IBAN da conta pagadora ou o endereço de e-mail da conta PayPal utilizada. A SUMA-EV não processa nem armazena estes dados. O prestador de serviços de pagamento, porém, fá-lo consoante a forma de pagamento.",
            "Por isso, as nossas formas de pagamento estão configuradas de modo a que seja necessário recolher o menos possível — nalguns casos, mesmo nenhuns — dados dos utilizadores.",
        ],
        "anonymous" => "Formas de pagamento anónimas",
        "more" => "Outras formas de pagamento",
    ],
    /**
     * Die Namen der Zahlungsarten. Standen im "checkout"-Namensraum des
     * Keymanagers, der dort bleibt — hierher kopiert, weil diese Seite die
     * einzige war, die sie außerhalb des Bezahlvorgangs gebraucht hat.
     */
    "methods" => [
        "cash" => "Numerário",
        "prepay" => "Transferência bancária",
        "card" => "Cartão de crédito / débito",
    ],
];
