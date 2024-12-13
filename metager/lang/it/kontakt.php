<?php
return [
    'letter' => [
        '2' => 'Preferiamo i contatti digitali. Tuttavia, se ritenete necessario contattarci per posta, potete inviarci una mail all\'indirizzo:',
        '3' => "SUMA-EV\r
Postfach 51 01 43\r
D-30631 Hannover\r
Germania",
        '1' => 'Per posta ordinaria',
    ],
    'error' => [
        '1' => 'Siamo spiacenti, ma purtroppo non abbiamo ricevuto alcun dato con la sua richiesta di contatto. Il messaggio non è stato inviato.',
        '2' => 'Si è verificato un errore nella consegna del messaggio. Potete contattarci direttamente al seguente indirizzo :email',
    ],
    'headline' => [
        '1' => 'Contatto',
        '2' => 'Email',
        'pgp' => 'Crittografia',
    ],
    'form' => [
        '1' => 'Modulo di contatto anonimo',
        '2' => 'Potete inviarci un messaggio anonimo utilizzando questo modulo. Se scegliete di non includere il vostro indirizzo e-mail, non riceverete naturalmente alcuna risposta.',
        'name' => 'Nome',
        '5' => 'Indirizzo e-mail (facoltativo)',
        '6' => 'Il vostro messaggio',
        '7' => 'Oggetto',
        '8' => 'Inviare',
        '9' => 'Fino a 5 allegati (dimensione del file < 5 MB)',
        'temperror' => 'Al momento abbiamo delle difficoltà. Il nostro modulo di contatto tornerà presto.',
    ],
    'success' => [
        '1' => 'Il messaggio è stato consegnato con successo. È stata inviata una prima risposta automatica a :email.',
    ],
    'email' => [
        'text' => 'Potete contattarci inviando una mail a: <a href="mailto::mail">:mail</a>',
        'pgp' => [
            'description' => 'Le nostre e-mail sono firmate crittograficamente. Se volete verificare la firma o inviare la vostra posta crittografata, utilizzate la seguente chiave pubblica. Se volete ricevere una risposta criptata, allegate la vostra chiave pubblica alla vostra mail criptata e firmata.',
            'pubkey' => 'Chiave pubblica PGP: <a href="/download/pubkey.asc" download="0x2185CC8F3CA782EC.asc">0x2185CC8F3CA782EC</a> o su <a href=":keyserver" target="_blank" rel="noopener">keys.openpgp.org</a>',
            'fingerprint' => 'Impronta digitale PGP: 5FA5 2398 C382 B498 B14A B7F6 2185 CC8F 3CA7 82EC',
        ],
    ],
];
