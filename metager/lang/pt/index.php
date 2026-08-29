<?php
return [
    'searchbar-replacement' => [
        'tagline' => 'Open source. Sem anúncios. Anónimo.',
        'message' => 'A sua chave é o seu acesso – sem conta, sem endereço de e-mail. O saldo e as definições dependem dela.',
        'first_time' => 'É a primeira vez aqui?',
        'start' => 'Configurar uma chave',
        // Swapped in by resources/js/accountBreadcrumb.js when this browser has
        // rendered a signed-in page before. Three strings, replacing three
        // elements in place — nothing appears, nothing moves.
        'welcome_back' => 'Bem-vindo de volta.',
        'welcome_back_message' => 'Já iniciou sessão neste dispositivo. Inicie sessão com a mesma chave – o seu saldo continua lá.',
        'welcome_back_button' => 'Iniciar sessão novamente',
        'have_key' => 'Iniciar sessão com a minha chave',
        'payment_id_error' => "Introduziu um ID de pagamento que não é uma chave correta. A sua chave tem 36 caracteres.",
        'key_error' => "A chave introduzida não é válida. Por favor, verifique a entrada.",
        'login_code_error' => "O código de início de sessão introduzido não era válido. Dica: os códigos de início de sessão só são válidos enquanto estiverem visíveis noutro dispositivo!",
        'login' => "Iniciar sessão",
        'new_key' => 'Ainda não tem chave?',
        'extension' => 'Mantenha-se ligado e anónimo com a nossa extensão Web',
    ],
    'plugin-title' => 'Adicionar MetaGer ao seu browser',
    'key' => [
        'tooltip' => [
            'low' => 'A ficha esgotou-se rapidamente. Recarregar agora.',
            'nokey' => 'Configurar uma pesquisa sem anúncios',
            'empty' => 'Token usado. Recarregar agora.',
            'full' => 'Pesquisa sem anúncios activada.',
        ],
        'placeholder' => 'Introduza a sua chave MetaGer para iniciar a pesquisa.',
    ],
    'foki' => [
        'produkte' => 'Produtos',
        'bilder' => 'Imagens',
        'web' => 'Web',
        'nachrichten' => 'Notícias',
        'science' => 'Ciência',
        'maps' => 'Mapas',
    ],
    'skip' => [
        'fokus' => 'Saltar para a seleção do foco de pesquisa',
        'navigation' => 'Saltar para a navegação',
        'search' => 'Saltar para a introdução da consulta de pesquisa',
    ],
    'searchreset' => 'eliminar a entrada da consulta de pesquisa',
    'placeholder' => 'MetaGer: Pesquisa e localização protegidas pela privacidade',
    'adfree' => 'MetaGer sem anúncios',
    'searchbutton' => 'Iniciar MetaGer-Search',
    'plugin' => 'Instalar o MetaGer',
    'lang' => 'linguagem wwitch',
    // The landing page shown to a visitor without a key: hero, "how it works",
    // and the five benefit cards. It came from the keymanager's own root page
    // (pass/views/index.ejs, pass/lang/*/index.json), which /keys used to serve
    // and which now redirects here.
    //
    // Placeholders are Laravel's :name, not i18next's {{name}}, and the links
    // are passed in from parts/landing/* so the locale prefix and the /keys
    // paths stay in one place.
    'landing' => [
        'title' => 'MetaGer: pesquise e navegue na web sem ser observado',
        'description' => 'O MetaGer respeita a sua privacidade: sem anúncios, sem rastreio, sem registos. E agora também pode visitar qualquer site de forma anónima.',
        'advantages' => [
            'ads' => 'Sem anúncios',
            'tracking' => 'Sem rastreio',
            'logging' => 'Sem registos',
            'compromise' => 'Sem compromissos',
        ],
        'calltoaction' => 'Como funciona',
        'benefits' => [
            'browsing' => [
                'heading' => 'Não só pesquisa anónima — também navegação anónima',
                'description' => 'Com a sua chave MetaGer pode também abrir qualquer site num navegador privado que corre em segurança nos nossos servidores, e não no seu dispositivo. Os sites não conseguem saber quem é nem de onde está a navegar, e tudo é apagado automaticamente quando a sessão termina. Sem instalação, sem configuração — basta abrir e começar.',
                'fingerprinting' => 'Fingerprinting',
                'tracking' => 'Rastreio',
            ],
            'ads' => [
                'heading' => 'Sem anúncios',
                'description' => 'Publicidade e privacidade raramente se dão bem. Por isso não existe qualquer publicidade no MetaGer, o que nos permite proteger a sua privacidade sem compromissos.',
                'ads' => 'Publicidade',
                'tracking' => 'Ligações de rastreio',
            ],
            'logging' => [
                'heading' => 'Sem registos',
                'description' => 'Pesquisar na Internet costuma deixar um rasto de dados. Não precisamos de guardar nada disso: o nosso motor de busca foi feito de forma a que combater spam não exija registos. Também não vai encontrar um único captcha no nosso site, mesmo usando uma VPN.',
                'logging' => 'Registos',
            ],
            'compromise' => [
                'heading' => 'Sem compromissos',
                'description' => 'Em vez de uma conta associada aos seus dados pessoais, recebe simplesmente uma chave gerada aleatoriamente, sem nome nem endereço de e-mail. Escolha entre vários <a href=":linkPaymentMethods">métodos de pagamento</a>, incluindo o pagamento em dinheiro, totalmente anónimo. Com a nossa <a href=":linkApp">aplicação Android</a> ou a extensão do navegador, pode até provar que as suas pesquisas continuam anónimas, através de <a href=":linkToken">tokens anónimos</a>.',
                'compromise' => 'Dados pessoais',
            ],
            'efficiency' => [
                'heading' => 'Pesquisar de forma mais eficiente',
                'description' => 'Encontre mais depressa o que procura. Quando é útil, acrescentamos ligações diretas claras, notícias relevantes e vídeos aos resultados da pesquisa. A nossa pesquisa de imagens recorre também a fontes adicionais.',
            ],
        ],
        'howitworks' => [
            'heading' => 'Como funciona',
            'steps' => [
                [
                    'heading' => 'Obtenha a sua chave gratuita',
                    'description' => 'A sua chave MetaGer é gerada automaticamente. Sem registo, sem dados pessoais. É tudo o que precisa para utilizar o MetaGer.',
                ],
                [
                    'heading' => 'Ative o seu acesso',
                    'description' => 'Um <a href=":linkCost">pagamento</a> único adiciona saldo à sua chave, a que chamamos token. Isto ativa a pesquisa sem publicidade e sem rastreio e a navegação anónima, incluindo todas as funcionalidades atuais e futuras do MetaGer. Cerca de 500 token (5 €) costumam chegar para uns 2 meses.',
                    'membership' => 'Nota: os membros da nossa associação sem fins lucrativos <a href="https://suma-ev.de" target="_blank">SUMA-EV</a> podem utilizar o MetaGer sem custos adicionais. <a href=":linkMembership" target="_blank">Torne-se membro agora</a>',
                ],
                [
                    'heading' => 'Use o MetaGer em todo o lado',
                    'description' => 'Use a mesma chave em tantos dispositivos quantos quiser, ou partilhe-a com amigos e família. Basta abrir o MetaGer em qualquer dispositivo, introduzir a sua chave e já pode pesquisar — ou navegar anonimamente.',
                ],
            ],
            'start' => 'Começar',
            'login' => 'Já tenho uma chave',
        ],
    ],
];
