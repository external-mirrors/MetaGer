<?php

/**
 * Die Seite zum Erstellen eines Schlüssels — /schluessel-erstellen. Siehe
 * lang/de/key-create.php für die Herkunft der Schlüssel und dafür, was
 * gegenüber dem Keymanager neu ist.
 */
return [
    'heading' => 'Criar uma chave',
    'lede' => 'A sua chave é a sua conta. Ela carrega o seu saldo de tokens e é tudo o que sabemos sobre si: nenhum nome, nenhum endereço de correio eletrónico, nenhuma palavra-passe. Isso significa também que, se a perder, perde o saldo que está nela.',

    'existing' => [
        'text' => 'Já teve uma chave MetaGer? Inicie sessão com ela em vez de criar uma nova: uma chave nova recebe o seu próprio saldo separado, e o antigo fica na chave antiga.',
        'action' => 'Iniciar sessão com uma chave existente',
    ],

    'offer' => [
        'text' => 'Uma pressão no botão e já tem uma. Nenhum formulário, nenhumas credenciais: o MetaGer sorteia uma sequência de caracteres que ainda não pertence a ninguém.',
        'button' => 'Criar a chave agora',
    ],

    'working' => 'Um momento: estamos a sortear uma chave nova para si …',

    /**
     * The mark that sits in the corner of every page from here on.
     *
     * Derived from the key and stored nowhere
     * ({@see \App\Authentication\KeyIdenticon}). It is here because a mark you
     * are meant to recognise has to be shown the first time — otherwise it is
     * just a coloured square the second time.
     */
    'identity' => 'É assim que reconhecerá a sua conta: a partir de agora esta marca aparece no canto superior direito de cada página.',

    'key' => [
        'label' => 'A sua chave nova',
        'hint' => '36 caracteres. É com eles que inicia sessão em qualquer outro dispositivo.',
    ],

    'copy' => [
        'action' => 'Copiar a chave',
        'done' => 'Copiada',
    ],

    'save' => [
        'heading' => 'Guarde-a em algum lado',
        'text' => 'Enquanto este navegador guardar o cookie, mantém-se com sessão iniciada. Se o perder — um dispositivo novo, dados de navegação apagados —, esta chave é o único caminho de volta.',

        'qr' => [
            'alt' => 'Código QR que leva à sua chave',
            'action' => 'Guardar como imagem',
            'hint' => 'A imagem que o formulário de início de sessão pede. Mais tarde pode carregá-la lá ou fotografá-la com a câmara.',
        ],

        'url' => [
            'label' => 'Marcador',
            'action' => 'Copiar o URL',
            'hint' => 'Abrir este URL volta a configurar a chave juntamente com as definições deste navegador.',
        ],

        'no_cookies' => 'Este navegador não guarda cookies para o MetaGer. Sem cookie não se mantém com sessão iniciada: então o URL acima é a forma de a iniciar antes de uma pesquisa. Também o pode adicionar como motor de pesquisa no seu navegador.',
    ],

    'continue' => 'Continuar: carregar saldo',
    'continue_hint' => 'Uma chave nova ainda não tem saldo. No passo seguinte escolhe um pacote de tokens.',

    'errors' => [
        'keyserver_unreachable' => 'Não foi possível criar uma chave neste momento. A culpa é nossa e não sua — tente novamente daqui a pouco.',
        'too_many_attempts' => 'A partir desta ligação acabaram de ser criadas muitíssimas chaves. Espere alguns minutos e volte a carregar a página.',
        'no_key' => 'A chave perdeu-se pelo caminho — isso acontece quando a página esteve aberta muito tempo. Aqui está uma nova.',
    ],
];
