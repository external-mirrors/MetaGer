<?php
return [
    'urls' => [
        'title' => 'Excluir URLs',
        'example_b' => '<i>a minha pesquisa</i> -url:dog',
        'explanation' => 'Pode excluir resultados de pesquisa que contenham palavras específicas nas suas ligações de resultados utilizando "-url:" na sua pesquisa.',
        'example_a' => 'Exemplo: Pretende excluir os resultados em que a palavra "cão" aparece na hiperligação do resultado:',
    ],
    'bang' => [
        '3' => 'Os "redireccionamentos" do !bang fazem parte das nossas dicas rápidas e requerem um "clique" adicional. Esta foi uma decisão difícil para nós, uma vez que torna os !bangs menos úteis. No entanto, infelizmente, é necessário porque as ligações para as quais ocorre o redireccionamento não são originárias de nós, mas de um terceiro, o DuckDuckGo.<p>Garantimos sempre que os nossos utilizadores mantêm o controlo em todos os momentos. Por isso, protegemos de duas formas: Primeiro, o termo de pesquisa introduzido nunca é transmitido ao DuckDuckGo, apenas o !bang. Em segundo lugar, o utilizador confirma explicitamente a visita ao alvo do !bang. Infelizmente, devido a razões de pessoal, não podemos atualmente verificar ou manter todos estes !bangs nós próprios.',
        '1' => "O MetaGer suporta, até certo ponto, um estilo de escrita frequentemente referido como sintaxe '!bang'.<br>Um '!bang' começa sempre com um ponto de exclamação e não contém espaços. Exemplos incluem '!twitter' ou '!facebook'.<br>Quando um !bang suportado é utilizado na consulta de pesquisa, aparece uma entrada nas nossas dicas rápidas, permitindo-lhe continuar a pesquisa com o respetivo serviço (Twitter ou Facebook) com o premir de um botão.",
        'title' => '!bangs <a title="For easy help, click here" href="/hilfe/easy-language/functions#eh-bangs"><img class="easy-help-icon lm-only" src="/img/help-questionmark-icon-lm.svg"/><img class="easy-help-icon dm-only" src="/img/help-questionmark-icon-dm.svg"/></a>',
        '2' => 'Porque é que os !bangs não são abertos diretamente?',
    ],
    'backarrow' => 'Voltar',
    'easy-help' => 'Ao clicar no símbolo <a title="For easy help, click here" href="/hilfe/easy-language/functions"><img class="easy-help-icon lm-only" src="/img/help-questionmark-icon-lm.svg"/><img class="easy-help-icon dm-only" src="/img/help-questionmark-icon-dm.svg"/></a> , acederá a uma versão simplificada da ajuda.',
    'multiwordsearch' => [
        '4' => [
            'example' => '"a mesa redonda"',
            'text' => "Com uma pesquisa por frase, pode procurar combinações de palavras em vez de palavras individuais. Basta colocar as palavras que devem aparecer juntas entre aspas.",
        ],
        '3' => [
            'example' => '"a" "redonda" "mesa"',
            'text' => "Se quiser garantir que as palavras da sua pesquisa também aparecem nos resultados, tem de as colocar entre aspas.",
        ],
        '1' => "Ao procurar mais do que uma palavra no MetaGer, tentamos automaticamente fornecer resultados em que todas as palavras aparecem ou estão o mais próximo possível.",
        'title' => 'Pesquisa de várias palavras <a title="For easy help, click here" href="/hilfe/easy-language/functions#eh-severalwords"><img class="easy-help-icon lm-only" src="/img/help-questionmark-icon-lm.svg"/><img class="easy-help-icon dm-only" src="/img/help-questionmark-icon-dm.svg"/></a>',
        '2' => "Se isto não for suficiente para si, tem duas opções para tornar a sua pesquisa mais precisa:",
    ],
    'key' => [
        '4' => 'Guardar ficheiro <br>Quando se encontra na <a href = "/keys/key/enter">página de gestão</a> da chave MetaGer, existe uma opção para guardar um ficheiro. Isto guarda a sua chave MetaGer como um ficheiro. Pode então utilizar este ficheiro noutro dispositivo para iniciar sessão com a sua chave.',
        'colors' => [
            'red' => 'Vermelho: Se o símbolo da chave for vermelho, significa que esta chave está vazia. Utilizou todas as pesquisas sem anúncios. Pode recarregar a chave na página de gestão de chaves.',
            'yellow' => 'Amarela: Se vires uma tecla amarela, significa que ainda tens um saldo de 30 fichas. As tuas pesquisas estão a esgotar-se. Recomenda-se que recarregue a chave em breve.',
            'grey' => 'Cinzento: Não definiu uma chave. Está a utilizar a pesquisa livre.',
            '1' => 'Para reconhecer facilmente se está a pesquisar sem anúncios, atribuímos cores aos nossos símbolos principais. Abaixo estão as explicações para as cores correspondentes:',
            'title' => 'Chave MetaGer colorida',
            'green' => 'Verde: Se o símbolo da tecla estiver verde, significa que está a utilizar uma tecla carregada.',
        ],
        'title' => 'Adicionar chave MetaGer <a title="For easy help, click here" href="/hilfe/easy-language/functions#eh-keyexplain"><img class="easy-help-icon lm-only" src="/img/help-questionmark-icon-lm.svg"/><img class="easy-help-icon dm-only" src="/img/help-questionmark-icon-dm.svg"/></a>',
        '5' => 'Digitalizar o código QR <br>Alternativamente, também pode digitalizar o código QR apresentado na <a href = "/keys/key/enter">página de gestão</a> para iniciar sessão com outro dispositivo.',
        '2' => 'Código de login <br>Na <a href = "/keys/key/enter">página de gestão</a> da chave MetaGer, pode utilizar o código de login para adicionar a sua chave a outro dispositivo. Basta introduzir o código numérico de seis dígitos ao iniciar a sessão. O código de acesso só pode ser utilizado uma vez e só é válido enquanto a janela estiver aberta.',
        '6' => 'Introduzir manualmente a chave MetaGer <br>Também pode introduzir manualmente a chave noutro dispositivo.',
        '3' => 'Copiar URL <br>Quando se encontra na <a href = "/keys/key/enter">página de gestão</a> da chave MetaGer, existe uma opção para copiar um URL. Este URL pode ser utilizado para guardar todas as definições do MetaGer, incluindo a chave MetaGer, noutro dispositivo.',
        '1' => 'A chave MetaGer é automaticamente configurada no seu browser e utilizada. Não precisa de fazer mais nada. Se pretender utilizar a chave MetaGer noutros dispositivos, existem várias formas de configurar a chave MetaGer:',
    ],
    'exactsearch' => [
        'example' => [
            '1' => "+palavra de exemplo",
            '2' => '+"frase de exemplo"',
        ],
        '3' => 'Exemplo: ',
        'title' => 'Pesquisa exacta <a title="For easy help, click here" href="/hilfe/easy-language/functions#exactsearch"><img class="easy-help-icon lm-only" src="/img/help-questionmark-icon-lm.svg"/><img class="easy-help-icon dm-only" src="/img/help-questionmark-icon-dm.svg"/></a>',
        '1' => "Se pretender encontrar uma palavra específica nos resultados de pesquisa do MetaGer, pode prefixar essa palavra com um sinal de mais. Ao utilizar um sinal de mais e aspas, uma frase é pesquisada exatamente como a introduziu.",
        '2' => "Exemplo: S",
    ],
    'stopwords' => [
        'title' => 'Palavras de paragem <a title="For easy help, click here" href="/hilfe/easy-language/functions#eh-stopwordsearch"><img class="easy-help-icon lm-only" src="/img/help-questionmark-icon-lm.svg"/><img class="easy-help-icon dm-only" src="/img/help-questionmark-icon-dm.svg"/></a>',
        '3' => "carro novo -bmw",
        '2' => "Exemplo: Está à procura de um carro novo, mas não de um BMW. A sua opinião seria:",
        '1' => "Se quiser excluir resultados de pesquisa no MetaGer que contenham palavras específicas (palavras de exclusão / stopwords), pode fazê-lo prefixando essas palavras com um sinal de menos.",
    ],
    'selist' => [
        'explanation_a' => 'Tente primeiro instalar o plugin atual. Para instalar, basta clicar na ligação diretamente abaixo da caixa de pesquisa. O seu browser já deve ter sido detectado aí.',
        'title' => 'Adicionar MetaGer à lista de motores de busca do seu navegador <a title="For easy help, click here" href="/hilfe/easy-language/functions#eh-selist"><img class="easy-help-icon lm-only" src="/img/help-questionmark-icon-lm.svg"/><img class="easy-help-icon dm-only" src="/img/help-questionmark-icon-dm.svg"/></a>',
        'explanation_b' => 'Alguns navegadores exigem que introduza um URL; deve ser "https://metager.de/meta/meta.ger3?input=%s" sem aspas. Pode gerar o URL você mesmo pesquisando algo no metager.de e substituindo o que está por trás de "input=" na barra de endereço por %s. Se continuar a ter problemas, contacte-nos: <a href="/kontalt" target="_blank" rel="noopener">Formulário de contacto</a>',
    ],
    'title' => 'MetaGer - Ajuda',
    'searchfunction' => [
        'title' => "Funções de pesquisa",
    ],
];
