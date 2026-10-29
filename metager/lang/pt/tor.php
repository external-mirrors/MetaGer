<?php
return [
    'description' => 'Abaixo encontra a ligação para o serviço oculto do MetaGer. Atenção: Esta ligação só pode ser acedida através da rede Tor. O teu browser produz uma mensagem de erro se não estiver ligado a ela.',
    'torbutton' => 'Abrir o serviço oculto do TOR',
    'torurl' => 'http://metagerv65pwclop2rsfzg4jwowpavpwd6grhhlvdgsswvo6ii4akgyd.onion/en/',
    // Shown only with the MetaGer web extension installed, which fills it in;
    // see resources/views/tor.blade.php.
    'extension' => [
        'disabled' => 'Usa a extensão web do MetaGer? Ela também pode funcionar no serviço oculto, assim que lhe permitir o acesso ao endereço onion.',
        'enable'   => 'Ativar a extensão para o serviço oculto',
        'enabled'  => 'A extensão web do MetaGer está ativada para o serviço oculto.',
        'manage'   => 'Gerir',
    ],
];
