<?php
return [
    'lead' => 'Rispondiamo più rapidamente via email.',
    'letter' => [
        '3' => "SUMA-EV\r
Postfach 51 01 43\r
D-30631 Hannover\r
Germania",
        '1' => 'Posta',
    ],
    'headline' => [
        '1' => 'Contatto',
        '2' => 'Email',
    ],
    'email' => [
        'write' => 'Scrivi un\'email',
        'pgp' => [
            'summary' => 'Scrivere cifrato (PGP)',
            'signed' => 'Ogni email che inviamo è firmata con la nostra chiave OpenPGP, inclusa anche nell\'intestazione Autocrypt. Usate questa chiave per scriverci in modo cifrato:',
            'replies' => [
                'heading' => 'Risposte cifrate',
                'how' => 'Rispondiamo automaticamente in modo cifrato non appena abbiamo la vostra chiave e ci fidiamo di essa. Per questo, firmate la vostra email. Prendiamo la vostra chiave dall\'email (come allegato o nell\'intestazione Autocrypt) oppure da keys.openpgp.org, e la usiamo da soli se',
                'trusted_when' => [
                    'keys.openpgp.org la fornisce per il vostro indirizzo (che lì è quindi verificato), oppure',
                    'la vostra email ha una firma DKIM valida del vostro dominio mittente.',
                ],
                'otherwise' => 'Altrimenti qualcuno del nostro team la conferma prima a mano. Da quel momento cifriamo tutto ciò che vi inviamo, non solo le risposte.',
                'tofu' => 'Ci fidiamo della prima chiave ricevuta in questo modo per il vostro indirizzo (Trust on First Use). Non adottiamo automaticamente una nuova chiave per lo stesso indirizzo: finché non l\'abbiamo verificata, continuiamo a cifrare con quella precedente.',
                'never_plain' => 'Non rispondiamo mai in chiaro a un\'email cifrata. Senza la vostra chiave non possiamo rispondere: firmate quindi sempre le email cifrate che ci inviate.',
            ],
            'pubkey' => 'Chiave pubblica PGP: <a href="/download/pubkey.asc" download="0x2185CC8F3CA782EC.asc">0x2185CC8F3CA782EC</a> o su <a href=":keyserver" target="_blank" rel="noopener">keys.openpgp.org</a>',
            'fingerprint' => 'Impronta digitale PGP: 5FA5 2398 C382 B498 B14A B7F6 2185 CC8F 3CA7 82EC',
        ],
    ],
    'removal' => [
        'hint' => 'Si tratta di rimuovere un risultato di ricerca? Leggete prima <a href=":link">la nostra guida</a>: MetaGer mostra i risultati di altri motori di ricerca, quindi di solito un risultato può essere rimosso solo lì.',
    ],
    'phone' => [
        'heading' => 'Telefono',
        'hours' => 'Dal lunedì al venerdì, dalle :from alle :to (ora tedesca)',
        'outside' => 'In questi orari ci trovate quasi sempre, al di fuori quasi mai. In quel caso è meglio scriverci un’e-mail.',
    ],
    'topics' => [
        'heading' => 'Domande frequenti',
        'key' => [
            'title' => 'Chiave MetaGer e token',
            'text' => 'Come funziona la chiave, ricarica e saldo',
        ],
        'orders' => [
            'title' => 'Ordini e fatture',
            'text' => 'Trovare un ordine, ricevute e rimborsi',
        ],
        'donation' => [
            'title' => 'Donare',
            'text' => 'Sostieni MetaGer con una donazione',
        ],
        'membership' => [
            'title' => 'Diventa membro',
            'text' => 'Unisciti a SUMA-EV, l’associazione dietro MetaGer',
        ],
        'help' => [
            'title' => 'Aiuto per la ricerca',
            'text' => 'Funzioni, impostazioni e domande frequenti',
        ],
        'privacy' => [
            'title' => 'La privacy',
            'text' => 'Cosa salviamo — e cosa no',
        ],
    ],
];
