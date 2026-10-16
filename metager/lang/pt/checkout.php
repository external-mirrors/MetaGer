<?php

/**
 * Aufladen (/konto/aufladen) — App\Http\Controllers\ChargeController.
 *
 * Bezahlt wird bei suma-payments: hier stehen nur noch die Zustimmung
 * (`suma`, `consent`), die Schrittleiste und Kurzfassung (`page`) und die
 * Rückkehrseite (`returned`). Die Texte der eigenen Seiten für Bar, Wero,
 * micropayment und PayPal sind mit ihnen gegangen (suma-crm
 * docs/keymanager-migration-plan.md, Schritt 7).
 */
return [
    'page' => [
        'change' => 'Alterar quantidade',
        'cancel' => 'Voltar à conta',
        'steps' => [
            'aria_two' => 'Carregamento em dois passos',
            'amount' => 'Quantidade',
            'pay' => 'Pagar',
        ],
        'summary' => [
            'buying' => 'Está a comprar',
            'after' => 'Saldo depois',
            'price' => 'Preço',
        ],
    ],
    'consent' => [
        'agb' => 'Ao continuar a sua compra, aceita os nossos <a href=":agblink" target="_blank">Termos e Condições</a>.',
        'label' => 'Concordo expressamente com a execução do contrato antes do termo do prazo de revogação. Compreendo que o <a href=":revocation_link" target="_blank">direito de revogação</a> caduca com o início da execução do contrato. Em vez disso, concedemos-lhe um <a href=":refundlink" target="_blank">direito de devolução voluntário de 30 dias</a>.',
        'error' => 'Este campo é obrigatório',
    ],
    'suma' => [
        'label' => 'Transferência, débito direto e mais',
        'heading' => 'Pagar',
        'lede' => 'Na página seguinte escolhe como pagar: :methods. Pode pagar de forma anónima – nome e e-mail são voluntários, exceto para o débito direto.',
        'submit' => 'Continuar para o pagamento',
        'privacy' => 'O pagamento passa pela área de pagamentos da SUMA-EV (payments.suma-ev.de). A transferência bancária, o débito direto SEPA e o dinheiro por correio são tratados por nós – um prestador de pagamentos externo só intervém com cartão e Wero (VR Payment) e com PayPal.',
        'methods' => [
            'banktransfer' => 'Transferência bancária',
            'sepa_directdebit' => 'Débito direto SEPA',
            'card' => 'Cartão de crédito/débito',
            'wero' => 'Wero',
            'paypal' => 'PayPal',
            'cash' => 'Dinheiro por correio',
        ],
        'error' => [
            'unreachable' => 'Ocorreu um problema ao criar a sua encomenda. Tente novamente mais tarde.',
        ],
    ],
    'returned' => [
        'heading' => 'Carregamento concluído',
        'pending_heading' => 'O pagamento está a ser processado',
        'paid' => 'Obrigado! A sua chave foi carregada com :amount tokens.',
        'next' => 'O seu saldo está disponível de imediato — pode continuar a pesquisar agora.',
        'details' => 'Ver detalhes da encomenda',
        'pending' => 'O seu pagamento ainda está a ser processado. Assim que chegar até nós, a sua chave será carregada automaticamente.',
    ],
];
