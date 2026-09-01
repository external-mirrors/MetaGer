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
];
