<?php

/**
 * O keymanager nunca teve `pt` — este ficheiro é uma tradução nova a partir
 * de de/en, tal como as restantes páginas do fluxo da chave.
 */

return [
    'lookup' => [
        'heading' => 'Procurar uma encomenda',
        'description' => 'Introduza o ID de pagamento de uma das suas encomendas para ver os respetivos detalhes.',
        'placeholder' => 'ID de pagamento',
        'submit' => 'Mostrar encomenda',
        'error' => [
            'invalid' => 'Este não é um ID de pagamento válido.',
            'not_found' => 'Nenhuma encomenda na sua chave corresponde a esse ID de pagamento.',
        ],
    ],

    'show' => [
        'heading' => 'Encomenda :reference',
        'breadcrumb' => 'Encomendas',
        'thanks' => 'Obrigado pela sua compra!',
        'pending' => 'Os seus tokens serão creditados assim que o seu pagamento chegar até nós. Receberá então um e-mail de confirmação.',
        'lookup_hint' => 'Pode reabrir esta vista geral a qualquer momento introduzindo o seu ID de pagamento (:reference).',
        'order_line' => 'Encomenda :id de :date',
        'item' => 'Chave MetaGer: tokens',
        'count' => 'Quantidade',
        'price' => 'Preço',
        'vat' => 'IVA (:rate %)',
        'total' => 'Montante total',
        'exchange_rate' => 'Taxa de câmbio',
        'documents' => 'Comprovativo e fatura',
        'request_refund' => 'Pedir reembolso',
    ],


    'refund' => [
        'heading' => 'Reembolso',
        'breadcrumb' => 'Encomenda :reference',
        'unavailable' => 'Já não existe saldo reembolsável para esta encomenda — ou já foi pedido um reembolso, ou o método de pagamento utilizado não suporta um pedido de reembolso através deste formulário.',
        'success' => 'O seu pedido foi enviado com sucesso. Iremos processá-lo o mais rapidamente possível. Consoante o método de pagamento, pode demorar alguns dias até o reembolso ser visível nos seus movimentos.',
        'description' => 'Não está satisfeito com a sua chave? Lamentamos muito! Nesse caso, reembolsaremos naturalmente o valor da fatura. Um reembolso é sempre feito para a mesma conta utilizada no pagamento original. Também agradecemos a sua crítica.',
        'partial_note' => 'Parte do seu saldo adquirido já foi utilizada. Por isso, só podemos reembolsar-lhe <span class="bold">:count</span> de <span class="bold">:total</span> pesquisas.',
        'message' => [
            'label' => 'A sua mensagem (opcional)',
        ],
        'submit' => 'Pedir reembolso de :amount €',
        'error' => [
            'not_allowed' => 'Já não é possível um reembolso para esta encomenda.',
            'unreachable' => 'Erro ao enviar a sua mensagem. Tente novamente mais tarde.',
        ],
    ],
];
