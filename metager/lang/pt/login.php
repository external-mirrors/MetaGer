<?php

/**
 * Die Anmeldeseite — /anmelden. Siehe lang/de/login.php für die Herkunft der
 * Schlüssel und dafür, was gegenüber dem Keymanager neu ist.
 *
 * Portugiesisch gab es im Keymanager nicht; aus der deutschen Fassung neu
 * übersetzt. Wortwahl an lang/pt/price.php und lang/pt/account.php angelehnt —
 * „chave“ für den Schlüssel, „token“ für die Token.
 */
return [
    'heading' => 'Iniciar sessão no MetaGer',
    'lede' => 'A sua chave é a sua conta. Ela carrega o seu saldo de tokens e é tudo o que sabemos sobre si: nenhum nome, nenhum endereço de correio eletrónico, nenhuma palavra-passe.',

    'key' => [
        'label' => 'Chave ou código de acesso',
        'hint' => '36 caracteres. A partir de um dispositivo com sessão já iniciada também serve a palavra-passe de utilização única de seis dígitos da janela de transferência.',
        'placeholder' => 'xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx',
    ],

    'submit' => 'Iniciar sessão',
    'or' => 'ou',

    'file' => [
        'button' => 'Escolher ficheiro de cópia de segurança',
        'hint' => 'O ficheiro ou a imagem do código QR que guardou ao criar a sua chave.',
    ],

    'qr' => [
        'button' => 'Ler código QR',
        'hint' => 'Com a câmara deste dispositivo, por exemplo a partir do ecrã de outro.',
        'no_camera' => 'Nenhuma câmara disponível.',
        'invalid' => 'Esse código QR não contém nenhuma chave.',
        'close' => 'Fechar',
    ],

    'create' => [
        'prompt' => 'Ainda não tem chave?',
        'action' => 'Criar uma chave',
    ],

    'errors' => [
        'invalid_key' => 'Isso não é uma chave válida. Uma chave tem 36 caracteres e um código de acesso tem seis dígitos.',
        'invalid_login_code' => 'Esse código de acesso já não é válido. Dura alguns segundos e serve para um único início de sessão — peça um novo ao dispositivo com sessão iniciada. A abreviatura ao lado do seu saldo não é um código de acesso.',
        // Seis caracteres que não são uma chave. Quase sempre a abreviatura ao
        // lado do saldo — ver KeyIdenticon.
        'key_mark' => 'Esses seis caracteres são a abreviatura da sua chave — a que aparece ao lado do seu saldo. Identifica a sua conta, mas não a abre. Para iniciar sessão precisa da chave completa de 36 caracteres ou de um código de acesso de um dispositivo com sessão já iniciada.',
        'invalid_key_payment_id' => 'Isso é um número de pagamento, não uma chave. A sua chave tem 36 caracteres e não começa por Z.',
        'no_input' => 'Introduza uma chave ou escolha um ficheiro de cópia de segurança.',
        'file_unreadable' => 'Não foi possível ler nenhuma chave desse ficheiro. Deveria conter o código QR que guardou ao criar a sua chave.',
        // Der Keyserver hat nicht geantwortet, und zu viele Versuche von einer
        // Adresse. Beides sind Aussagen über uns und nicht über die Eingabe.
        'keyserver_unreachable' => 'Não foi possível verificar a chave neste momento. Isso não diz nada sobre a sua chave — tente novamente daqui a pouco.',
        'too_many_attempts' => 'Demasiadas tentativas a partir desta ligação. Aguarde alguns minutos e tente novamente.',
    ],

    'validation' => [
        'hex' => 'Uma chave só contém os caracteres 0–9, a–f e hífenes.',
        'uuid' => 'Isso não é uma chave válida.',
        'login' => 'Isso não é uma chave completa nem um código de acesso.',
    ],

    'empty_key' => [
        'message' => 'Esta chave não tem saldo. Se é isso que esperava, inicie sessão; caso contrário, talvez tenha havido um carácter mal escrito.',
        'entered' => 'Chave introduzida',
        'revalidate' => 'Verificar o que introduziu',
        'confirm' => 'Iniciar sessão mesmo assim',
    ],

    'extension' => [
        'heading' => 'A extensão MetaGer para o seu navegador',
        'text' => 'Mantenha a sessão iniciada mesmo depois de limpar os dados do navegador — e permaneça <a href=":tokenlink">comprovadamente anónimo</a> apesar de ter sessão iniciada.',
        'install' => 'Instalar para :browser',
        'install_generic' => 'Instalar a extensão',
    ],
];
