<?php
return [
    'lead' => 'Respondemos mais depressa por e-mail.',
    'email' => [
        'pgp' => [
            'summary' => 'Escrever cifrado (PGP)',
            'signed' => 'Todos os e-mails que enviamos são assinados com a nossa chave OpenPGP, que também segue no cabeçalho Autocrypt. Use esta chave para nos escrever de forma cifrada:',
            'replies' => [
                'heading' => 'Respostas cifradas',
                'how' => 'Respondemos cifrado automaticamente assim que tivermos a sua chave e confiarmos nela. Para isso, assine o seu e-mail. Obtemos a sua chave do e-mail (em anexo ou no cabeçalho Autocrypt) ou de keys.openpgp.org, e usamo-la por iniciativa própria se',
                'trusted_when' => [
                    'keys.openpgp.org a fornecer para o seu endereço (ou seja, o endereço está lá verificado), ou',
                    'o seu e-mail tiver uma assinatura DKIM válida do seu domínio de envio.',
                ],
                'otherwise' => 'Caso contrário, alguém da nossa equipa confirma-a primeiro manualmente. A partir daí, ciframos tudo o que lhe enviamos, não apenas as respostas.',
                'tofu' => 'Confiamos na primeira chave que recebermos assim para o seu endereço (Trust on First Use). Não adotamos automaticamente uma nova chave para o mesmo endereço: até a verificarmos, continuamos a cifrar com a anterior.',
                'never_plain' => 'Nunca respondemos sem cifra a um e-mail cifrado. Sem a sua chave, não podemos responder — por isso, assine sempre os e-mails cifrados que nos enviar.',
            ],
            'pubkey' => 'PGP Publickey: <a href="/download/pubkey.asc" download="0x2185CC8F3CA782EC.asc">0x2185CC8F3CA782EC</a> ou em <a href=":keyserver" target="_blank" rel="noopener">keys.openpgp.org</a>',
            'fingerprint' => 'Impressão digital PGP: 5FA5 2398 C382 B498 B14A B7F6 2185 CC8F 3CA7 82EC',
        ],
        'write' => 'Escrever um e-mail',
    ],
    'letter' => [
        '3' => "SUMA-EV\r
Postfach 51 01 43\r
D-30631 Hannover\r
Alemanha",
        '1' => 'Correio',
    ],
    'headline' => [
        '2' => 'Correio eletrónico',
        '1' => 'Contacto',
    ],
    'removal' => [
        'hint' => 'Trata-se de remover um resultado de pesquisa? Leia primeiro <a href=":link">o nosso guia</a>: o MetaGer mostra resultados de outros motores de pesquisa, por isso um resultado normalmente só pode ser removido lá.',
    ],
    'phone' => [
        'heading' => 'Telefone',
    ],
    'topics' => [
        'heading' => 'Perguntas frequentes',
        'key' => [
            'title' => 'Chave MetaGer e tokens',
            'text' => 'Como funciona a chave, carregamento e saldo',
        ],
        'orders' => [
            'title' => 'Encomendas e faturas',
            'text' => 'Encontrar uma encomenda, recibos e reembolsos',
        ],
        'donation' => [
            'title' => 'Doar',
            'text' => 'Apoie o MetaGer com um donativo',
        ],
        'membership' => [
            'title' => 'Tornar-se membro',
            'text' => 'Junte-se à SUMA-EV, a associação por trás do MetaGer',
        ],
        'help' => [
            'title' => 'Ajuda na pesquisa',
            'text' => 'Funções, definições e perguntas frequentes',
        ],
        'privacy' => [
            'title' => 'Privacidade',
            'text' => 'O que guardamos — e o que não guardamos',
        ],
    ],
];
