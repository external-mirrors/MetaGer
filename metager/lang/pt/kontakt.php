<?php
return [
    'error' => [
        '1' => 'Lamentamos, mas infelizmente não recebemos quaisquer dados com o seu pedido de contacto. A mensagem não foi enviada.',
        '2' => 'Ocorreu um erro na entrega da sua mensagem. Pode contactar-nos diretamente através de :email',
    ],
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
Röselerstr. 3\r
30159 Hannover\r
Alemanha",
        '1' => 'Por correio postal',
    ],
    'form' => [
        '8' => 'Enviar',
        '1' => 'Formulário de contacto anónimo',
        '7' => 'Assunto',
        '2' => 'Pode enviar-nos uma mensagem anónima utilizando este formulário. Se optar por não incluir o seu endereço de correio eletrónico, não receberá qualquer resposta.',
        '6' => 'A sua mensagem',
        '9' => 'Até 5 anexos (tamanho do ficheiro < 5 MB)',
        '5' => 'O seu endereço eletrónico (facultativo)',
    ],
    'headline' => [
        '2' => 'Correio eletrónico',
        'pgp' => 'Encriptação',
    ],
    'success' => [
        '1' => 'A sua mensagem foi entregue com sucesso. Foi enviada uma primeira resposta automática para :email.',
    ],
];
