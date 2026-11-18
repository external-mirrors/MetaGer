<?php
return [
    'tor' => [
        'title' => 'Serviço Oculto TOR <a title="For easy help, click here" href="/hilfe/easy-language/privacy-protection#eh-torhidden" ><img class="easy-help-icon lm-only" src="/img/help-questionmark-icon-lm.svg"/><img class="easy-help-icon dm-only" src="/img/help-questionmark-icon-dm.svg"/></a>',
        '2' => "Pode aceder ao MetaGer no browser Tor em: http://metagerv65pwclop2rsfzg4jwowpavpwd6grhhlvdgsswvo6ii4akgyd.onion .",
        '1' => "Durante muitos anos, o MetaGer tem estado a esconder e a não armazenar endereços IP. No entanto, estes endereços estão temporariamente visíveis no servidor do MetaGer enquanto uma pesquisa está a decorrer: se o MetaGer for comprometido, um atacante poderá ler e armazenar os seus endereços. Para cumprir os mais elevados requisitos de segurança, utilizamos uma instância do MetaGer na rede Tor: o MetaGer TOR Hidden Service - acessível através de: <a href=\"/tor/\" target=\"_blank\" rel=\"noopener\">https://metager.de/tor/</a>. Para o utilizar, é necessário um browser especial, que pode ser descarregado a partir de <a href=\"https://www.torproject.org/\" target=\"_blank\" rel=\"noopener\">https://www.torproject.org/</a>.",
    ],
    'content' => [
        'title' => 'Conteúdo questionável / Proteção dos jovens <a title="For easy help, click here" href="/help/easy-language/privacy-protection#eh-content" ><img class="easy-help-icon lm-only" src="/img/help-questionmark-icon-lm.svg"/><img class="easy-help-icon dm-only" src="/img/help-questionmark-icon-dm.svg"/></a>',
        'explanation' => [
            '2' => 'Se encontrar algo na Internet que considere ilegal ou prejudicial para menores, pode contactar <a href="mailto:hotline@jugendschutz.net" target="_blank" rel="noopener">hotline@jugendschutz.net</a> por correio eletrónico ou visitar <a href="http://www.jugendschutz.net/" target="_blank" rel="noopener">www.jugendschutz.net</a> e preencher o formulário de queixa aí disponível. É útil fornecer uma breve nota sobre o que considera inadmissível e como se deparou com esse conteúdo. Também pode denunciar conteúdos questionáveis diretamente a nós. Para o fazer, envie uma mensagem de correio eletrónico para o nosso responsável pela proteção dos jovens (<a href="mailto:jugendschutz@metager.de" target="_blank" rel="noopener">jugendschutz@metager.de</a>).',
            '1' => 'Recebi "hits" que não só considero incómodos como também contêm, na minha opinião, conteúdos ilegais!',
        ],
    ],
    'privacy' => [
        '1' => 'Cookies de rastreio, IDs de sessão e endereços IP <a title="For easy help, click here" href="/hilfe/easy-language/privacy-protection#eh-tracking" ><img class="easy-help-icon lm-only" src="/img/help-questionmark-icon-lm.svg"/><img class="easy-help-icon dm-only" src="/img/help-questionmark-icon-dm.svg"/></a>',
        '3' => "Mais informações podem ser encontradas abaixo. As funções estão acessíveis em \"Serviços\" na barra de navegação.",
        '2' => 'Nenhum destes dados é utilizado, armazenado, retido ou processado aqui na MetaGer (exceção: armazenamento a curto prazo para proteção contra ataques de hackers e bots). Porque consideramos este tópico extremamente importante, também criámos formas de o ajudar a atingir o mais alto nível de segurança: o serviço MetaGer TOR Hidden e o nosso navegador anónimo.',
        'title' => "Anonimato e segurança dos dados",
    ],
    'proxy' => [
        'title' => 'Abrir anonimamente (SafeBrowse) <a title="For easy help, click here" href="/hilfe/easy-language/privacy-protection#eh-proxy" ><img class="easy-help-icon lm-only" src="/img/help-questionmark-icon-lm.svg"/><img class="easy-help-icon dm-only" src="/img/help-questionmark-icon-dm.svg"/></a>',
        'intro' => 'Com «Abrir anonimamente» visita um site sem que ele chegue a ver o seu dispositivo. A página é aberta num navegador que funciona nos nossos servidores: o SafeBrowse. O site só lida com esse navegador e não fica a saber nem o seu endereço IP nem nada sobre o seu dispositivo, o seu navegador ou os seus cookies.',
        'use' => [
            'title' => 'Como abrir uma página anonimamente',
            'text' => [
                'Clique em «Abrir anonimamente» (o ícone do escudo) num resultado. A página abre num novo separador.',
                'Se quiser abrir todos os resultados anonimamente, ative «Abrir resultados anonimamente» nas definições. Assim, cada link de um resultado passa pelo SafeBrowse, e com «Abrir diretamente» visita uma página sem o SafeBrowse.',
            ],
        ],
        'session' => [
            'title' => 'A sessão',
            'text' => [
                'O primeiro resultado aberto anonimamente inicia uma sessão. Cada um dos seguintes abre na mesma sessão, num separador próprio. Aí continua a navegar normalmente: seguir links, preencher formulários, escrever um novo endereço ou um termo de pesquisa na barra de endereço. Continua protegido enquanto o faz.',
                'Uma sessão custa tokens da sua chave MetaGer; quantos e por quanto tempo é indicado antes de começar. Enquanto usar a sessão, ela é prolongada automaticamente. Quando deixar de a usar, termina sozinha pouco depois, ou termina-a com «Fechar sessão».',
                'Quando a sessão termina, o navegador nos nossos servidores é apagado com tudo o que lá estava: páginas visitadas, cookies, inícios de sessão e ficheiros transferidos. A sessão seguinte começa com um navegador novo e vazio. Por isso, guarde antes os ficheiros transferidos no seu dispositivo em «Transferências».',
            ],
        ],
        'views' => [
            'title' => 'Duas vistas',
            'text' => [
                '«Página» mostra uma cópia do site sem os seus scripts. Nela lê, desloca, pesquisa e copia como em qualquer página; os seus cliques e o que escreve vão para o navegador nos nossos servidores. «Navegador completo» mostra esse navegador como imagem, com todos os seus separadores. Aí tudo funciona, incluindo vídeos e páginas que a «Página» não consegue mostrar. Alterna entre as duas na barra no topo.',
            ],
        ],
        'nojs' => [
            'title' => 'Sem JavaScript',
            'text' => [
                'Mesmo com o JavaScript desativado no seu navegador, «Abrir anonimamente» abre a página no SafeBrowse. Vê então uma cópia da página em que os links, os botões e os formulários funcionam. É recarregada após cada clique, mas não muda sozinha. Vídeos, diálogos da página e o envio de ficheiros não funcionam desta forma. Se o seu navegador bloquear cookies, os resultados que abre a partir de uma página de resultados continuam a ir para a mesma sessão, também se continuar a pesquisar a partir dela.',
            ],
        ],
        'limits' => [
            'title' => 'O que «Abrir anonimamente» não faz',
            'text' => [
                'O site não o reconhece pelo seu dispositivo, mas sim pelo que lhe disser: se iniciar sessão ou introduzir dados pessoais, sabe quem é. Se também não se quiser mostrar ao MetaGer, use o MetaGer através do Tor (ver acima).',
            ],
        ],
    ],
    'easy-help' => 'Ao clicar no símbolo <a title="For easy help, click here" href="/hilfe/easy-language/privacy-protection" ><img class="easy-help-icon lm-only" src="/img/help-questionmark-icon-lm.svg"/><img class="easy-help-icon dm-only" src="/img/help-questionmark-icon-dm.svg"/></a> , acederá a uma versão simplificada da ajuda.',
    'maps' => [
        '1' => 'Preservar a privacidade na era dos gigantes globais de dados também nos levou a desenvolver <a href="https://maps.metager.de" target="_blank">https://maps.metager.de</a>: o (até onde sabemos) único planeador de rotas que oferece todas as funcionalidades através de um navegador e de uma aplicação sem armazenar as localizações dos utilizadores. Tudo isto é verificável porque o nosso software é de código aberto. Para utilizar o maps.metager.de, recomendamos a nossa versão rápida da aplicação. Pode descarregar as nossas aplicações a partir de <a href="/app" target="_blank">aqui</a> (ou, claro, também a partir da Play Store).',
        '3' => "Aquando do carregamento, o mapa apresenta os pontos (POIs = Pontos de Interesse) encontrados pelo MetaGer, que também são listados na coluna da direita. Ao fazer zoom, esta lista adapta-se à secção do mapa. Ao passar o rato sobre um marcador no mapa ou na lista, o item correspondente é realçado. Clique em 'Detalhes' para obter mais informações sobre esse ponto a partir da base de dados abaixo.",
        '2' => "Esta função de mapa também pode ser acedida a partir da pesquisa no MetaGer (e vice-versa). Depois de ter pesquisado um termo no MetaGer, verá um novo foco de pesquisa \"Mapas\" no canto superior direito. Clicando nele, acederá a um mapa correspondente.",
        'title' => "Mapas MetaGer",
    ],
    'backarrow' => 'Voltar',
    'title' => 'MetaGer - Ajuda',
];
