<?php
return [
    'description' => [
        'message' => [
            'description' => 'A mensagem aqui introduzida ser-nos-á transmitida e utilizada para processar o seu pedido.',
            'title' => 'Mensagem',
        ],
        'title' => 'Descrição dos dados resultantes',
        'ip' => [
            'title' => 'Endereço de protocolo Internet',
            'description' => 'O endereço de protocolo Internet (doravante designado por IP) é obrigatório para a utilização de serviços Web como o MetaGer. Este IP, em combinação com uma data - semelhante a um número de telefone - identifica claramente um acesso à Internet e o seu proprietário. Em geral, os primeiros três (de um total de quatro) blocos de um IP não são pessoais. Se os blocos posteriores do IP forem encurtados, o endereço encurtado identifica a área geográfica aproximada em torno da ligação à Internet.',
            'example_full' => 'Exemplos (endereço IP completo)',
            'example_partial' => 'Exemplos (apenas os dois primeiros blocos)',
        ],
        'useragent' => [
            'title' => 'Identificador do agente do utilizador',
            'description' => 'Quando acede a um sítio Web, o seu browser envia automaticamente um identificador, normalmente com dados sobre o browser e o sistema operativo utilizados. Este identificador do browser (o chamado agente do utilizador) pode ser utilizado pelos sítios Web, por exemplo, para reconhecer dispositivos móveis e apresentar-lhes um resultado personalizado.',
            'example' => 'Exemplo',
        ],
        'payment' => [
            'title' => 'Detalhes do pagamento',
            'description' => 'Ao comprar uma chave MetaGer, são necessários diferentes dados de pagamento, dependendo do fornecedor de pagamento',
            'examples' => 'Exemplos',
            'name' => 'Max Mustermann, mail@example.com',
            'card' => 'Últimos dígitos do número do cartão de crédito',
        ],
        'query' => [
            'title' => 'Consulta de pesquisa introduzida',
            'description' => 'Os termos de pesquisa introduzidos são absolutamente necessários para uma pesquisa na Web. Regra geral, não é possível obter dados pessoais a partir deles; entre outras coisas, porque não têm uma estrutura fixa.',
            'examples' => 'Exemplos',
            'example_1' => 'consumo de água do duche',
            'example_2' => 'Letra da música Numa árvore um cuco',
        ],
        'preferences' => [
            'title' => 'Preferências do utilizador',
            'description' => 'Para além dos dados do formulário e dos agentes do utilizador, o browser transfere frequentemente outros dados. Estes incluem a seleção do idioma, definições de pesquisa, cabeçalhos de aceitação, cabeçalhos de não rastreio e outros.',
        ],
        'contact' => [
            'title' => 'Dados de contacto',
            'description' => 'A seguir está o nome (Vor- und Nachname) e o endereço de correio eletrónico do utilizador. Estes dados são utilizados por nós para responder às suas questões e para as enviar para outros endereços.',
        ],
    ],
    'data' => [
        'referrer' => 'o referenciador que enviou',
        'gps' => 'Dados de localização',
        'optional' => 'facultativo',
        'unused' => 'Não será guardado ou partilhado.',
        'ip' => 'Endereço IP',
        'useragent' => 'Agente do utilizador',
        'query' => 'Consulta de pesquisa',
        'preferences' => 'Preferências do utilizador',
        'contact' => 'Dados de contacto',
        'message' => 'Mensagem',
        'payment' => 'Dados de pagamento',
    ],
    'base' => [
        'title' => 'Base jurídica do tratamento',
        'description' => 'A base legal para o processamento dos seus dados pessoais identificáveis é o Art. 6 (1) (a) do RGPD se o utilizador consentir o processamento ao utilizar os nossos serviços, ou o Art. 6 (1) (f) do RGPD se o processamento for necessário para proteger os nossos interesses legítimos, ou outra base legal se o notificarmos separadamente.',
    ],
    'stats' => [
        'description' => 'Estamos sempre a trabalhar para melhorar os nossos serviços. Para o fazer, precisamos de saber quais as funções que estão a ser utilizadas. Por este motivo, recolhemos dados completamente anónimos sobre a frequência das visualizações de páginas e a utilização de funções individuais nos nossos sítios Web. Também recolhemos dados anónimos sobre a distribuição dos tipos e versões de browsers. Estas estatísticas não se baseiam em perfis de utilizadores individuais e são criadas sem cookies ou tecnologias semelhantes. Não contêm quaisquer dados pessoais.',
        'title' => 'Estatísticas anónimas',
    ],
    'contexts' => [
        'title' => 'Dados de entrada por contexto',
        'metager' => [
            'title' => 'Utilização do motor de busca MetaGer',
            'description' => 'Ao utilizar o nosso motor de busca MetaGer através do seu formulário web ou através da sua interface OpenSearch, são gerados os seguintes dados:',
            'query' => 'Como parte integrante da metapesquisa, a consulta de pesquisa é transmitida aos nossos parceiros para obter resultados de pesquisa a apresentar na página de resultados. Os resultados recebidos, incluindo o termo de pesquisa, são mantidos para apresentação durante algumas horas.',
            'preferences' => 'Utilizamos estes dados (por exemplo, definições de idioma) para responder à respectiva consulta de pesquisa. Armazenamos alguns destes dados numa base não pessoal para fins estatísticos.',
        ],
        'contact' => [
            'title' => 'Utilização do formulário de contacto',
            'description' => 'Ao utilizar o formulário de contacto MetaGer, são gerados os seguintes dados, que armazenamos para fins de referência até 2 meses após a conclusão do seu pedido:',
            'contact' => 'Será guardado para efeitos de referência até 2 meses após a conclusão do seu pedido.',
        ],
        'donate' => [
            'title' => 'Utilização do formulário de donativo',
            'description' => 'Os seguintes dados transmitidos no formulário de doação serão armazenados durante 2 meses para processamento:',
            'contact' => 'Utilizamos estes dados exclusivamente para eventuais consultas e não os transmitimos, em caso algum, a terceiros.',
            'payment' => 'Os dados de pagamento serão utilizados apenas para processar o donativo e não serão transmitidos a terceiros em circunstância alguma. Por razões fiscais, somos obrigados a manter e, por conseguinte, a guardar estes dados durante 10 anos. Em seguida, serão automaticamente apagados e não serão objeto de qualquer outro tratamento.',
            'message' => 'A mensagem que introduzir aqui ser-nos-á transmitida e tida em conta no processamento da sua doação.',
        ],
        'key' => [
            'title' => 'Verificar a chave MetaGer',
            'contact' => 'Utilizamos estes dados exclusivamente para eventuais consultas ou para faturação e não os transmitimos, em caso algum, a terceiros.',
            'payment' => 'Os dados de pagamento serão utilizados apenas para processar o donativo e não serão transmitidos a terceiros em circunstância alguma. Por razões fiscais, somos obrigados a manter e, por conseguinte, a guardar estes dados durante 10 anos. Em seguida, serão automaticamente apagados e não serão objeto de qualquer outro tratamento.',
        ],
        'suma' => [
            'title' => 'Utilização do sítio Web <a href="https://suma-ev.de">suma-ev.de</a>',
            'description' => 'Ao visitar os sítios Web do domínio "suma-ev.de", os seguintes dados são recolhidos e armazenados durante uma semana:',
            'function' => 'Ao visitar os sítios Web do domínio "suma-ev.de", os seguintes dados são recolhidos e armazenados durante uma semana:',
            'other' => 'Nos outros sítios Web dos nossos domínios, apenas processamos os dados recolhidos para responder a pedidos de informação e no âmbito dos outros pontos desta declaração de proteção de dados.',
            'startpage' => 'Na página inicial do nosso serviço MetaGer, utilizamos o agente do utilizador que transmitiu para lhe mostrar as instruções de instalação do plug-in adequado ao seu browser.',
        ],
        'newsletter' => [
            'title' => 'Registar para receber o boletim SUMA-EV',
            'description' => 'Para o manter informado sobre as nossas actividades, oferecemos um boletim informativo por correio eletrónico. Guardamos os seguintes dados até que cancele a sua subscrição:',
            'contact' => 'Utilizamos estes dados exclusivamente para lhe enviar a nossa newsletter e não os transmitimos, em caso algum, a terceiros.',
        ],
        'maps' => [
            'title' => 'Utilização de Maps.MetaGer.de',
            'description' => 'Ao utilizar o serviço de mapas MetaGer, são gerados os seguintes dados:',
            'ip' => 'Estamos a utilizar o seu endereço IP para estimar uma boa localização de partida para focar o mapa inicialmente. Para o efeito, o seu endereço IP é processado localmente. Os resultados não são armazenados em lado nenhum e serão imediatamente eliminados após o seu pedido.',
        ],
        'quote' => [
            'description' => 'O termo de pesquisa introduzido é utilizado para procurar resultados na base de dados de citações. Ao contrário das pesquisas na Web com o MetaGer, não é necessário transmitir o termo de pesquisa a terceiros porque a base de dados de citações está localizada no nosso servidor. Não serão guardados nem transmitidos outros dados.',
            'title' => 'Utilização da pesquisa de citações',
        ],
        'asso' => [
            'title' => 'Utilização do Associador',
            'description' => 'O associador utiliza o termo de pesquisa para determinar e apresentar os termos associados a ele. Os outros dados não serão guardados ou transmitidos.',
        ],
        'mapsapp' => [
            'title' => 'Utilização da aplicação MetaGer',
            'description' => 'Utilizar a aplicação MetaGer é o mesmo que utilizar o MetaGer através de um browser.',
        ],
        'plugin' => [
            'title' => 'Utilização do plugin MetaGer',
            'description' => 'Ao utilizar o plugin MetaGer, são gerados os seguintes dados:',
        ],
        'proxy' => [
            'title' => 'Utilização do proxy de anonimização',
            'description' => 'Ao utilizar o proxy de anonimização, são gerados os seguintes dados:',
        ],
    ],
    'hosting' => [
        'description' => 'Os nossos serviços são administrados por nós, a SUMA-EV, e operados em hardware alugado à Hetzner Online GmbH.',
        'title' => 'Alojamento',
    ],
    'rights' => [
        'title' => 'Os seus direitos enquanto utilizador (e as nossas obrigações)',
        'description' => 'Para que também possa proteger os seus dados pessoais, esclarecemos (de acordo com o Art. 13 DSGVO) que tem os seguintes direitos:',
        'information' => [
            'title' => 'Direito de fornecer informações',
            'description' => 'O utilizador tem o direito (art. 15.º do RGPD) de nos solicitar, a qualquer momento, informações sobre se e, em caso afirmativo, quais os dados que nós (metager.de e SUMA-EV) temos sobre si. Enviar-lhe-emos o mais rapidamente possível, ou seja, no prazo de alguns dias, uma cópia completa dos dados que armazenámos ou que de outra forma armazenámos sobre si, de acordo com o artigo 15º, parágrafo 3, subsecção 1 do RGPD. Para o efeito, damos preferência ao método eletrónico, em conformidade com o artigo 15.º, n.º 3, ponto 3 do RGPD; para o efeito, guardamos o seu endereço de correio eletrónico durante o período de tratamento. Por favor, informe-nos se desejar especificamente a informação em papel.',
        ],
        'correction' => [
            'title' => 'Direito de correção e de complemento',
            'description' => 'De acordo com o artigo 16º do RGPD. Se tivermos armazenado dados incorrectos sobre si, pode solicitar a sua correção. Isto também se aplica a componentes em falta, onde tem o direito de os complementar.',
        ],
        'deletion' => [
            'title' => 'Direito ao apagamento',
            'description' => 'De acordo com o artigo 17º do RGPD',
        ],
        'processing' => [
            'title' => 'Direito à restrição do tratamento',
            'description' => 'Nos termos do artigo 18.º do RGPD; Por exemplo, se nos tiver pedido para apagar ou alterar os dados que lhe dizem respeito, pode impor-nos uma proibição de processamento durante o tempo necessário para o fazermos. Isto é possível independentemente do facto de acabarmos por alterar, apagar, etc. os dados em questão.',
        ],
        'complaint' => [
            'title' => 'Direito de queixa',
            'description' => 'De acordo com o artigo 13.º, n.º 2, alínea d) do RGPD, pode apresentar uma queixa contra nós ao responsável pela proteção de dados do Estado da Baixa Saxónia. Em linha: <a href="https://www.lfd.niedersachsen.de/startseite/">Responsável pela proteção de dados</a>',
        ],
        'opposition' => [
            'title' => 'Direito de se opor ao tratamento',
            'description' => 'De acordo com o artigo 21.º do RGPD, por exemplo, se constar de uma lista e quiser continuar a constar, pode proibir o tratamento ou o tratamento posterior desses dados.',
        ],
        'portability' => [
            'title' => 'Direito à portabilidade dos dados',
            'description' => 'De acordo com o artigo 20.º do RGPD, isto significa que somos obrigados a fornecer-lhe os dados solicitados de forma legível, eventualmente legível por máquina ou habitual, de modo a que possa tornar os dados acessíveis a outra pessoa tal como estão (para transferir).',
        ],
        'obligation_notify' => [
            'title' => 'Obrigação de notificação em caso de correção ou supressão de dados pessoais ou de limitação do tratamento:',
            'description' => 'De acordo com o artigo 19.º do RGPD, se tivéssemos tornado acessíveis a terceiros os dados que nos confiou (o que nunca fazemos), seríamos obrigados a informá-los de que, a seu pedido, iríamos apagar, alterar, etc.',
        ],
        'perception' => 'Para exercer estes direitos, basta contactar-nos através do nosso <a href=":contact_link">formulário de contacto</a></b>. Se preferir a forma de carta, envie-nos correio para o nosso endereço de escritório:',
    ],
    'changes' => [
        'title' => 'Alterações a esta declaração',
        'description' => 'Tal como as nossas ofertas, também esta declaração de proteção de dados está sujeita a alterações constantes. Por conseguinte, deve voltar a lê-la regularmente.',
        'date' => 'Esta versão da nossa política de privacidade é datada de: :date',
    ],
    'introduction' => 'Para uma maior transparência, enumeramos os dados que recolhemos do utilizador e a forma como os utilizamos. A proteção dos seus dados é importante para nós e deve sê-lo também para si. <strong>Leia atentamente esta declaração; é do seu interesse.</strong>',
    'responsible_party' => [
        'title' => 'Pessoas responsáveis e pessoas de contacto',
        'description' => 'O MetaGer e os serviços relacionados são operados por <a href="https://suma-ev.de">SUMA-EV</a>, que é também o autor desta declaração. Nesta declaração, "nós" significa geralmente a SUMA-EV. Pode encontrar os nossos dados de contacto em <a href=":link_impress">Imprint</a>. Podemos ser contactados por e-mail através do nosso formulário de contacto <a href=":link_contact"></a> .',
    ],
    'principles' => [
        'title' => 'Princípios',
        'description' => 'Enquanto associação sem fins lucrativos, estamos empenhados no livre acesso ao conhecimento. Como sabemos que a investigação livre não é compatível com a vigilância em massa, também levamos muito a sério a proteção de dados. Sempre processámos apenas os dados absolutamente necessários para o funcionamento dos nossos serviços. A proteção de dados é sempre a nossa norma. Não utilizamos a definição de perfis, ou seja, a criação automática de perfis de utilizador.',
    ],
];
