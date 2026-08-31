<?php
return [
    /**
     * The account, wherever it appears: the pill in the corner, the block at the
     * top of the site menu, and the one alert that interrupts.
     *
     * Its own file rather than more keys under index/sidebar, because the same
     * strings are now rendered from three different views on two different
     * layouts, and none of them is "the index page".
     */
    'pill' => [
        'charge' => ':charge Token',
        // Shown instead of the key code when the key cannot be named — a legacy
        // non-UUID key whose canonical form we could not resolve.
        'signed_in' => 'Sessão iniciada',
        // The webextension: the key never reaches us, so there is nothing to name.
        'anonymous' => 'sessão iniciada anonimamente',
        // The mark is decorative and the code is styled shorthand, so the whole
        // answer has to be in the label for anyone not reading the pixels.
        //
        // "ending in", not a bare "key :fingerprint": these are the last six
        // characters of the key (KeyUser::getKeyFingerprint()), and calling them
        // the key invited people to type them into the sign-in form — where,
        // until the guard in the keymanager's POST /key/enter, they resolved to
        // an empty phantom account.
        'aria' => 'A minha conta – chave terminada em :fingerprint, :charge Token',
        'aria_nocharge' => 'A minha conta – chave terminada em :fingerprint',
        'aria_nofingerprint' => 'A minha conta – :charge Token',
        'aria_anonymous' => 'A minha conta – sessão iniciada anonimamente através da extensão web',
    ],
    'sidebar' => [
        'balance' => ':charge Token · sem publicidade',
        // Not "0 Token · …": at zero the searches are not ad-free, they do
        // not happen at all.
        'balance_empty' => 'Sem Token',
        'manage' => 'Gerir',
        'topup' => 'Carregar',
        'logout' => 'Terminar sessão',
        'login' => 'Iniciar sessão',
        'create' => 'Configurar',
        'logged_out' => 'Sessão não iniciada. Com uma chave pesquisa sem publicidade e de forma anónima.',
        'anonymous_hint' => 'Sem publicidade · gerido pela extensão web',
        // The webextension holds the key, so the account lives in its popup.
        // The button this labels is hidden until the extension reveals it.
        'extension_settings' => 'Gerir na extensão',
    ],
    /**
     * The account page itself — /konto, moved here from /keys/key/<uuid>.
     *
     * Taken from the keymanager's pass/lang/<locale>/key.json, but mostly new.
     * The old page was almost nothing but button labels; what it never said is
     * what any of them are *for* — which is exactly what support gets asked.
     *
     * Not carried over: `key.share.*`. The share button handed the settings URL,
     * key included, to `navigator.share` and therefore to the operating system's
     * share sheet. Passing an account on is not something a button should
     * advertise; whoever wants to can copy the URL. The copy button stayed.
     */
    'page' => [
        'heading' => 'A minha conta',

        // Not "your key: 123456". These are the last six characters, and calling
        // them the key led people to type them into the sign-in form.
        'fingerprint' => 'Chave terminada em :fingerprint',
        'fingerprint_unknown' => 'Sessão iniciada',

        'balance' => [
            'unit' => 'tokens',
            'one_token' => 'Um token corresponde a uma pesquisa.',
            'valid_until' => 'Saldo válido até :date',
            'empty' => 'Sem saldo. Sem tokens não pode pesquisar: carregue para continuar.',
            'low' => 'O saldo está a acabar.',
            'unknown' => 'Neste momento não conseguimos consultar o seu saldo. A culpa é nossa e não sua — tente novamente dentro de alguns minutos.',
            'orders_summary' => 'De :count carregamentos, que expiram uns a seguir aos outros',
            'orders_heading' => 'Datas de expiração',
            'order' => ':amount tokens até :date',
        ],

        'actions' => [
            'topup' => 'Carregar saldo',
            'search' => 'Ir para a pesquisa',
        ],

        'charge' => [
            'heading' => 'Carregar saldo',
            'lede' => 'Um token é uma pesquisa e custa um cêntimo. Todos os preços incluem IVA.',
            'tokens' => ':amount tokens',
            'price' => ':price €',
            'more' => 'Todos os preços e formas de pagamento',

            /**
             * Why no package is on offer right now. Three sentences for three
             * states, all three of which the old page had too — except that it
             * said "your key is already fully charged", which is not true: what
             * is full is not the balance but the number of open top-ups.
             */
            'blocked' => [
                'proxy' => 'Está a navegar através de uma das nossas sessões de proxy. Enquanto o fizer, o carregamento está desativado para sua segurança: um pagamento leva a um prestador de serviços de pagamento, e este não deve ver esta sessão. Abra esta página sem sessão de proxy para carregar.',
                'full' => 'Esta chave já tem três carregamentos. Assim que o mais antigo estiver gasto ou tiver expirado, poderá carregar novamente.',
                'member' => 'É membro da SUMA-EV e pesquisa sem custos adicionais. Não precisa de nenhum pacote de tokens.',
            ],
        ],

        /**
         * The section the old page did not have: QR code, settings URL and the
         * transfer button sat there in one row, with not a sentence about what
         * they are for.
         */
        'save' => [
            'heading' => 'Proteja o seu acesso',
            'text' => 'Enquanto este navegador mantiver o cookie, a sua sessão permanece iniciada. Se o perder — um dispositivo novo, dados de navegação apagados —, a sua chave é o único caminho de volta ao seu saldo. Aqui está ela, e aqui estão três formas de a levar consigo.',

            /**
             * The key itself.
             *
             * It has to be here — the sign-in form asks for it first of all —
             * and it is collapsed, because this page gets photographed for
             * support tickets. The old page showed it large and always.
             */
            'key' => [
                'summary' => 'Mostrar e copiar a chave',
                'label' => 'A sua chave',
                'action' => 'Copiar a chave',
                'hint' => '36 caracteres. É com eles que inicia sessão em qualquer outro dispositivo. Recolhida porque esta página é muitas vezes fotografada: quem vir a sua chave pesquisa à sua custa.',
            ],

            'qr' => [
                'label' => 'Código QR',
                'alt' => 'Código QR que leva à sua chave',
                'action' => 'Guardar como imagem',
                'hint' => 'A imagem que o formulário de início de sessão pede. Pode carregá-la aí ou fotografá-la com a câmara.',
            ],

            'url' => [
                'label' => 'Marcador',
                'action' => 'Copiar o URL',
                'hint' => 'Abrir este URL repõe a chave juntamente com as definições de pesquisa deste navegador.',
            ],

            /**
             * The transfer dialog. The keymanager called it "generate login
             * code" — a label naming the means rather than the end, which is why
             * it never answered "how do I get MetaGer onto my phone?", even
             * though that is exactly what the button does.
             */
            'transfer' => [
                'label' => 'Outro dispositivo',
                'action' => 'Iniciar sessão num dispositivo',
                'hint' => 'Mostra um código curto que escreve no formulário de início de sessão do outro dispositivo, em vez de copiar a chave inteira.',

                'title' => 'Iniciar sessão noutro dispositivo',
                'description' => 'Introduza este código no outro dispositivo, no formulário de início de sessão, onde normalmente vai a chave.',
                'waiting' => 'A obter o código …',
                'note' => 'O código é válido para um único início de sessão e apenas enquanto estiver aqui visível. Feche esta janela assim que o tiver introduzido.',
                'failed' => 'Não foi possível obter o código. Feche a janela e tente novamente daqui a pouco.',
                'close' => 'Fechar',
            ],
        ],

        /**
         * What still lives in the keymanager. A list at the foot rather than the
         * three equal tabs of before: hardly anybody has campaigns, and a third
         * tab claimed otherwise.
         */
        'more' => [
            'heading' => 'Mais',
            'orders' => 'Encomendas e faturas',
            'campaigns' => 'Campanhas de vales',
            'help' => 'Ajuda sobre a chave',
            'logout' => 'Terminar sessão',
            // Signing out only clears the cookie. Someone who does not know
            // that will not click it — and someone who reads it as "delete
            // account" certainly will not.
            'logout_hint' => 'Remove a chave deste navegador. O saldo permanece na chave.',
        ],
    ],

    'empty' => [
        'message' => 'Os seus Token acabaram.',
        'action' => 'Carregar agora',
    ],
];
