<?php

return [
    'heading' => 'Campanyes de val',
    'description' => 'Repartiu claus del vostre propi saldo de fitxes, per exemple entre amics o companys de feina. Les claus repartides només descompten les seves fitxes de la vostra clau quan es fan servir realment: els regals no utilitzats no us costen res.',
    'unreachable' => 'Ara mateix no s\'han pogut carregar les vostres campanyes de val. Torneu-ho a provar més tard.',
    'copy_link' => 'Copia l\'enllaç',
    'public_link' => 'Enllaç públic',
    'delete_note' => 'Les campanyes caducades i desactivades s\'eliminen automàticament.',
    'print_cards' => 'Imprimeix les targetes (PDF)',
    'disable' => 'Desactiva',
    'delete' => 'Elimina ara',

    'status' => [
        'active' => 'activa',
        'disabled' => 'desactivada',
        'expired' => 'caducada',
    ],

    'facts' => [
        'tokens_per_key' => ':tokens fitxes per clau',
        'redeemed' => ':redeemed de :total bescanviats',
        'budget' => 'queden :left de :total fitxes',
        'expires' => 'acaba el :date',
    ],

    'create' => [
        'heading' => 'Crea una campanya',
        'info' => 'La campanya es finança amb aquesta clau: les fitxes repartides es descompten del vostre saldo quan es fan servir. Les campanyes duren 3 mesos i les claus repartides són vàlides durant 1 mes des del bescanvi.',
        'name' => 'Nom (només visible per a vosaltres)',
        'tokens_per_key' => 'Fitxes per clau repartida',
        'total_volume' => 'Màxim total de fitxes',
        'total_volume_hint' => 'La vostra clau té ara mateix :charge fitxes. Mai no podeu repartir més que el vostre saldo.',
        'voucher_count' => 'Nombre de vals (opcional)',
        'voucher_count_hint' => 'Per defecte, el màxim total dividit entre les fitxes per clau.',
        'submit' => 'Crea la campanya',
        'error' => [
            'tokens_per_key_too_high' => 'Les fitxes per clau no poden superar el màxim total.',
            'voucher_count_out_of_range' => 'El nombre de vals no s\'ajusta a les fitxes per clau ni al màxim total.',
            'over_budget' => 'El màxim total supera el vostre saldo disponible.',
            'too_many_active' => 'Ja teniu el nombre màxim de campanyes actives.',
            'invalid' => 'No s\'ha pogut crear la campanya. Comproveu les vostres dades.',
            'unreachable' => 'Ara mateix no s\'ha pogut crear la campanya. Torneu-ho a provar més tard.',
        ],
    ],

    /**
     * /c — App\Http\Controllers\VoucherController. Redactat calcat del
     * `campaign.json` del keymanager (`enter`/`teaser`/`redeemed`/`error`),
     * excepte `redeemed.to_account` i `redeemed.qr_alt`, que allà no eren
     * claus pròpies.
     */
    'redeem' => [
        'enter' => [
            'heading' => 'Bescanvieu el vostre val',
            'description' => 'Heu rebut un codi de val per a cerques gratuïtes a MetaGer? Introduïu-lo aquí per obtenir la vostra clau personal de MetaGer.',
            'label' => 'El vostre codi de val',
            'submit' => 'Bescanvia el codi',
            'invalid_code' => 'Aquest codi no és vàlid. Reviseu el que heu escrit.',
            'rate_limited' => 'Massa intents. Torneu-ho a provar més tard.',
        ],
        'teaser' => [
            'heading' => 'El vostre regal de MetaGer',
            'tokens' => 'Fitxes',
            'description' => 'Aquest codi us dona una clau de MetaGer pròpia carregada amb :tokens fitxes: cerqueu al web sense publicitat i sense que us rastregin.',
            'validity' => 'La clau és vàlida durant :days dies des del bescanvi.',
            'submit' => 'Vull la meva clau',
        ],
        'redeemed' => [
            'heading' => 'Aquí teniu la vostra clau de MetaGer!',
            'description' => 'La vostra clau nova està carregada amb :tokens fitxes.',
            'save' => [
                'heading' => '1. Deseu la vostra clau',
                'description' => 'La clau és el vostre accés: només es mostra aquí i no es pot recuperar. Deseu-la al gestor de contrasenyes, baixeu el codi QR o imprimiu aquesta pàgina.',
            ],
            'copy_key' => 'Copia la clau',
            'validity' => 'La clau és vàlida fins al :date.',
            'use' => [
                'heading' => '2. Comenceu a cercar',
                'description' => 'Obriu aquest enllaç per activar la clau al vostre navegador. Deseu-lo a les adreces d\'interès per mantenir la sessió iniciada.',
            ],
            'copy_url' => 'Copia l\'enllaç',
            'start_searching' => 'Comença a cercar ara',
            'to_account' => 'Ves al meu compte',
            'qr_alt' => 'Codi QR per a la clau',
            'no_cookies' => 'Sembla que aquest navegador no desa galetes. Deseu la clau o el codi QR de dalt.',
        ],
        'error' => [
            'heading' => 'Això no ha funcionat',
            'invalid_code' => 'Aquest codi no existeix. Reviseu el que heu escrit.',
            'invalid_token' => 'Aquest enllaç no és vàlid o ha caducat.',
            'already_redeemed' => 'Aquest codi ja s\'ha bescanviat.',
            'campaign_inactive' => 'Aquesta campanya s\'ha acabat. El codi ja no es pot bescanviar.',
            'budget_exhausted' => 'Tots els regals d\'aquesta campanya ja s\'han repartit.',
            'rate_limited' => 'Massa intents. Torneu-ho a provar més tard.',
            'unreachable' => 'Ara mateix no s\'ha pogut bescanviar el val. Torneu-ho a provar més tard.',
            'unknown' => 'S\'ha produït un error inesperat. Torneu-ho a provar més tard.',
            'retry' => 'Introduïu un codi',
        ],
    ],
];
