<?php
return [
    'email' => [
        'pgp' => [
            'description' => 'Os nossos e-mails são assinados criptograficamente. Se pretender verificar a assinatura ou enviar o seu correio encriptado, utilize a seguinte chave pública. Se pretender receber uma resposta encriptada, anexe a sua chave pública ao seu correio encriptado e assinado.',
            'pubkey' => 'PGP Publickey: <a href="/download/pubkey.asc" download="0x2185CC8F3CA782EC.asc">0x2185CC8F3CA782EC</a> ou em <a href=":keyserver" target="_blank" rel="noopener">keys.openpgp.org</a>',
            'fingerprint' => 'Impressão digital PGP: 5FA5 2398 C382 B498 B14A B7F6 2185 CC8F 3CA7 82EC',
        ],
        'text' => 'Pode contactar-nos enviando um e-mail para: <a href="mailto::mail">:mail</a>',
    ],
    'letter' => [
        '2' => 'Preferimos o contacto digital. No entanto, se considerar necessário contactar-nos por via postal, pode enviar-nos para',
        '3' => "SUMA-EV\r
Postfach 51 01 43\r
D-30631 Hannover\r
Alemanha",
        '1' => 'Por correio postal',
    ],
    'headline' => [
        '2' => 'Correio eletrónico',
        'pgp' => 'Encriptação',
        '1' => 'Contacto',
    ],
    'intro' => 'Antes de nos escrever: muitas perguntas já têm resposta aqui — normalmente mais rápida do que por e-mail.',
    'removal' => [
        'heading' => 'Pretende que um resultado de pesquisa seja removido?',
        'text' => 'Leia primeiro o nosso guia. O MetaGer mostra os resultados de outros motores de pesquisa, pelo que um resultado só pode ser removido no sítio web e nesses motores de pesquisa. O guia explica onde e como.',
        'link' => 'Ler o guia',
    ],
    'topics' => [
        'heading' => 'Outros pedidos frequentes',
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
    'reach' => 'Como nos contactar',
];
