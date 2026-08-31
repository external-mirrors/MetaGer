<?php

/**
 * Aufladen (/konto/aufladen) — App\Http\Controllers\ChargeController.
 *
 * `cash`, `consent` und micropayment.prepay sind wortgleich aus dem Keymanager
 * übernommen (dessen checkout.json unter payments.cash/payments.prepay/
 * micropayment, dessen order.json unter agb und revocation, je Sprache) —
 * dieselbe Kasse, jetzt hier gerendert. `page`, `manual`,
 * micropayment.lastschrift/directbanking (dort nur Logos, keine Texte),
 * `returned` und vrpayment.label/submit/error.failed sind neu; vrpayment.privacy
 * ist wortgleich aus dem Keymanager übernommen wie cash/consent/micropayment.
 */
return [
    'page' => [
        'change' => 'Alterar quantidade',
        'methods' => [
            'heading' => 'Escolher o método de pagamento',
            'more' => 'Mais métodos de pagamento',
            'back' => 'Escolher outro método de pagamento',
        ],
        'cancel' => 'Voltar à conta',
    ],

    'cash' => [
        'label' => 'Dinheiro',
        'description' => 'Também pode carregar a sua chave em numerário. Para isso, basta enviar-nos por correio o seguinte número de encomenda juntamente com o montante pretendido. Tenha em atenção que o número de encomenda tem de ser legível para que possa ser processado por nós.',
        'note' => 'Tenha em atenção o seguinte:',
        'no_large_values' => 'Para sua própria segurança, não nos envie mais de 100 € por correio. Não assumimos qualquer responsabilidade pelo percurso de transporte. É da sua responsabilidade garantir que a carta nos chega.',
        'no_coins' => 'Aceitamos apenas notas. Não envie moedas!',
        'accepted_currencies' => 'Aceitamos apenas as seguintes moedas: EUR, USD, CAD, GBP.',
        'currency_translation' => 'Cobramos sempre os montantes em EUR. Se nos enviar outra moeda, o montante enviado será convertido à taxa de câmbio do dia',
        'no_refund' => 'Devido à legislação aplicável em matéria de branqueamento de capitais, infelizmente não é possível um reembolso ou devolução. No entanto, assim que o carregamento tiver sido registado por nós, pode introduzir o identificador de pagamento enviado em "Encomendas" para obter uma visão geral da encomenda e/ou solicitar uma fatura.',
        'generate' => 'Gerar identificador de pagamento',
        'error' => [
            'unreachable' => 'Ocorreu um problema ao criar a sua encomenda. Tente novamente mais tarde.',
        ],
        'order' => [
            'heading' => 'O seu identificador de pagamento',
            'copy' => 'Copiar identificador de pagamento',
            'address_heading' => 'Envie a carta para o seguinte endereço e anote o identificador de pagamento para os seus próprios registos',
            'address' => 'SUMA-EV
Postfach 51 01 43
30631 Hannover
Alemanha',
            'expiration' => 'O identificador de pagamento é válido até :date. Após esta data, deixa de poder ser utilizado para um carregamento.',
            'unique' => 'Utilize o identificador de pagamento apenas para um único carregamento. Recebe um novo sempre que visita esta página!',
        ],
    ],

    'consent' => [
        'agb' => 'Ao continuar a sua compra, aceita os nossos <a href=":agblink" target="_blank">Termos e Condições</a>.',
        'label' => 'Concordo expressamente com a execução do contrato antes do termo do prazo de revogação. Compreendo que o <a href=":revocation_link" target="_blank">direito de revogação</a> caduca com o início da execução do contrato. Em vez disso, concedemos-lhe um <a href=":refundlink" target="_blank">direito de devolução voluntário de 30 dias</a>.',
        'error' => 'Este campo é obrigatório',
    ],

    'manual' => [
        'label' => 'Manual (dev)',
        'description' => 'Ignore um pagamento real. Disponível apenas num ambiente de desenvolvimento.',
        'submit' => 'Concluir pagamento',
    ],

    'micropayment' => [
        'label' => 'Micropayment',
        'prepay' => [
            'label' => 'Transferência bancária',
            'email' => [
                'label' => 'Endereço de e-mail',
                'description' => 'Para este endereço enviaremos, uma única vez, informações sobre os nossos dados bancários e uma notificação quando o pagamento for concluído.',
            ],
        ],
        'lastschrift' => ['label' => 'Débito direto'],
        'directbanking' => ['label' => 'Transferência bancária instantânea'],
        'submit' => 'Efetuar pagamento',
        'privacy' => 'Ao clicar em "Efetuar pagamento", será redirecionado para o nosso prestador de serviços de pagamento <a href="https://micropayment.de" target="_blank">MicroPayment</a> para concluir a compra. Mais informações sobre <a href=":link" target="_blank">privacidade em :link_text</a>.',
    ],

    'vrpayment' => [
        'label' => 'VR Payment',
        'submit' => 'Efetuar pagamento',
        'privacy' => 'Ao clicar em "Efetuar pagamento", será redirecionado para o nosso prestador de serviços de pagamento <a href="https://www.vr-payment.de" target="_blank">VR Payment</a> para concluir a compra. Mais informações sobre a <a href=":link" target="_blank">privacidade na VR Payment</a>.',
        'error' => [
            'failed' => 'A VR Payment recusou este pagamento. Tente novamente ou escolha outro método de pagamento.',
        ],
    ],

    'paypal' => [
        'label' => 'PayPal',
        'heading' => 'Efetuar pagamento',
        'submit' => 'Efetuar pagamento',
        'loading' => 'O método de pagamento está a ser carregado',
        'cancel' => 'O processo de pagamento foi cancelado. Se o seu pagamento foi concluído antes do cancelamento, a sua encomenda será processada assim que o pagamento for confirmado pelo processador de pagamentos. Caso contrário, tente novamente.',
        'privacy' => 'Os métodos de pagamento deste grupo normalmente não exigem uma conta PayPal, mas são processados nela. Mais informações sobre a <a href=":link" target="_blank">privacidade no PayPal</a>.',
        'noscript' => 'Este método de pagamento requer JavaScript. Escolha outro método de pagamento ou ative o JavaScript.',
        'funding' => [
            'paypal' => 'PayPal',
            'card' => 'Cartão de crédito / débito',
            'p24' => 'Przelewy24',
            'bancontact' => 'Bancontact',
            'blik' => 'BLIK',
            'eps' => 'EPS',
            'mybank' => 'MyBank',
        ],
        'error' => [
            'not_available' => 'Lamentamos, mas o método de pagamento selecionado não está disponível na sua região.',
            'generic' => 'O processo de pagamento foi cancelado devido a um erro. Se o seu pagamento foi concluído antes do cancelamento, a sua encomenda será processada assim que o pagamento for confirmado pelo processador de pagamentos. Caso contrário, tente novamente.',
        ],
        'card' => [
            'label' => 'Cartão de crédito / débito',
            'name' => 'Nome do titular do cartão (opcional)',
            'number' => 'Número do cartão',
            'expiration' => 'Válido até',
            'cvv' => 'CVV',
            'error' => [
                '9500' => 'Cartão de crédito rejeitado por suspeita de fraude',
                '5100' => 'O cartão de crédito foi recusado pela instituição de crédito',
                '00N7' => 'CVV incorreto. Verifique os dados introduzidos',
                '5400' => 'Cartão de crédito expirado',
                '5180' => 'Falha na verificação de Luhn',
                '5120' => 'Cartão de crédito recusado por fundos insuficientes.',
                '9520' => 'Cartão de crédito rejeitado por perda/roubo',
                '0500' => 'Cartão de crédito recusado pela instituição de crédito',
                '1330' => 'Cartão de crédito inválido. Verifique os dados introduzidos',
                '3ds' => 'Falha na autenticação 3D',
                'generic' => 'Cartão de crédito recusado pela instituição de crédito',
            ],
        ],
    ],
    'returned' => [
        'heading' => 'Carregamento concluído',
        'paid' => 'Obrigado! A sua chave foi carregada com :amount tokens.',
        'pending' => 'O seu pagamento ainda está a ser processado. Assim que chegar até nós, a sua chave será carregada automaticamente.',
    ],
];
