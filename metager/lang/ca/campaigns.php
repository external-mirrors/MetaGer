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
];
