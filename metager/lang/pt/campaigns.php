<?php

return [
    'heading' => 'Campanhas de vales',
    'description' => 'Ofereça chaves do seu próprio saldo de tokens, por exemplo a amigos ou colegas. As chaves oferecidas só deduzem os tokens da sua chave quando são efetivamente utilizadas – presentes não utilizados não lhe custam nada.',
    'unreachable' => 'Não foi possível carregar as suas campanhas de vales neste momento. Tente novamente mais tarde.',
    'copy_link' => 'Copiar link',
    'public_link' => 'Link público',
    'delete_note' => 'As campanhas expiradas e desativadas são eliminadas automaticamente.',
    'print_cards' => 'Imprimir cartões (PDF)',
    'disable' => 'Desativar',
    'delete' => 'Eliminar agora',

    'status' => [
        'active' => 'ativa',
        'disabled' => 'desativada',
        'expired' => 'expirada',
    ],

    'facts' => [
        'tokens_per_key' => ':tokens tokens por chave',
        'redeemed' => ':redeemed de :total resgatados',
        'budget' => 'restam :left de :total tokens',
        'expires' => 'termina em :date',
    ],

    'create' => [
        'heading' => 'Criar campanha',
        'info' => 'A campanha é garantida por esta chave: os tokens oferecidos são deduzidos do seu saldo quando são utilizados. As campanhas duram 3 meses, as chaves oferecidas são válidas durante 1 mês após o resgate.',
        'name' => 'Nome (visível apenas para si)',
        'tokens_per_key' => 'Tokens por chave oferecida',
        'total_volume' => 'Número máximo total de tokens',
        'total_volume_hint' => 'A sua chave contém atualmente :charge tokens. Nunca pode oferecer mais do que o seu saldo.',
        'voucher_count' => 'Número de vales (opcional)',
        'voucher_count_hint' => 'Predefinição: número máximo total dividido pelos tokens por chave.',
        'submit' => 'Criar campanha',
        'error' => [
            'tokens_per_key_too_high' => 'Os tokens por chave não podem exceder o número máximo total.',
            'voucher_count_out_of_range' => 'O número de vales não corresponde aos tokens por chave e ao número máximo total.',
            'over_budget' => 'O número máximo total excede o seu saldo disponível.',
            'too_many_active' => 'Já atingiu o número máximo de campanhas ativas.',
            'invalid' => 'Não foi possível criar a campanha. Verifique os seus dados.',
            'unreachable' => 'Não foi possível criar a campanha neste momento. Tente novamente mais tarde.',
        ],
    ],

    /**
     * /c — App\Http\Controllers\VoucherController.
     */
    'redeem' => [
        'enter' => [
            'heading' => 'Resgatar o seu vale',
            'description' => 'Recebeu um código de vale para pesquisas gratuitas no MetaGer? Introduza-o aqui para obter a sua chave MetaGer pessoal.',
            'label' => 'O seu código de vale',
            'submit' => 'Resgatar código',
            'invalid_code' => 'Este código não é válido. Verifique os dados introduzidos.',
            'rate_limited' => 'Demasiadas tentativas. Tente novamente mais tarde.',
        ],
        'teaser' => [
            'heading' => 'O seu presente MetaGer',
            'tokens' => 'Tokens',
            'description' => 'Este código dá-lhe a sua própria chave MetaGer carregada com :tokens tokens - pesquise na web sem publicidade e sem ser rastreado.',
            'validity' => 'A chave é válida durante :days dias após o resgate.',
            'submit' => 'Obter a minha chave',
        ],
        'redeemed' => [
            'heading' => 'Aqui está a sua chave MetaGer!',
            'description' => 'A sua nova chave está carregada com :tokens tokens.',
            'save' => [
                'heading' => '1. Guarde a sua chave',
                'description' => 'A sua chave é o seu início de sessão - só é mostrada aqui e não pode ser recuperada. Guarde-a no seu gestor de palavras-passe, transfira o código QR ou imprima esta página.',
            ],
            'copy_key' => 'Copiar chave',
            'validity' => 'A chave é válida até :date.',
            'use' => [
                'heading' => '2. Comece a pesquisar',
                'description' => 'Abra este link para ativar a chave no seu navegador. Guarde-o nos marcadores para continuar com sessão iniciada.',
            ],
            'copy_url' => 'Copiar link',
            'start_searching' => 'Começar a pesquisar agora',
            'to_account' => 'Ir para a minha conta',
            'qr_alt' => 'Código QR da chave',
            'no_cookies' => 'Este navegador não parece guardar cookies. Guarde antes a chave ou o código QR acima.',
        ],
        'error' => [
            'heading' => 'Isto não funcionou',
            'invalid_code' => 'Este código não existe. Verifique os dados introduzidos.',
            'invalid_token' => 'Este link é inválido ou expirou.',
            'already_redeemed' => 'Este código já foi resgatado.',
            'campaign_inactive' => 'Esta campanha terminou. O código já não pode ser resgatado.',
            'budget_exhausted' => 'Todos os presentes desta campanha já foram distribuídos.',
            'rate_limited' => 'Demasiadas tentativas. Tente novamente mais tarde.',
            'unreachable' => 'Não foi possível resgatar o vale neste momento. Tente novamente mais tarde.',
            'unknown' => 'Ocorreu um erro inesperado. Tente novamente mais tarde.',
            'retry' => 'Introduzir um código',
        ],
    ],
];
