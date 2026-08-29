<?php

/**
 * Fragen zum MetaGer-Schlüssel — /hilfe/schluessel.
 *
 * Portugiesisch gab es im Keymanager nicht; aus de/en neu geschrieben.
 */

return [
    "heading" => "Perguntas sobre a chave MetaGer",
    "faqs" => [
        [
            "summary" => "Como funciona a chave MetaGer?",
            "description" => "Com uma chave MetaGer pesquisa sem publicidade. Recebe tokens dos quais é descontado um por cada pesquisa. Quando utiliza uma chave MetaGer, todos os mecanismos que protegem o MetaGer de chamadas automatizadas ficam desativados. Isto significa que não verá pedidos de captcha e que o seu endereço IP não será guardado por tempo limitado. Em resumo: o MetaGer fica mais rápido, mais fiável e mais seguro.",
        ],
        [
            "summary" => "Como funcionam os tokens anónimos?",
            "description" => "Pode utilizar os tokens anónimos com a nossa extensão para o navegador ou com a nossa aplicação. Isso permite-lhe pesquisar no MetaGer com ainda mais segurança. Ao utilizar tokens anónimos, uma parte do seu saldo é guardada no seu dispositivo sob a forma de palavras-passe aleatórias. Através de um <a href=\":tokenlink\">processo criptográfico complexo</a>, torna-se impossível até para nós relacionar as suas pesquisas entre si ou com a sua chave.",
        ],
        [
            "summary" => "Como utilizo a chave MetaGer?",
            "description" => "A chave MetaGer é configurada e utilizada automaticamente no navegador, pelo que não precisa de fazer mais nada. Se quiser utilizar a chave MetaGer noutros dispositivos, existem várias formas de a configurar:",
            "steps" => [
                [
                    "heading" => "Copiar o URL",
                    "description" => "Na página de gestão da chave MetaGer existe a opção de copiar um URL. Com esse URL pode guardar noutro dispositivo todas as definições do MetaGer, incluindo a chave MetaGer.",
                ],
                [
                    "heading" => "Guardar um ficheiro",
                    "description" => "Na página de gestão da chave MetaGer existe a opção de guardar um ficheiro. Isso grava a sua chave MetaGer como ficheiro. Pode depois utilizar esse ficheiro noutro dispositivo para iniciar sessão com a sua chave.",
                ],
                [
                    "heading" => "Ler o código QR",
                    "description" => "Em alternativa, pode também ler o código QR apresentado na página de gestão para iniciar sessão noutro dispositivo.",
                ],
                [
                    "heading" => "Introduzir a chave MetaGer manualmente",
                    "description" => "Naturalmente, também pode introduzir a chave manualmente noutro dispositivo.",
                ],
            ],
        ],
        [
            "summary" => "Tenho de introduzir a minha chave com frequência. O que posso fazer?",
            "description" => "Pedimos ao seu navegador que guarde permanentemente a chave depois de a ter gerado ou de ter iniciado sessão. Conforme a configuração do seu navegador, pode tê-lo definido para apagar regularmente cookies e dados de sites, o que evidentemente também termina a sua sessão no MetaGer. Tem as seguintes opções:",
            "steps" => [
                [
                    "heading" => "Adicionar uma exceção",
                    "description" => "Nas definições do Firefox pode colocar o MetaGer numa lista de exceções para a eliminação de cookies e dados de sites, o que mantém a sua sessão iniciada.",
                ],
                [
                    "heading" => "Instalar a nossa extensão para o navegador",
                    "description" => "A nossa extensão para <a href=\"https://addons.mozilla.org/firefox/addon/metager-suche/\" target=\"_blank\" rel=\"noopener\">Firefox</a> e <a href=\"https://chrome.google.com/webstore/detail/gjfllojpkdnjaiaokblkmjlebiagbphd\" target=\"_blank\" rel=\"noopener\">Chrome</a> guarda as suas definições de pesquisa, incluindo a chave, sem utilizar cookies. Assim pode apagar todos os dados do navegador sem terminar a sessão no MetaGer.",
                ],
                [
                    "heading" => "Iniciar sessão sem escrever a chave de 36 caracteres",
                    "description" => "Se utilizar um gestor de palavras-passe, pode guardar aí a chave para iniciar sessão automaticamente. Em alternativa, disponibilizamos um <a href=\":keylink\">URL de definições</a> que pode guardar, por exemplo, como marcador. Ao ser aberto, esse URL inicia a sessão sem que tenha de introduzir a chave manualmente.",
                ],
            ],
        ],
        [
            "summary" => "Não estou satisfeito com a chave MetaGer. O que posso fazer?",
            "description" => "Nesse caso, pode pedir o reembolso dos tokens não utilizados no prazo de 30 dias após a compra. Para isso precisa do seu ID de pagamento. Para pedir o reembolso, abra a página de gestão da chave MetaGer, clique no item de menu «Encomendas» e introduza o seu ID de pagamento. Em seguida pode clicar no botão «Pedir reembolso» e enviar o pedido.",
        ],
        [
            "summary" => "Como pesquiso de forma completamente anónima?",
            "description" => "A sua privacidade e o seu anonimato são muito importantes para nós. Por isso oferecemos formas de pagamento anónimas (dinheiro). Oferecemos também a utilização de <a href=\":tokenlink\">tokens anónimos</a>, com os quais pode inclusivamente pesquisar de forma comprovadamente anónima.",
        ],
        [
            "summary" => "Preciso de uma fatura. Como a obtenho?",
            "description" => "Para isso só precisa do seu ID de pagamento. Para pedir a fatura, abra a página de gestão da chave MetaGer, clique no item de menu «Encomendas» e introduza o seu ID de pagamento. Pode então clicar no botão «Pedir fatura» e iniciar o pedido. Para a fatura precisamos do seu nome completo, do seu endereço de e-mail e da sua morada.",
        ],
        [
            "summary" => "Gostaria que a minha chave MetaGer fosse carregada automaticamente. Como faço?",
            "description" => "Aos nossos membros, a chave incluída na quota é carregada automaticamente todos os meses. O número de tokens depende do valor da quota paga.",
        ],
        [
            "summary" => "Recebi um cartão ou uma ligação com um código promocional. O que faço com ele?",
            "description" => "Algumas organizações oferecem chaves MetaGer com um saldo fixo através de cartões promocionais ou de uma ligação. Abra <a href=\":voucherlink\">a nossa página de resgate</a>, introduza o código impresso ou leia o código QR do cartão. Receberá de imediato uma nova chave MetaGer com o saldo oferecido, válido por tempo limitado. Cada código só pode ser resgatado uma vez.",
        ],
    ],
    "more-questions" => "Tem mais perguntas? Utilize então o nosso <a href=\":contactlink\" target=\"_blank\">formulário de contacto</a>.",
];
